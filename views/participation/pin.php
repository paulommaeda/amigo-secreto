<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div data-was-switcher>
    <div data-was-panel="reveal" <?php if (!empty($result['recover'])) echo 'hidden'; ?> style="text-align: center;">
        <span class="was-badge was-badge-drawn" style="display: inline-block; margin-bottom: 20px;">O Sorteio Já Aconteceu!</span>
        <form method="post">
            <?php wp_nonce_field('was_pin_' . $group_id, 'was_pin_nonce'); ?>
            <p style="color: var(--was-text-light); margin-bottom: 10px;">Digite seu PIN de 4 dígitos para revelar quem você tirou:</p>
            <input type="text" name="was_token" placeholder="0000" maxlength="4" inputmode="numeric" pattern="[0-9]{4}" class="was-input" style="font-size: 36px; text-align: center; letter-spacing: 16px; padding: 20px; font-weight: bold;" required>
            <button type="submit" class="was-btn was-btn-primary was-btn-block">Revelar Meu Amigo</button>
        </form>
        <p style="margin-top: 24px;"><a href="#" data-was-view="recover" style="color: var(--was-text-light); font-weight: 500; text-decoration: underline;">Esqueci meu PIN</a></p>
    </div>

    <div data-was-panel="recover" <?php if (empty($result['recover'])) echo 'hidden'; ?> style="text-align: left;">
        <form method="post">
            <?php wp_nonce_field('was_pin_' . $group_id, 'was_pin_nonce'); ?>
            <p style="font-weight: bold; margin-top: 0; font-size: 18px;">Recuperar meu PIN</p>
            <p style="font-size: 13px; color: var(--was-text-light); margin-bottom: 16px;">Confirme seus dados para exibir o PIN na tela.</p>

            <label style="font-size: 13px; font-weight: 600; margin-bottom: 4px; display: block; color: var(--was-text-light);">E-mail cadastrado</label>
            <input type="email" name="was_recover_email" class="was-input" placeholder="exemplo@email.com" required>

            <label style="font-size: 13px; font-weight: 600; margin-bottom: 4px; display: block; color: var(--was-text-light);">WhatsApp cadastrado</label>
            <input type="tel" name="was_recover_phone" data-was-phone class="was-input" placeholder="(00) 00000-0000" maxlength="15" required>

            <button type="submit" class="was-btn was-btn-primary was-btn-block">Revelar meu PIN</button>
        </form>
        <p style="margin-top: 24px; text-align: center;"><a href="#" data-was-view="reveal" style="color: var(--was-text-light); font-weight: 500;">Voltar</a></p>
    </div>
</div>
