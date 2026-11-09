<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="was-bottom-nav">
    <a href="<?php echo esc_url(remove_query_arg(['was_act', 'gid'])); ?>" class="was-nav-item <?php echo ($action !== 'profile') ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
        Sorteios
    </a>
    <a href="<?php echo esc_url(add_query_arg(['was_act' => 'profile'])); ?>" class="was-nav-item <?php echo ($action === 'profile') ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        Perfil
    </a>
    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="was-nav-item">
        <svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
        Sair
    </a>
</div>
