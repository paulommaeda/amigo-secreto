<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/groups.php';

function was_render_participation($group_id) {
    $group = was_get_group($group_id);
    if (!$group && absint(get_post_meta(get_queried_object_id(), '_was_linked_group_id', true)) !== $group_id) {
        return '<div class="was-app">' . was_notice_markup('Evento não encontrado.') . '</div>';
    }

    $result = $group ? was_process_participation($group) : [];
    ob_start();
    require dirname(__DIR__) . '/views/participation/event.php';
    return ob_get_clean();
}

function was_process_participation($group) {
    static $results = [];

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return [];
    }
    if (!isset($results[$group->id])) {
        $results[$group->id] = $group->status === 'open' ? was_process_registration($group) : was_process_pin($group->id);
    }
    return $results[$group->id];
}

function was_process_registration($group) {
    if (!isset($_POST['was_reg_nonce'])) {
        return [];
    }
    if (!wp_verify_nonce(was_request_value($_POST, 'was_reg_nonce'), 'reg_' . $group->id)) {
        return ['notice' => was_notice_markup('Sessão expirada. Atualize a página e tente novamente.')];
    }

    $name = sanitize_text_field(was_request_value($_POST, 'was_name'));
    $email = sanitize_email(was_request_value($_POST, 'was_email'));
    $phones = was_phone_formats(sanitize_text_field(was_request_value($_POST, 'was_whatsapp')));
    if (!preg_match('/\A.{1,100}\z/u', $name) || !is_email($email) || strlen($email) > 100 || !$phones) {
        return ['notice' => was_notice_markup('Informe um nome, um e-mail válido e o WhatsApp com DDD.')];
    }
    if (was_request_value($_POST, 'was_privacy_policy') === '') {
        return ['notice' => was_notice_markup('Confirme a leitura das informações para se inscrever.')];
    }

    $person = was_register_participant($group->id, $name, $email, $phones);
    if (is_wp_error($person)) {
        return ['notice' => was_notice_markup($person->get_error_message())];
    }

    if (function_exists('FluentCrmApi')) {
        require_once __DIR__ . '/fluentcrm.php';
        was_sync_fluentcrm($group, $person, was_request_value($_POST, 'was_marketing_opt_in') === '1');
    }
    require_once __DIR__ . '/notifications.php';
    return ['pin' => $person['token'], 'sent' => was_send_registration_email($group, $person)];
}

function was_phone_formats($phone) {
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) < 10 || strlen($digits) > 11) {
        return [];
    }

    $prefix = '(' . substr($digits, 0, 2) . ') ';
    $canonical = $prefix . substr($digits, 2, -4) . '-' . substr($digits, -4);
    $legacy = $prefix . substr($digits, 2, 5) . '-' . substr($digits, 7);
    return [$canonical, $legacy, $digits, $phone];
}

function was_register_participant($group_id, $name, $email, array $phones) {
    return was_with_locked_group($group_id, static function ($group) use ($name, $email, $phones) {
        global $wpdb;

        if ($group->status !== 'open') {
            return new WP_Error('was_closed_registration', 'As inscrições para este sorteio foram encerradas.');
        }

        $table = $wpdb->prefix . 'was_participants';
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE group_id = %d AND (email = %s OR whatsapp IN (%s, %s, %s, %s)) LIMIT 1",
            array_merge([$group->id, $email], $phones)
        ));
        if ($wpdb->last_error) {
            return was_database_error();
        }
        if ($exists) {
            return new WP_Error('was_existing_participant', 'Este e-mail ou WhatsApp já está cadastrado neste sorteio.');
        }

        $pin = was_generate_group_pin($group->id);
        if (is_wp_error($pin)) {
            return $pin;
        }
        $person = ['group_id' => $group->id, 'name' => $name, 'email' => $email, 'whatsapp' => $phones[0], 'token' => $pin];
        if ($wpdb->insert($table, $person) === false) {
            return was_database_error();
        }
        return $person;
    });
}

function was_generate_group_pin($group_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'was_participants';

    for ($attempt = 0; $attempt < 8; $attempt++) {
        $pin = sprintf('%04d', random_int(0, 9999));
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE group_id = %d AND token = %s LIMIT 1", $group_id, $pin));
        if ($wpdb->last_error) {
            return was_database_error();
        }
        if (!$exists) {
            return $pin;
        }
    }

    $used = array_fill_keys($wpdb->get_col($wpdb->prepare("SELECT token FROM $table WHERE group_id = %d", $group_id)), true);
    if ($wpdb->last_error) {
        return was_database_error();
    }

    $start = random_int(0, 9999);
    for ($offset = 0; $offset < 10000; $offset++) {
        $pin = sprintf('%04d', ($start + $offset) % 10000);
        if (!isset($used[$pin])) {
            return $pin;
        }
    }
    return new WP_Error('was_no_pins', 'O evento atingiu o limite de participantes.');
}

function was_process_pin($group_id) {
    global $wpdb;

    if (!isset($_POST['was_pin_nonce'])) {
        return [];
    }
    $recover = isset($_POST['was_recover_email']);
    $result = ['recover' => $recover];
    if (!wp_verify_nonce(was_request_value($_POST, 'was_pin_nonce'), 'was_pin_' . $group_id)) {
        return $result + ['notice' => was_notice_markup('Sessão expirada. Atualize a página e tente novamente.')];
    }

    $attempt_key = 'was_pin_' . hash('sha256', $group_id . ':' . ($_SERVER['REMOTE_ADDR'] ?? ''));
    $attempts = (int) get_transient($attempt_key);
    if ($attempts >= 10) {
        return $result + ['notice' => was_notice_markup('Muitas tentativas. Aguarde 15 minutos antes de tentar novamente.')];
    }

    $participants = $wpdb->prefix . 'was_participants';
    $groups = $wpdb->prefix . 'was_groups';
    if ($recover) {
        $email = sanitize_email(was_request_value($_POST, 'was_recover_email'));
        $phone = sanitize_text_field(was_request_value($_POST, 'was_recover_phone'));
        $phones = was_phone_formats($phone) ?: array_fill(0, 4, $phone);
        $person = is_email($email) && $phone !== '' ? $wpdb->get_row($wpdb->prepare(
            "SELECT p.name, p.token FROM $participants p JOIN $groups g ON g.id = p.group_id
            WHERE g.status = 'drawn' AND p.group_id = %d AND p.email = %s AND p.whatsapp IN (%s, %s, %s, %s)
            ORDER BY p.id LIMIT 1",
            array_merge([$group_id, $email], $phones)
        )) : null;
        if ($person) {
            return ['recovered' => $person];
        }
        $message = 'Os dados não conferem. Verifique o e-mail e o WhatsApp informados na inscrição.';
    } else {
        $pin = trim(was_request_value($_POST, 'was_token'));
        $person = preg_match('/\A[0-9]{4}\z/', $pin) ? $wpdb->get_row($wpdb->prepare(
            "SELECT p.name, f.name AS friend_name FROM $participants p JOIN $groups g ON g.id = p.group_id
            JOIN $participants f ON f.id = p.friend_id AND f.group_id = p.group_id
            WHERE g.status = 'drawn' AND p.group_id = %d AND p.token = %s ORDER BY p.id LIMIT 1",
            $group_id,
            $pin
        )) : null;
        if ($person) {
            return ['revealed' => $person];
        }
        $message = 'PIN incorreto. Tente novamente.';
    }

    set_transient($attempt_key, $attempts + 1, 15 * MINUTE_IN_SECONDS);
    return $result + ['notice' => was_notice_markup($message)];
}
