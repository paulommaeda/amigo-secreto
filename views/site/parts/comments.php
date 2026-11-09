<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<section class="was-site-comments" aria-labelledby="was-site-comments-title">
    <h2 id="was-site-comments-title">Comentários</h2>
    <?php if (have_comments()) : ?>
        <ol class="was-site-comment-list">
            <?php wp_list_comments(['style' => 'ol', 'short_ping' => true, 'avatar_size' => 36]); ?>
        </ol>
        <?php the_comments_pagination(['prev_text' => 'Anteriores', 'next_text' => 'Mais recentes']); ?>
    <?php endif; ?>
    <?php comment_form(['title_reply' => 'Deixe um comentário', 'label_submit' => 'Publicar comentário']); ?>
</section>
