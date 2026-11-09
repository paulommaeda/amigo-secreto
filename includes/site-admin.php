<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'was_site_admin_menu');
add_action('admin_post_was_configure_site', 'was_site_configure');

function was_site_admin_menu() {
    add_options_page('Site do Amigo Secreto', 'Amigo Secreto', 'manage_options', 'was-site', 'was_site_admin_page');
}

function was_site_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $site = was_site_settings();
    $active = !empty($site['enabled']);
    $result = was_request_value($_GET, 'was_result');
    $links = ['home' => 'Página inicial', 'dashboard' => 'Painel e cadastro', 'blog' => 'Blog', 'tutorial' => 'Tutorial'];
    ?>
    <div class="wrap">
        <h1>Site do Amigo Secreto</h1>
        <?php if ($result === 'error') : ?>
            <div class="notice notice-error"><p>Não foi possível preparar todas as páginas. Tente ativar novamente.</p></div>
        <?php elseif ($result === 'saved') : ?>
            <div class="notice notice-success"><p>Configuração salva.</p></div>
        <?php endif; ?>

        <?php if ($active) : ?>
            <p>Os layouts do site estão ativos. Publique o conteúdo do blog em <strong>Posts → Adicionar novo</strong>.</p>
            <ul>
                <?php foreach ($links as $role => $label) : ?>
                    <li><a href="<?php echo esc_url(was_site_page_url($role)); ?>"><?php echo esc_html($label); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <p>Você pode desativar os layouts para voltar à página inicial e à configuração de blog anteriores.</p>
        <?php else : ?>
            <p>Ative a página inicial pública, os layouts de blog, artigo, arquivo e 404 e conecte o painel de sorteios ao menu do site.</p>
            <p>O WordPress prepara as páginas necessárias e configura a página inicial e o blog. O painel de sorteios existente será reaproveitado quando encontrado.</p>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('was_configure_site'); ?>
            <input type="hidden" name="action" value="was_configure_site">
            <input type="hidden" name="was_site_action" value="<?php echo $active ? 'disable' : 'enable'; ?>">
            <?php submit_button($active ? 'Desativar layouts do site' : 'Ativar site do Amigo Secreto', $active ? 'secondary' : 'primary'); ?>
        </form>
    </div>
    <?php
}

function was_site_configure() {
    if (!current_user_can('manage_options')) {
        wp_die('Acesso negado.', '', ['response' => 403]);
    }
    check_admin_referer('was_configure_site');

    $action = was_request_value($_POST, 'was_site_action');
    $result = 'saved';
    if ($action === 'disable') {
        was_site_disable();
    } elseif ($action === 'enable') {
        $result = was_site_enable() ? 'saved' : 'error';
    }

    $url = add_query_arg(['page' => 'was-site', 'was_result' => $result], admin_url('options-general.php'));
    wp_safe_redirect($url, 303);
    exit;
}

function was_site_enable() {
    $site = was_site_settings();
    if (!empty($site['enabled'])) {
        return true;
    }

    $reading = [
        'show_on_front' => get_option('show_on_front'),
        'page_on_front' => (int) get_option('page_on_front'),
        'page_for_posts' => (int) get_option('page_for_posts'),
    ];
    $pages = [];
    $definitions = [
        'home' => ['Início', 'inicio', ''],
        'dashboard' => ['Painel de Sorteios', 'painel', 'amigo_secreto_dashboard'],
        'blog' => ['Blog', 'blog', ''],
        'tutorial' => ['Como Funciona', 'como-funciona', 'amigo_secreto_tutorial'],
    ];

    foreach ($definitions as $role => [$title, $slug, $shortcode]) {
        $page_id = was_site_find_page($role, $shortcode, $site['pages'][$role] ?? 0, $reading);
        if (!$page_id || in_array($page_id, $pages, true)) {
            $page_id = wp_insert_post([
                'post_title' => $title,
                'post_name' => $slug,
                'post_content' => $shortcode ? '[' . $shortcode . ']' : '',
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

    update_option('was_site_settings', ['enabled' => true, 'pages' => $pages, 'previous_reading' => $reading], true);
    if (empty(was_site_settings()['enabled'])) {
        return false;
    }

    update_option('page_on_front', $pages['home']);
    update_option('page_for_posts', $pages['blog']);
    update_option('show_on_front', 'page');
    if ((int) get_option('page_on_front') !== $pages['home']
        || (int) get_option('page_for_posts') !== $pages['blog']
        || get_option('show_on_front') !== 'page') {
        was_site_disable();
        return false;
    }
    return true;
}

function was_site_find_page($role, $shortcode, $saved_id, array $reading) {
    $candidates = [$saved_id];
    if ($role === 'dashboard') {
        $candidates[] = $reading['page_on_front'];
    } elseif ($role === 'blog') {
        $candidates[] = $reading['page_for_posts'];
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
