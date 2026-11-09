<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
?>
<div style="padding: 30px 16px 10px 16px;">
    <h2 style="margin: 0; font-size: 24px;">Meu Perfil</h2>
</div>
<div class="was-card">
    <form method="post">
        <?php wp_nonce_field('was_dashboard_profile'); ?>
        <input type="hidden" name="was_dashboard_action" value="profile">
        <label style="font-size: 13px; font-weight: 600; margin-bottom: 4px; display: block; color: var(--was-text-light);">Nome</label>
        <input type="text" name="user_name" class="was-input" value="<?php echo esc_attr($user->display_name); ?>" required>
        <label style="font-size: 13px; font-weight: 600; margin-bottom: 4px; display: block; color: var(--was-text-light);">E-mail</label>
        <input type="email" name="user_email" class="was-input" value="<?php echo esc_attr($user->user_email); ?>" required>
        <hr style="border: 0; border-top: 1px solid var(--was-border); margin: 20px 0;">
        <p style="font-size: 13px; color: var(--was-text-light); margin-bottom: 15px;">Deixe em branco se não quiser alterar a senha.</p>
        <label style="font-size: 13px; font-weight: 600; margin-bottom: 4px; display: block; color: var(--was-text-light);">Nova Senha</label>
        <input type="password" name="user_pass" class="was-input">
        <label style="font-size: 13px; font-weight: 600; margin-bottom: 4px; display: block; color: var(--was-text-light);">Confirmar Nova Senha</label>
        <input type="password" name="user_pass_confirm" class="was-input">
        <button type="submit" class="was-btn was-btn-primary was-btn-block">Salvar Alterações</button>
    </form>
</div>
