<?php

if (!defined('ABSPATH')) {
    exit;
}

$article_url = get_permalink($article);
$article_title = get_the_title($article) ?: 'Artigo sem título';
$categories = get_the_category($article->ID);
$cover = post_password_required($article) ? '' : get_the_post_thumbnail($article, $lead ? 'large' : 'medium_large', [
    'loading' => $lead ? 'eager' : 'lazy',
    'decoding' => 'async',
    'alt' => '',
]);
$excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt($article)), $lead ? 32 : 22, '…');
?>
<article class="was-site-post-card<?php echo $lead ? ' was-site-post-lead' : ''; ?>">
    <a class="was-site-post-media<?php echo $cover ? '' : ' was-site-post-placeholder'; ?>" href="<?php echo esc_url($article_url); ?>" aria-label="<?php echo esc_attr($article_title); ?>">
        <?php if ($cover) : ?>
            <?php echo $cover; ?>
        <?php else : ?>
            <?php was_site_icon('gift'); ?>
        <?php endif; ?>
    </a>
    <div class="was-site-post-content">
        <div class="was-site-post-meta">
            <?php if ($categories) : ?>
                <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>"><?php echo esc_html($categories[0]->name); ?></a>
            <?php endif; ?>
            <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $article)); ?>"><?php echo esc_html(get_the_date('', $article)); ?></time>
        </div>
        <<?php echo $card_heading; ?> class="was-site-post-title"><a href="<?php echo esc_url($article_url); ?>"><?php echo esc_html($article_title); ?></a></<?php echo $card_heading; ?>>
        <?php if ($excerpt) : ?>
            <p><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>
        <a class="was-site-text-link" href="<?php echo esc_url($article_url); ?>" aria-label="<?php echo esc_attr('Ler ' . $article_title); ?>">Ler artigo <?php was_site_icon('arrow'); ?></a>
    </div>
</article>
