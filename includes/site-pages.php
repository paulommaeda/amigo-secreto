<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'was_site_migrate_information_pages', 20);

function was_site_information_pages() {
    return [
        'about' => ['Sobre', 'sobre', ''],
        'contact' => ['Contato', 'contato', ''],
        'terms' => ['Termos de Uso', 'termos-de-uso', ''],
        'privacy' => ['Política de Privacidade', 'politica-de-privacidade', ''],
    ];
}

function was_site_additional_pages() {
    return [
        'login' => ['Entrar', 'entrar', ''],
        'register' => ['Criar conta', 'criar-conta', ''],
    ] + was_site_information_pages();
}

function was_site_migrate_information_pages() {
    $site = was_site_settings();
    if (!empty($site['enabled']) && empty($site['information_pages_ready'])) {
        was_site_complete(true);
    }
}

function was_site_change_pages(callable $operation) {
    global $wpdb;

    $lock = 'was_info_pages_' . md5(DB_NAME . ':' . $wpdb->prefix);
    if ((int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)', $lock)) !== 1) {
        return false;
    }

    try {
        // A request that waited for the lock may have loaded the previous page mapping.
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('was_site_settings', 'options');
        return $operation();
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}

function was_site_complete($automatic = false) {
    return was_site_change_pages(static function () use ($automatic) {
        $site = was_site_settings();
        if (empty($site['enabled'])) {
            return false;
        }
        if ($automatic && !empty($site['information_pages_ready'])) {
            return true;
        }

        $definitions = $automatic
            ? array_filter(was_site_information_pages(), static function ($role) {
                return !was_site_page_id($role);
            }, ARRAY_FILTER_USE_KEY)
            : was_site_additional_pages();
        $pages = was_site_prepare_pages($definitions, $site['pages'], $site['previous_reading']);
        if (!$pages) {
            return false;
        }

        $site['pages'] = $pages;
        $site['information_pages_ready'] = true;
        if (update_option('was_site_settings', $site, true)) {
            was_site_purge_information_cache(true);
            return true;
        }
        return was_site_settings() === $site;
    });
}

function was_site_prepare_pages(array $definitions, array $pages, array $reading) {
    foreach ($definitions as $role => [$title, $slug, $shortcode]) {
        $page_id = was_site_find_page($role, $slug, $shortcode, $pages[$role] ?? 0, $reading);
        if (!$page_id || in_array($page_id, array_diff_key($pages, [$role => true]), true)) {
            $content = $shortcode ? '[' . $shortcode . ']' : '';
            if (isset(was_site_information_pages()[$role])) {
                $content = file_get_contents(dirname(__DIR__) . '/views/site/content/' . $role . '.html');
                if ($content === false) {
                    return false;
                }
            }
            $page_id = wp_insert_post([
                'post_title' => $title,
                'post_name' => $slug,
                'post_content' => wp_slash($content),
                'post_author' => get_current_user_id() ?: (int) get_post_field('post_author', $pages['home'] ?? 0),
                'post_type' => 'page',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'meta_input' => ['_was_site_role' => $role],
            ], true);
        }
        if (is_wp_error($page_id) || !$page_id) {
            return false;
        }
        $pages[$role] = (int) $page_id;
    }
    return $pages;
}

function was_site_find_page($role, $slug, $shortcode, $saved_id, array $reading) {
    $candidates = [$saved_id];
    if ($role === 'dashboard') {
        $candidates[] = $reading['page_on_front'];
    } elseif ($role === 'blog') {
        $candidates[] = $reading['page_for_posts'];
    } elseif ($role === 'privacy') {
        $candidates[] = (int) get_option('wp_page_for_privacy_policy');
    }

    foreach (array_filter($candidates) as $id) {
        $page = get_post($id);
        if ($page && $page->post_type === 'page' && $page->post_status === 'publish'
            && (!$shortcode || has_shortcode($page->post_content, $shortcode))) {
            return (int) $page->ID;
        }
    }

    $query = ['post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids'];
    $owned = get_posts($query + ['meta_key' => '_was_site_role', 'meta_value' => $role]);
    if ($owned && (!$shortcode || has_shortcode(get_post_field('post_content', $owned[0]), $shortcode))) {
        return (int) $owned[0];
    }

    if (isset(was_site_information_pages()[$role])) {
        $existing = get_page_by_path($slug, OBJECT, 'page');
        if ($existing && $existing->post_status === 'publish' && $existing->post_password === ''
            && !has_shortcode($existing->post_content, 'amigo_secreto')
            && !has_shortcode($existing->post_content, 'amigo_secreto_dashboard')
            && !get_post_meta($existing->ID, '_was_linked_group_id', true)) {
            return (int) $existing->ID;
        }
    }

    if ($shortcode) {
        $query['posts_per_page'] = 20;
        foreach (get_posts($query + ['s' => $shortcode]) as $id) {
            if (has_shortcode(get_post_field('post_content', $id), $shortcode)) {
                return (int) $id;
            }
        }
    }
    return 0;
}
