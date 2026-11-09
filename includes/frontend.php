<?php

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('amigo_secreto_dashboard', 'was_frontend_dashboard');
add_shortcode('amigo_secreto', 'was_public_shortcode');
add_shortcode('amigo_secreto_tutorial', 'was_tutorial_shortcode');
add_action('wp_enqueue_scripts', 'was_enqueue_page_assets');
add_filter('body_class', 'was_body_class');
add_action('after_setup_theme', 'was_remove_admin_bar');
add_action('template_redirect', 'was_handle_frontend_forms');

function was_request_value(array $input, $key) {
    return isset($input[$key]) && is_string($input[$key]) ? wp_unslash($input[$key]) : '';
}

function was_notice_markup($message, $success = false) {
    $class = $success ? 'was-alert-success' : 'was-alert-error';
    return '<div class="was-alert ' . $class . '" role="status">' . esc_html($message) . '</div>';
}

function was_page_features() {
    static $features = [];

    $post = get_queried_object();
    if (!is_singular() || !($post instanceof WP_Post)) {
        return [];
    }

    if (!isset($features[$post->ID])) {
        $interactive = has_shortcode($post->post_content, 'amigo_secreto')
            || has_shortcode($post->post_content, 'amigo_secreto_dashboard');
        $is_plugin_page = $interactive || has_shortcode($post->post_content, 'amigo_secreto_tutorial')
            || get_post_meta($post->ID, '_was_is_landing_page', true) === 'yes';
        $features[$post->ID] = $is_plugin_page ? ['interactive' => $interactive] : [];
    }

    return $features[$post->ID];
}

function was_enqueue_page_assets() {
    $features = was_page_features();
    if ($features) {
        was_enqueue_assets($features['interactive']);
    }
}

function was_enqueue_assets($interactive = false) {
    wp_enqueue_style('was-frontend', plugins_url('assets/frontend.css', WAS_PLUGIN_FILE), [], WAS_VERSION);
    if ($interactive) {
        wp_enqueue_script('was-frontend', plugins_url('assets/frontend.js', WAS_PLUGIN_FILE), [], WAS_VERSION, true);
    }
}

function was_body_class($classes) {
    if (was_page_features()) {
        $classes[] = 'was-page';
    }
    return $classes;
}

function was_remove_admin_bar() {
    if (!is_admin() && !current_user_can('manage_options')) {
        show_admin_bar(false);
    }
}

function was_handle_frontend_forms() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }

    if (isset($_POST['was_auth_action'])) {
        nocache_headers();
        require_once __DIR__ . '/auth.php';
        was_process_auth_forms();
    } elseif (is_user_logged_in() && isset($_POST['was_dashboard_action'])) {
        nocache_headers();
        require_once __DIR__ . '/dashboard.php';
        was_process_dashboard_forms();
    } elseif (isset($_POST['was_reg_nonce']) || isset($_POST['was_pin_nonce'])) {
        nocache_headers();
    }
}

function was_frontend_dashboard() {
    require_once __DIR__ . '/dashboard.php';
    was_enqueue_assets(true);
    return was_render_dashboard();
}

function was_public_shortcode($atts) {
    $atts = shortcode_atts(['id' => 0], $atts, 'amigo_secreto');
    $group_id = absint($atts['id']);
    if (!$group_id) {
        return '';
    }

    require_once __DIR__ . '/participation.php';
    was_enqueue_assets(true);
    return was_render_participation($group_id);
}

function was_tutorial_shortcode() {
    was_enqueue_assets();
    ob_start();
    require dirname(__DIR__) . '/views/tutorial.php';
    return ob_get_clean();
}
