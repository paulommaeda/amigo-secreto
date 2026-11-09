<?php

if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/header.php';
$blog_title = 'Dicas de amigo secreto';
$description = 'Guias de organização, regras do sorteio e ideias de presentes para a próxima confraternização.';
if (is_search()) {
    $blog_title = sprintf('Busca por “%s”', get_search_query(false));
    $description = 'Artigos que correspondem à sua busca.';
} elseif (is_archive()) {
    $blog_title = is_category() || is_tag() ? single_term_title('', false) : wp_strip_all_tags(get_the_archive_title());
    $description = trim(wp_strip_all_tags(get_the_archive_description())) ?: 'Explore os artigos deste arquivo.';
}
$topics = get_categories(['hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 9]);
?>
<main id="was-site-content">
    <section class="was-site-blog-head">
        <div class="was-site-wrap was-site-blog-head-inner">
            <div>
                <p class="was-site-kicker was-site-pill"><?php was_site_icon('book'); ?> Blog do Amigo Secreto</p>
                <h1><?php echo esc_html($blog_title); ?></h1>
                <p><?php echo esc_html($description); ?></p>
            </div>
            <?php require __DIR__ . '/parts/search.php'; ?>
        </div>
    </section>

    <section class="was-site-wrap was-site-blog-list" aria-label="Artigos do blog">
        <?php if ($topics || !is_home()) : ?>
            <nav class="was-site-topics" aria-label="Categorias do blog">
                <a href="<?php echo esc_url(was_site_page_url('blog')); ?>"<?php echo is_home() ? ' aria-current="page"' : ''; ?>>Todos os artigos</a>
                <?php foreach ($topics as $topic) : ?>
                    <a href="<?php echo esc_url(get_category_link($topic->term_id)); ?>"<?php echo is_category($topic->term_id) ? ' aria-current="page"' : ''; ?>><?php echo esc_html($topic->name); ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if (have_posts()) : ?>
            <div class="was-site-post-grid">
                <?php $lead = is_home() && !is_paged(); $card_heading = 'h2'; ?>
                <?php while (have_posts()) : ?>
                    <?php the_post(); $article = get_post(); require __DIR__ . '/parts/post-card.php'; $lead = false; ?>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination([
                'mid_size' => 1,
                'prev_text' => 'Anterior',
                'next_text' => 'Próxima',
                'screen_reader_text' => 'Páginas do blog',
            ]); ?>
        <?php else : ?>
            <div class="was-site-empty">
                <h2><?php echo is_search() ? 'Nenhum artigo encontrado' : 'Ainda não há artigos aqui'; ?></h2>
                <p><?php echo is_search() ? 'Tente outro termo ou navegue pelas categorias do blog.' : 'Volte em breve para conferir novas publicações.'; ?></p>
            </div>
        <?php endif; ?>
    </section>

    <?php require __DIR__ . '/parts/create-cta.php'; ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
