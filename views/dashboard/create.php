<?php

if (!defined('ABSPATH')) {
    exit;
}

$id = wp_unique_id('was-group-title-');
?>
<header class="was-dashboard-heading">
    <div>
        <h1>Novo sorteio</h1>
        <p>Escolha um nome que seus convidados vão reconhecer.</p>
    </div>
    <a href="<?php echo esc_url($dashboard_url); ?>" class="was-dashboard-back">Cancelar</a>
</header>
<section class="was-card was-dashboard-form-card">
    <form method="post" class="was-event-form">
        <?php wp_nonce_field('was_dashboard_create'); ?>
        <div class="was-event-field">
            <label for="<?php echo esc_attr($id); ?>">Nome do grupo</label>
            <input id="<?php echo esc_attr($id); ?>" type="text" name="group_title" maxlength="150" class="was-input" placeholder="Amigo Secreto da Equipe" required>
        </div>
        <button type="submit" name="was_dashboard_action" value="create" class="was-btn was-btn-primary was-dashboard-action">Criar grupo</button>
    </form>
</section>
