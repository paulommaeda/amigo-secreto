<?php

if (!defined('ABSPATH')) {
    exit;
}

$id = wp_unique_id('was-pin-');
?>
<div data-was-switcher>
    <div data-was-panel="reveal"<?php echo !empty($result['recover']) ? ' hidden' : ''; ?>>
        <form method="post" class="was-event-form">
            <?php wp_nonce_field('was_pin_' . $group_id, 'was_pin_nonce'); ?>
            <div class="was-event-field">
                <label for="<?php echo esc_attr($id . 'token'); ?>">Seu PIN de 4 dígitos</label>
                <input id="<?php echo esc_attr($id . 'token'); ?>" type="text" name="was_token" placeholder="0000" maxlength="4" inputmode="numeric" pattern="[0-9]{4}" class="was-input was-pin-input" required>
            </div>
            <button type="submit" class="was-btn was-btn-primary">Revelar meu amigo</button>
        </form>
        <button type="button" class="was-event-link" data-was-view="recover">Esqueci meu PIN</button>
    </div>

    <div data-was-panel="recover"<?php echo empty($result['recover']) ? ' hidden' : ''; ?>>
        <h3>Recuperar meu PIN</h3>
        <form method="post" class="was-event-form">
            <?php wp_nonce_field('was_pin_' . $group_id, 'was_pin_nonce'); ?>
            <div class="was-event-field">
                <label for="<?php echo esc_attr($id . 'email'); ?>">E-mail cadastrado</label>
                <input id="<?php echo esc_attr($id . 'email'); ?>" type="email" name="was_recover_email" class="was-input" autocomplete="email" required>
            </div>
            <div class="was-event-field">
                <label for="<?php echo esc_attr($id . 'phone'); ?>">WhatsApp cadastrado</label>
                <input id="<?php echo esc_attr($id . 'phone'); ?>" type="tel" name="was_recover_phone" data-was-phone class="was-input" autocomplete="tel-national" placeholder="(00) 00000-0000" maxlength="15" required>
            </div>
            <button type="submit" class="was-btn was-btn-primary">Recuperar PIN</button>
        </form>
        <button type="button" class="was-event-link" data-was-view="reveal">Voltar</button>
    </div>
</div>
