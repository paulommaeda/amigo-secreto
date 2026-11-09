<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div style="display: flex; justify-content: space-between; align-items: center; padding: 30px 16px 20px 16px; gap: 15px;">
    <div style="flex: 1; min-width: 0;">
        <p style="margin: 0 0 4px 0; font-size: 14px; color: var(--was-text-light); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Olá, <span style="color: #fff;"><?php echo esc_html($first_name); ?></span> 👋</p>
        <h2 style="margin: 0; font-size: 24px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Meus Sorteios</h2>
    </div>
    <a href="<?php echo esc_url(add_query_arg(['was_act' => 'create'])); ?>" class="was-btn was-btn-primary" style="width: auto; padding: 10px 20px; font-size: 14px; flex-shrink: 0;">+ Novo</a>
</div>

<?php if(!$groups): ?>
    <div style="text-align: center; padding: 40px 20px;">
        <div style="font-size: 60px; margin-bottom: 10px; filter: drop-shadow(0 0 10px rgba(255,77,109,0.5));">🎁</div>
        <h3 style="margin-bottom: 8px;">Nenhum sorteio ainda</h3>
        <p style="color: var(--was-text-light); font-size: 14px;">Crie seu primeiro evento e convide seus amigos.</p>
    </div>
<?php else: ?>
    <div style="padding: 0 16px;">
        <?php foreach($groups as $g): ?>
            <div class="was-card" style="margin: 0 0 16px 0; padding: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span class="was-badge <?php echo ($g->status === 'open') ? 'was-badge-open' : 'was-badge-drawn'; ?>">
                        <?php echo ($g->status === 'open') ? 'Aberto' : 'Sorteado'; ?>
                    </span>
                </div>
                <h3 style="margin: 0 0 16px 0; font-size: 18px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo esc_html($g->title); ?></h3>
                <a href="<?php echo esc_url(add_query_arg(['was_act' => 'manage', 'gid' => $g->id])); ?>" class="was-btn was-btn-outline" style="padding: 10px;">Gerenciar Sorteio</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
