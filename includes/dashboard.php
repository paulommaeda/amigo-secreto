<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/groups.php';

function was_process_dashboard_forms() {
    global $was_dashboard_notice;

    $action = was_request_value($_POST, 'was_dashboard_action');
    $user = wp_get_current_user();
    $group_id = absint(was_request_value($_GET, 'gid'));
    $is_group_action = in_array($action, ['draw', 'reset', 'delete'], true);
    if (!$is_group_action && !in_array($action, ['create', 'profile'], true)) {
        return;
    }

    $nonce_action = $is_group_action ? 'was_manage_' . $group_id : 'was_dashboard_' . $action;
    if (!wp_verify_nonce(was_request_value($_POST, '_wpnonce'), $nonce_action)) {
        $was_dashboard_notice = was_notice_markup('Sessão expirada. Atualize a página e tente novamente.');
        return;
    }

    if ($action === 'create') {
        $result = was_create_group(sanitize_text_field(was_request_value($_POST, 'group_title')), $user->ID);
        if (!is_wp_error($result)) {
            wp_safe_redirect(add_query_arg(['was_act' => 'manage', 'gid' => $result]), 303);
            exit;
        }
    } elseif ($action === 'profile') {
        $result = was_update_profile($user);
        if (!is_wp_error($result)) {
            $was_dashboard_notice = was_notice_markup('Perfil atualizado com sucesso!', true);
        }
    } else {
        $result = was_manage_group($group_id, $user->ID, $action);
        if (!is_wp_error($result)) {
            if ($action === 'delete') {
                wp_safe_redirect(remove_query_arg(['was_act', 'gid', 'delete']), 303);
                exit;
            }
            if ($action === 'draw') {
                require_once __DIR__ . '/notifications.php';
                $failed = was_send_result_notifications($result['group'], $result['people']);
                $message = $failed
                    ? sprintf('Sorteio realizado. O WordPress não conseguiu enviar %d e-mail(s).', $failed)
                    : 'Sorteio realizado! Os resultados foram enviados por e-mail.';
                $was_dashboard_notice = was_notice_markup($message, !$failed);
            } else {
                $was_dashboard_notice = was_notice_markup('Inscrições reabertas. Você já pode realizar um novo sorteio.', true);
            }
        }
    }

    if (is_wp_error($result)) {
        $was_dashboard_notice = was_notice_markup($result->get_error_message());
    }
}

function was_update_profile($user) {
    $name = sanitize_text_field(was_request_value($_POST, 'user_name'));
    $email = sanitize_email(was_request_value($_POST, 'user_email'));
    $password = was_request_value($_POST, 'user_pass');
    if ($name === '' || !is_email($email)) {
        return new WP_Error('was_invalid_profile', 'Informe seu nome e um e-mail válido.');
    }
    if ($password !== '' && $password !== was_request_value($_POST, 'user_pass_confirm')) {
        return new WP_Error('was_password_mismatch', 'As senhas digitadas não coincidem.');
    }

    $data = ['ID' => $user->ID, 'user_email' => $email, 'display_name' => $name, 'first_name' => $name];
    if ($password !== '') {
        $data['user_pass'] = $password;
    }
    return wp_update_user($data);
}

function was_render_dashboard() {
    global $wpdb, $was_dashboard_notice;

    if (!is_user_logged_in()) {
        require_once __DIR__ . '/auth.php';
        return was_render_auth();
    }

    $views = dirname(__DIR__) . '/views/dashboard/';
    $user = wp_get_current_user();
    $action = was_request_value($_GET, 'was_act');
    $group_id = absint(was_request_value($_GET, 'gid'));

    ob_start();
    echo '<div class="was-app"><div class="was-app-container was-dashboard">';
    require $views . 'navigation.php';
    echo $was_dashboard_notice ?? '';

    if ($action === 'create') {
        require $views . 'create.php';
    } elseif ($action === 'manage' && $group_id) {
        $group = was_get_group($group_id);
        if ($group && (int) $group->created_by === $user->ID) {
            $table = $wpdb->prefix . 'was_participants';
            $participants = $wpdb->get_results($wpdb->prepare(
                "SELECT name, email, whatsapp FROM $table WHERE group_id = %d ORDER BY id",
                $group_id
            ));
            $invite_url = get_permalink($group->page_id);
            require $views . 'manage.php';
        } else {
            echo was_notice_markup('Acesso negado.');
        }
    } elseif ($action === 'profile') {
        require $views . 'profile.php';
    } else {
        $table = $wpdb->prefix . 'was_groups';
        $groups = $wpdb->get_results($wpdb->prepare(
            "SELECT id, title, status FROM $table WHERE created_by = %d ORDER BY id DESC",
            $user->ID
        ));
        $first_name = strtok(trim($user->display_name), ' @') ?: $user->user_login;
        require $views . 'list.php';
    }

    echo '</div></div>';
    return ob_get_clean();
}
