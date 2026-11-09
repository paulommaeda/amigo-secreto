<?php

if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/header.php';
?>
<main id="was-site-content" class="was-site-wrap was-site-missing">
    <div class="was-site-missing-code" aria-hidden="true">404<span><?php was_site_icon('gift'); ?></span></div>
    <p class="was-site-kicker">Página não encontrada</p>
    <h1>Esse link saiu do sorteio.</h1>
    <p>Confira o endereço ou peça ao organizador um novo link se você estava tentando abrir um convite.</p>
    <div class="was-site-actions">
        <a class="was-site-btn was-site-btn-primary" href="<?php echo esc_url(was_site_page_url('home')); ?>">Voltar ao início <?php was_site_icon('arrow'); ?></a>
        <a class="was-site-btn was-site-btn-secondary" href="<?php echo esc_url(was_site_page_url('blog')); ?>">Ir para o blog</a>
    </div>
    <?php require __DIR__ . '/parts/search.php'; ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
