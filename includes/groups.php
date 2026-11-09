<?php

if (!defined('ABSPATH')) {
    exit;
}

function was_get_group($group_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'was_groups';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $group_id));
}

function was_create_group($title, $organizer_id) {
    global $wpdb;

    if (!preg_match('/\A.{1,150}\z/u', $title)) {
        return new WP_Error('was_invalid_title', 'Informe um nome de até 150 caracteres.');
    }

    $table = $wpdb->prefix . 'was_groups';
    if ($wpdb->insert($table, ['title' => $title, 'created_by' => $organizer_id, 'status' => 'open']) === false) {
        return was_database_error();
    }

    $group_id = (int) $wpdb->insert_id;
    $page_id = wp_insert_post([
        'post_title' => wp_slash($title),
        'post_content' => '[amigo_secreto id="' . $group_id . '"]',
        'post_status' => 'publish',
        'post_type' => 'page',
        'comment_status' => 'closed',
        'meta_input' => ['_was_is_landing_page' => 'yes', '_was_linked_group_id' => $group_id],
    ], true);

    if (is_wp_error($page_id) || !$page_id) {
        $wpdb->delete($table, ['id' => $group_id]);
        return new WP_Error('was_page_creation', 'Não foi possível criar a página de convite.');
    }
    if ($wpdb->update($table, ['page_id' => $page_id], ['id' => $group_id]) !== 1) {
        wp_delete_post($page_id, true);
        $wpdb->delete($table, ['id' => $group_id]);
        return was_database_error();
    }

    return $group_id;
}

function was_manage_group($group_id, $organizer_id, $action) {
    return was_with_locked_group($group_id, static function ($group) use ($organizer_id, $action) {
        global $wpdb;

        if ((int) $group->created_by !== $organizer_id) {
            return new WP_Error('was_forbidden', 'Acesso negado.');
        }
        if ($action === 'draw') {
            return was_draw_group($group);
        }

        $groups = $wpdb->prefix . 'was_groups';
        $participants = $wpdb->prefix . 'was_participants';
        if ($action === 'delete') {
            if ($wpdb->delete($participants, ['group_id' => $group->id]) === false
                || $wpdb->delete($groups, ['id' => $group->id]) !== 1) {
                return was_database_error();
            }
            return true;
        }

        if ($action === 'reset' && $group->status === 'drawn') {
            if ($wpdb->update($participants, ['friend_id' => null], ['group_id' => $group->id]) === false
                || $wpdb->update($groups, ['status' => 'open'], ['id' => $group->id]) !== 1) {
                return was_database_error();
            }
            return true;
        }

        return new WP_Error('was_group_action', 'O evento não pode ser alterado desta forma.');
    });
}

function was_draw_group($group) {
    global $wpdb;

    if ($group->status !== 'open') {
        return new WP_Error('was_already_drawn', 'O sorteio já foi realizado.');
    }

    $groups = $wpdb->prefix . 'was_groups';
    $participants = $wpdb->prefix . 'was_participants';
    $people = $wpdb->get_results($wpdb->prepare("SELECT id, name, email FROM $participants WHERE group_id = %d", $group->id));
    if ($wpdb->last_error) {
        return was_database_error();
    }
    $count = count($people);
    if ($count < 3) {
        return new WP_Error('was_not_enough_participants', 'Mínimo de 3 participantes.');
    }

    shuffle($people);
    $assignments = [];
    $arguments = [];
    foreach ($people as $index => $person) {
        $friend = $people[($index + 1) % $count];
        $person->friend_name = $friend->name;
        $assignments[] = 'WHEN %d THEN %d';
        $arguments[] = $person->id;
        $arguments[] = $friend->id;
    }

    $arguments[] = $group->id;
    $assignments = implode(' ', $assignments);
    if ($wpdb->query($wpdb->prepare("UPDATE $participants SET friend_id = CASE id $assignments END WHERE group_id = %d", $arguments)) === false
        || $wpdb->update($groups, ['status' => 'drawn'], ['id' => $group->id]) !== 1) {
        return was_database_error();
    }

    return ['group' => $group, 'people' => $people];
}
