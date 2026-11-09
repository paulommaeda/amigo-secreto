<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<aside class="was-site-wrap was-site-cta-wrap" aria-label="Criar um sorteio">
    <div class="was-site-cta">
        <div>
            <h2>Pronto para abrir seu grupo?</h2>
            <p>Crie o sorteio e compartilhe o link com os convidados.</p>
        </div>
        <a class="was-site-btn was-site-btn-primary" href="<?php echo esc_url(was_site_dashboard_url(true)); ?>">Criar meu sorteio <?php was_site_icon('arrow'); ?></a>
    </div>
</aside>
