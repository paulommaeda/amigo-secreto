<?php

if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/header.php';
?>
<main id="was-site-content">
    <?php while (have_posts()) : ?>
        <?php the_post(); $categories = get_the_category(); ?>
        <article <?php post_class('was-site-article'); ?>>
            <header class="was-site-article-head">
                <div class="was-site-wrap was-site-reading">
                    <a class="was-site-text-link was-site-back" href="<?php echo esc_url(was_site_page_url('blog')); ?>"><?php was_site_icon('arrow'); ?> Voltar ao blog</a>
                    <div class="was-site-post-meta">
                        <?php foreach ($categories as $category) : ?>
                            <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>"><?php echo esc_html($category->name); ?></a>
                        <?php endforeach; ?>
                    </div>
                    <h1><?php the_title(); ?></h1>
                    <div class="was-site-article-byline">
                        <span><?php the_author_posts_link(); ?></span>
                        <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time>
                    </div>
                </div>
            </header>

            <?php if (has_post_thumbnail() && !post_password_required()) : ?>
                <figure class="was-site-wrap was-site-article-cover">
                    <?php the_post_thumbnail('large', ['loading' => 'eager', 'fetchpriority' => 'high']); ?>
                    <?php if (get_the_post_thumbnail_caption()) : ?>
                        <figcaption><?php echo esc_html(get_the_post_thumbnail_caption()); ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endif; ?>

            <div class="was-site-wrap was-site-reading">
                <div class="was-site-prose">
                    <?php the_content(); ?>
                    <?php wp_link_pages(['before' => '<nav class="was-site-article-pages" aria-label="Páginas do artigo">Páginas ', 'after' => '</nav>']); ?>
                </div>
                <?php if (!post_password_required()) : ?>
                    <?php the_tags('<nav class="was-site-topics was-site-tags" aria-label="Temas do artigo">', '', '</nav>'); ?>
                    <?php the_post_navigation(['prev_text' => 'Anterior<br>%title', 'next_text' => 'Próximo<br>%title']); ?>
                    <?php if (comments_open() || get_comments_number()) : ?>
                        <?php comments_template(); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </article>
    <?php endwhile; ?>

    <?php require __DIR__ . '/parts/create-cta.php'; ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
