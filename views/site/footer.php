<?php

if (!defined('ABSPATH')) {
    exit;
}

$home_url = was_site_page_url('home');
$policy_url = was_site_page_url('privacy');
$terms_url = was_site_page_url('terms');
$about_url = was_site_page_url('about');
?>
<footer class="was-site-footer">
    <div class="was-site-wrap">
        <div class="was-site-footer-main">
            <div>
                <a class="was-site-brand" href="<?php echo esc_url($home_url); ?>">
                    <span class="was-site-brand-mark"><?php was_site_icon('gift'); ?></span>
                    <span>Amigo Secreto</span>
                </a>
                <p>Organize sorteios para a família, os amigos ou a equipe.</p>
            </div>
            <nav aria-label="Links do rodapé">
                <a href="<?php echo esc_url($home_url . '#como-funciona'); ?>">Como funciona</a>
                <a href="<?php echo esc_url($home_url . '#perguntas'); ?>">Perguntas frequentes</a>
                <a href="<?php echo esc_url(was_site_page_url('blog')); ?>">Blog</a>
                <?php if ($about_url) : ?>
                    <a href="<?php echo esc_url($about_url); ?>">Sobre</a>
                <?php endif; ?>
                <?php if ($terms_url) : ?>
                    <a href="<?php echo esc_url($terms_url); ?>">Termos de Uso</a>
                <?php endif; ?>
                <?php if ($policy_url) : ?>
                    <a href="<?php echo esc_url($policy_url); ?>">Política de Privacidade</a>
                <?php endif; ?>
            </nav>
        </div>
        <div class="was-site-footer-bottom">&copy; <?php echo esc_html(wp_date('Y')); ?> Amigo Secreto</div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
