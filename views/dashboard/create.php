<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div style="display: flex; justify-content: space-between; align-items: center; padding: 30px 16px 10px 16px;">
    <h2 style="margin: 0; font-size: 24px;">Novo <span class="text-gradient">Sorteio</span></h2>
    <a href="<?php echo esc_url(remove_query_arg(['was_act', 'gid'])); ?>" style="color: var(--was-text-light); text-decoration: none; font-size: 15px;">Cancelar</a>
</div>
<div class="was-card">
    <form method="post">
        <?php wp_nonce_field('was_dashboard_create'); ?>
        <label style="display:block; margin-bottom: 8px; font-weight: 500; color: var(--was-text-light);">Qual o nome do evento?</label>
        <input type="text" name="group_title" maxlength="150" class="was-input" placeholder="Ex: Amigo Secreto da Firma" required>
        <button type="submit" name="was_dashboard_action" value="create" class="was-btn was-btn-primary">Criar Grupo</button>
    </form>
</div>
