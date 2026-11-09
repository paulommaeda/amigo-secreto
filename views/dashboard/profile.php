<?php

if (!defined('ABSPATH')) {
    exit;
}

$id = wp_unique_id('was-profile-');
?>
<header class="was-dashboard-heading">
    <div><h1>Meu perfil</h1><p>Atualize seu nome, e-mail ou senha.</p></div>
</header>
<section class="was-card was-dashboard-form-card">
    <form method="post" class="was-event-form">
        <?php wp_nonce_field('was_dashboard_profile'); ?>
        <input type="hidden" name="was_dashboard_action" value="profile">
        <div class="was-event-field">
            <label for="<?php echo esc_attr($id . 'name'); ?>">Nome</label>
            <input id="<?php echo esc_attr($id . 'name'); ?>" type="text" name="user_name" class="was-input" autocomplete="name" value="<?php echo esc_attr($user->display_name); ?>" required>
        </div>
        <div class="was-event-field">
            <label for="<?php echo esc_attr($id . 'email'); ?>">E-mail</label>
            <input id="<?php echo esc_attr($id . 'email'); ?>" type="email" name="user_email" class="was-input" autocomplete="email" value="<?php echo esc_attr($user->user_email); ?>" required>
        </div>
        <p>Preencha os campos abaixo se quiser alterar a senha.</p>
        <div class="was-event-field">
            <label for="<?php echo esc_attr($id . 'password'); ?>">Nova senha</label>
            <input id="<?php echo esc_attr($id . 'password'); ?>" type="password" name="user_pass" class="was-input" autocomplete="new-password">
        </div>
        <div class="was-event-field">
            <label for="<?php echo esc_attr($id . 'confirm'); ?>">Confirme a nova senha</label>
            <input id="<?php echo esc_attr($id . 'confirm'); ?>" type="password" name="user_pass_confirm" class="was-input" autocomplete="new-password">
        </div>
        <button type="submit" class="was-btn was-btn-primary was-dashboard-action">Salvar alterações</button>
    </form>
</section>
