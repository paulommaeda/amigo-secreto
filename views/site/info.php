<?php

if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/header.php';
$role = was_site_current_role(['about', 'contact']);
?>
<main id="was-site-content">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>
        <article class="was-site-article">
            <header class="was-site-article-head">
                <div class="was-site-wrap was-site-reading">
                    <p class="was-site-kicker"><?php echo esc_html(['about' => 'Sobre o projeto', 'contact' => 'Fale com a gente'][$role] ?? 'Informações do site'); ?></p>
                    <h1><?php the_title(); ?></h1>
                    <?php if (!$role) : ?>
                        <p class="was-site-article-byline">Atualizado em <time datetime="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_modified_date('d/m/Y')); ?></time></p>
                    <?php endif; ?>
                </div>
            </header>
            <div class="was-site-wrap was-site-reading was-site-prose">
                <?php if ($role === 'contact' && !post_password_required()) : ?>
                    <?php require __DIR__ . '/parts/contact-details.php'; ?>
                <?php endif; ?>
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
