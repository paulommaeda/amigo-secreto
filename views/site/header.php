<?php

if (!defined('ABSPATH')) {
    exit;
}

$home_url = was_site_page_url('home');
$blog_url = was_site_page_url('blog');
$about_url = was_site_page_url('about');
$view = was_site_view();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (!current_theme_supports('title-tag')) : ?>
        <title><?php echo esc_html(wp_get_document_title()); ?></title>
    <?php endif; ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="was-site-skip" href="#was-site-content">Ir para o conteúdo</a>
<header class="was-site-header">
    <div class="was-site-wrap was-site-header-inner">
        <a class="was-site-brand" href="<?php echo esc_url($home_url); ?>">
            <span class="was-site-brand-mark"><?php was_site_icon('gift'); ?></span>
            <span>Amigo Secreto</span>
        </a>

        <nav class="was-site-nav" aria-label="Navegação principal">
            <a href="<?php echo esc_url($home_url . '#como-funciona'); ?>">Como funciona</a>
            <a href="<?php echo esc_url($blog_url); ?>"<?php echo in_array($view, ['blog', 'article'], true) ? ' aria-current="true"' : ''; ?>>Blog</a>
            <?php if ($about_url) : ?>
                <a href="<?php echo esc_url($about_url); ?>"<?php echo was_site_current_role(['about']) ? ' aria-current="page"' : ''; ?>>Sobre</a>
            <?php endif; ?>
        </nav>

        <div class="was-site-header-actions">
            <a class="was-site-login" href="<?php echo esc_url(was_site_dashboard_url()); ?>"><?php echo is_user_logged_in() ? 'Meu painel' : 'Entrar'; ?></a>
            <a class="was-site-btn was-site-btn-primary was-site-btn-small" href="<?php echo esc_url(was_site_dashboard_url(true)); ?>"><?php echo $view === 'auth' ? 'Criar conta' : 'Criar sorteio'; ?></a>
        </div>
    </div>
</header>
