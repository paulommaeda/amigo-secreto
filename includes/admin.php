<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'was_block_wp_admin');
add_action('admin_post_was_export_csv', 'was_export_csv_handler');

function was_block_wp_admin() {
    if (!wp_doing_ajax() && !current_user_can('manage_options')) {
        wp_safe_redirect(home_url('/'));
        exit;
    }
}

function was_export_csv_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Acesso negado.', '', ['response' => 403]);
    }

    $group_id = absint(was_request_value($_GET, 'gid'));
    check_admin_referer('was_export_csv_' . $group_id);

    require_once __DIR__ . '/groups.php';
    $group = was_get_group($group_id);
    if (!$group) {
        wp_die('Grupo não encontrado.', '', ['response' => 404]);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'was_participants';
    $participants = $wpdb->get_results($wpdb->prepare(
        "SELECT p.name, p.email, p.whatsapp, f.name AS friend_name FROM $table p
        LEFT JOIN $table f ON f.id = p.friend_id AND f.group_id = p.group_id
        WHERE p.group_id = %d ORDER BY p.id",
        $group_id
    ));

    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Sorteio_' . sanitize_title($group->title) . '.csv"');
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ['Nome', 'E-mail', 'WhatsApp', 'Amigo Sorteado'], ';', '"', '');

    foreach ($participants as $person) {
        $cells = [$person->name, $person->email, $person->whatsapp, $person->friend_name ?: 'Ainda não sorteado'];
        $cells = array_map(static function ($cell) {
            return preg_match('/^[\s]*[=+\-@]|^[\t\r\n]/', $cell) ? "'" . $cell : $cell;
        }, $cells);
        fputcsv($output, $cells, ';', '"', '');
    }

    fclose($output);
    exit;
}
