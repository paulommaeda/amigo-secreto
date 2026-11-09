<?php

if (!defined('ABSPATH')) {
    exit;
}

add_filter('template_include', 'was_site_template', 90);
add_filter('comments_template', 'was_site_comments_template', 90);
add_action('wp_enqueue_scripts', 'was_site_enqueue_assets');
add_filter('body_class', 'was_site_body_classes');
add_action('template_redirect', 'was_site_redirect_signed_in_auth', 12);

function was_site_settings() {
    return get_option('was_site_settings', []);
}

function was_site_page_id($role) {
    $site = was_site_settings();
    $page_id = $role === 'blog'
        ? (int) get_option('page_for_posts')
        : (!empty($site['enabled']) ? ($site['pages'][$role] ?? 0) : 0);
    $page = $page_id ? get_post($page_id) : null;
    return $page && $page->post_type === 'page' && $page->post_status === 'publish' ? (int) $page->ID : 0;
}

function was_site_page_url($role) {
    $page_id = was_site_page_id($role);
    if ($page_id) {
        return get_permalink($page_id);
    }

    if ($role === 'tutorial') {
        return home_url('/como-funciona/');
    }
    if ($role === 'privacy') {
        return get_privacy_policy_url();
    }
    if (in_array($role, ['terms', 'about', 'contact'], true)) {
        return '';
    }
    return home_url('/');
}

function was_site_account_destination($create = false) {
    $url = was_site_page_url('dashboard');
    return $create ? add_query_arg('was_act', 'create', $url) : $url;
}

function was_site_auth_url($view, $create = false) {
    $page_id = was_site_page_id($view === 'register' ? 'register' : 'login');
    $url = $page_id ? get_permalink($page_id) : was_site_page_url('dashboard');
    $args = $create ? ['was_act' => 'create'] : [];
    if (!$page_id || $view === 'recover') {
        $args['was_auth'] = $view;
    }
    return add_query_arg($args, $url);
}

function was_site_dashboard_url($create = false) {
    return is_user_logged_in()
        ? was_site_account_destination($create)
        : was_site_auth_url($create ? 'register' : 'login', $create);
}

function was_site_current_role(array $roles) {
    $site = was_site_settings();
    if (empty($site['enabled']) || !is_page()) {
        return '';
    }
    $id = get_queried_object_id();
    foreach ($roles as $role) {
        if ($id && $id === (int) ($site['pages'][$role] ?? 0)) {
            return $role;
        }
    }
    return '';
}

function was_site_redirect_signed_in_auth() {
    if (!is_user_logged_in()) {
        return;
    }
    $role = was_site_current_role(['login', 'register']);
    if ($role) {
        $create = $role === 'register' || was_request_value($_GET, 'was_act') === 'create';
        wp_safe_redirect(was_site_account_destination($create));
        exit;
    }
}

function was_site_icon($name) {
    $url = plugins_url('assets/site-icons.svg', WAS_PLUGIN_FILE) . '#' . $name;
    echo '<svg class="was-site-icon" aria-hidden="true" focusable="false"><use href="' . esc_url($url) . '"></use></svg>';
}

function was_site_view() {
    $site = was_site_settings();
    if (empty($site['enabled']) || is_feed() || is_embed()) {
        return '';
    }

    if (is_page($site['pages']['home'])) {
        return 'home';
    }
    if (was_site_current_role(['login', 'register'])) {
        return 'auth';
    }
    if (was_site_current_role(['about', 'contact', 'terms', 'privacy'])) {
        return 'info';
    }
    if (is_404()) {
        return '404';
    }
    if (is_home() || is_category() || is_tag() || is_author() || is_date()
        || (is_search() && get_query_var('post_type') === 'post')) {
        return 'blog';
    }
    if (is_singular('post')) {
        return 'article';
    }
    if (is_page() && was_page_features()) {
        return 'app';
    }

    return '';
}

function was_site_template($template) {
    $view = was_site_view();
    return $view ? dirname(__DIR__) . '/views/site/' . $view . '.php' : $template;
}

function was_site_enqueue_assets() {
    $view = was_site_view();
    if ($view) {
        wp_enqueue_style('was-site', plugins_url('assets/site.css', WAS_PLUGIN_FILE), [], WAS_VERSION);
    }
    if ($view === 'auth') {
        was_enqueue_assets(true);
    }
    if ($view === 'article' && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}

function was_site_comments_template($template) {
    return was_site_view() === 'article' ? dirname(__DIR__) . '/views/site/parts/comments.php' : $template;
}

function was_site_body_classes($classes) {
    $view = was_site_view();
    if ($view) {
        $classes[] = 'was-site';
        $classes[] = 'was-site-' . $view;
    }
    return $classes;
}

function was_site_disable() {
    $site = was_site_settings();
    if (empty($site['enabled'])) {
        return;
    }

    $pages = $site['pages'];
    $previous = $site['previous_reading'];
    if ((int) get_option('page_on_front') === $pages['home']) {
        update_option('page_on_front', $previous['page_on_front']);
        if (get_option('show_on_front') === 'page') {
            update_option('show_on_front', $previous['show_on_front']);
        }
    }
    if ((int) get_option('page_for_posts') === $pages['blog']) {
        update_option('page_for_posts', $previous['page_for_posts']);
    }

    $site['enabled'] = false;
    update_option('was_site_settings', $site, true);
}
