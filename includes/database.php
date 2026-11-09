<?php

if (!defined('ABSPATH')) {
    exit;
}

function was_install() {
    global $wpdb;

    $schema_version = '2';
    if (get_option('was_schema_version') === $schema_version) {
        return;
    }

    $groups = $wpdb->prefix . 'was_groups';
    $participants = $wpdb->prefix . 'was_participants';
    $lock_name = 'was_schema_' . md5(DB_NAME . ':' . $wpdb->prefix);
    if ((int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)', $lock_name)) !== 1) {
        return;
    }

    try {
        $installed_schema = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'was_schema_version'");
        if ($installed_schema === $schema_version) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();
        dbDelta("CREATE TABLE $groups (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(150) NOT NULL,
            status varchar(20) DEFAULT 'open',
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            page_id bigint(20) DEFAULT 0,
            PRIMARY KEY  (id),
            KEY created_by (created_by)
        ) ENGINE=InnoDB $charset_collate;");
        dbDelta("CREATE TABLE $participants (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            group_id mediumint(9) NOT NULL,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            whatsapp varchar(20) NOT NULL DEFAULT '',
            token varchar(10) NOT NULL,
            friend_id mediumint(9) DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY group_email (group_id,email),
            KEY group_whatsapp (group_id,whatsapp),
            KEY group_token (group_id,token)
        ) ENGINE=InnoDB $charset_collate;");

        $indexes = [
            $groups => ['created_by'],
            $participants => ['group_email', 'group_whatsapp', 'group_token'],
        ];
        foreach ($indexes as $table => $expected_indexes) {
            $status = $wpdb->get_row($wpdb->prepare('SHOW TABLE STATUS LIKE %s', $wpdb->esc_like($table)));
            if (!$status || (strtolower($status->Engine) !== 'innodb'
                && $wpdb->query("ALTER TABLE $table ENGINE=InnoDB") === false)) {
                return;
            }

            $existing_indexes = $wpdb->get_col("SHOW INDEX FROM $table", 2);
            if (array_diff($expected_indexes, $existing_indexes)) {
                return;
            }
        }

        update_option('was_schema_version', $schema_version, true);
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock_name));
    }
}

function was_database_error() {
    return new WP_Error('was_database_error', 'Não foi possível salvar. Tente novamente.');
}

function was_with_locked_group($group_id, callable $operation) {
    global $wpdb;

    $groups = $wpdb->prefix . 'was_groups';
    if ($wpdb->query('START TRANSACTION') === false) {
        return was_database_error();
    }

    $committed = false;
    try {
        $group = $wpdb->get_row($wpdb->prepare("SELECT * FROM $groups WHERE id = %d FOR UPDATE", $group_id));
        if (!$group) {
            return new WP_Error('was_missing_group', 'Evento não encontrado.');
        }

        $result = $operation($group);
        if (is_wp_error($result)) {
            return $result;
        }
        if ($wpdb->query('COMMIT') === false) {
            return was_database_error();
        }

        $committed = true;
        return $result;
    } finally {
        if (!$committed) {
            $wpdb->query('ROLLBACK');
        }
    }
}
