<?php

if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/header.php';
?>
<main id="was-site-content" class="was-site-app-content">
    <?php while (have_posts()) : ?>
        <?php the_post(); the_content(); ?>
    <?php endwhile; ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
