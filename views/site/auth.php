<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require __DIR__ . '/header.php';
?>
<main id="was-site-content" class="was-site-auth-content">
    <?php echo was_render_auth(); ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
