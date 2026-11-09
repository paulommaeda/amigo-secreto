<?php

if (!defined('ABSPATH')) {
    exit;
}

$dashboard_url = remove_query_arg(['was_act', 'gid', 'delete', 'was_auth']);
?>
<nav class="was-dashboard-nav" aria-label="Área do organizador">
    <a href="<?php echo esc_url($dashboard_url); ?>"<?php echo $action !== 'profile' ? ' aria-current="page"' : ''; ?>>Meus sorteios</a>
    <a href="<?php echo esc_url(add_query_arg('was_act', 'profile', $dashboard_url)); ?>"<?php echo $action === 'profile' ? ' aria-current="page"' : ''; ?>>Meu perfil</a>
    <a href="<?php echo esc_url(wp_logout_url(was_site_page_url('home'))); ?>">Sair</a>
</nav>
