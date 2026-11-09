<?php

if (!defined('ABSPATH')) {
    exit;
}

$home_url = was_site_page_url('home');
$email = was_site_contact_email();
?>
<footer class="was-site-footer">
    <div class="was-site-wrap">
        <div class="was-site-footer-main">
            <div id="contato">
                <a class="was-site-brand" href="<?php echo esc_url($home_url); ?>">
                    <span class="was-site-brand-mark"><?php was_site_icon('gift'); ?></span>
                    <span>Amigo Secreto</span>
                </a>
                <p>Organize sorteios para a família, os amigos ou a equipe.</p>
                <a class="was-site-footer-email" href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a>
            </div>
            <nav aria-label="Links do rodapé">
                <a href="<?php echo esc_url($home_url . '#como-funciona'); ?>">Como funciona</a>
                <a href="<?php echo esc_url($home_url . '#perguntas'); ?>">Perguntas frequentes</a>
                <a href="<?php echo esc_url(was_site_page_url('blog')); ?>">Blog</a>
                <?php foreach (was_site_information_links() as $label => $url) : ?>
                    <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
        <div class="was-site-footer-bottom">&copy; <?php echo esc_html(wp_date('Y')); ?> Amigo Secreto</div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
