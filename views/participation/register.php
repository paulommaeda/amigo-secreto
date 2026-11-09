<?php

if (!defined('ABSPATH')) {
    exit;
}

$fields = [
    'was_name' => ['Nome completo', 'text', 'name', 100],
    'was_email' => ['E-mail', 'email', 'email', 100],
    'was_whatsapp' => ['WhatsApp com DDD', 'tel', 'tel-national', 15],
];
$terms_url = was_site_page_url('terms');
$privacy_url = was_site_page_url('privacy');
$id = wp_unique_id('was-join-');
?>
<form method="post" class="was-event-form">
    <?php wp_nonce_field('reg_' . $group_id, 'was_reg_nonce'); ?>
    <?php foreach ($fields as $name => [$label, $type, $autocomplete, $max]) : ?>
        <div class="was-event-field">
            <label for="<?php echo esc_attr($id . $name); ?>"><?php echo esc_html($label); ?></label>
            <input id="<?php echo esc_attr($id . $name); ?>" type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>" autocomplete="<?php echo esc_attr($autocomplete); ?>" maxlength="<?php echo esc_attr($max); ?>" value="<?php echo esc_attr(was_request_value($_POST, $name)); ?>" class="was-input"<?php if ($type === 'tel') : ?> placeholder="(00) 00000-0000" data-was-phone<?php endif; ?> required>
        </div>
    <?php endforeach; ?>
    <div class="was-form-check">
        <input id="<?php echo esc_attr($id . 'terms'); ?>" type="checkbox" name="was_privacy_policy" <?php checked(was_request_value($_POST, 'was_privacy_policy') !== ''); ?> required>
        <label for="<?php echo esc_attr($id . 'terms'); ?>">
            <?php if ($terms_url) : ?>
                Aceito os <a href="<?php echo esc_url($terms_url); ?>" target="_blank" rel="noopener">Termos de Uso</a><?php if ($privacy_url) : ?> e li a <a href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener">Política de Privacidade</a><?php endif; ?>.
            <?php elseif ($privacy_url) : ?>
                Li a <a href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener">Política de Privacidade</a> e quero participar.
            <?php else : ?>
                Confirmo meus dados e quero participar do sorteio.
            <?php endif; ?>
        </label>
    </div>
    <?php if (function_exists('FluentCrmApi')) : ?>
        <div class="was-form-check">
            <input id="<?php echo esc_attr($id . 'news'); ?>" type="checkbox" name="was_marketing_opt_in" value="1" <?php checked(was_request_value($_POST, 'was_marketing_opt_in'), '1'); ?>>
            <label for="<?php echo esc_attr($id . 'news'); ?>">Quero receber por e-mail dicas e novidades do Amigo Secreto. Opcional.</label>
        </div>
    <?php endif; ?>
    <button type="submit" class="was-btn was-btn-primary">Confirmar presença</button>
</form>
