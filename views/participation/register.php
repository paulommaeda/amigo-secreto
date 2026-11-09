<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<form method="post">
    <?php wp_nonce_field('reg_' . $group_id, 'was_reg_nonce'); ?>
    <p style="color: var(--was-text-light); text-align: center; margin-bottom: 24px; font-size: 15px;">Preencha seus dados para participar do sorteio.</p>
    <label style="font-size: 13px; font-weight: 600; margin-bottom: 4px; display: block; color: var(--was-text-light);">Seu Nome e Sobrenome</label>
    <input type="text" name="was_name" autocomplete="name" maxlength="100" class="was-input" required>
    <label style="font-size: 13px; font-weight: 600; margin-bottom: 4px; display: block; color: var(--was-text-light);">E-mail Válido</label>
    <input type="email" name="was_email" autocomplete="email" maxlength="100" class="was-input" required>
    <label style="font-size: 13px; font-weight: 600; margin-bottom: 4px; display: block; color: var(--was-text-light);">WhatsApp</label>
    <input type="tel" name="was_whatsapp" data-was-phone class="was-input" placeholder="(00) 00000-0000" maxlength="15" required>
    <div style="margin-bottom: 24px; display: flex; align-items: flex-start; gap: 8px;">
        <input type="checkbox" name="was_privacy_policy" required style="margin-top: 2px; width: 18px; height: 18px; accent-color: #a855f7;">
        <span style="font-size: 13px; color: var(--was-text-light);">Aceito participar do grupo e concordo com a <a href="<?php echo esc_url(get_privacy_policy_url() ?: home_url('/politica-de-privacidade/')); ?>" target="_blank" rel="noopener" style="color: var(--was-primary-solid);">Política de Privacidade</a>.</span>
    </div>
    <button type="submit" class="was-btn was-btn-primary was-btn-block">Confirmar Presença</button>
</form>
