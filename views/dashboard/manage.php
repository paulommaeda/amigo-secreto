<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 30px 16px 10px 16px; gap: 10px;">
    <div style="flex: 1; min-width: 0;">
        <h2 style="margin: 0; font-size: 24px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo esc_html($group->title); ?></h2>
        <span class="was-badge <?php echo ($group->status === 'open') ? 'was-badge-open' : 'was-badge-drawn'; ?>" style="display: inline-block; margin-top: 6px;">
            <?php echo ($group->status === 'open') ? 'Aberto' : 'Sorteado'; ?>
        </span>
    </div>
    <a href="<?php echo esc_url(remove_query_arg(['was_act', 'gid'])); ?>" class="was-btn was-btn-outline" style="padding: 8px 14px; font-size: 13px; width: auto; flex-shrink: 0;">Voltar</a>
</div>

<div class="was-card">
    <p style="margin: 0 0 8px 0; font-size: 13px; color: var(--was-text-light);">Envie este link para os participantes:</p>

    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
        <a href="<?php echo esc_url($invite_url); ?>" data-was-copy-link target="_blank" rel="noopener" style="background: rgba(0,0,0,0.3); padding: 14px 12px; border-radius: 8px; word-break: break-all; font-size: 14px; color: #a855f7; font-weight: 500; border: 1px solid var(--was-border); text-decoration: none; text-align: center; transition: 0.2s;">
            <?php echo esc_html($invite_url); ?>
        </a>

        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Participe do Amigo Secreto: *" . $group->title . "*! 🎁\n\nAcesse o link abaixo para se inscrever e garantir sua vaga:\n" . $invite_url); ?>" target="_blank" rel="noopener" class="was-btn" style="background: #25D366; color: #ffffff; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="margin-right: 8px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
            Enviar pelo WhatsApp
        </a>
    </div>
    <form method="post" style="display: flex; flex-direction: column; gap: 12px;">
        <?php wp_nonce_field('was_manage_' . $group_id); ?>
        <?php if($group->status === 'open'): ?>
            <button type="submit" name="was_dashboard_action" value="draw" data-was-confirm="Sortear e avisar a todos?" class="was-btn was-btn-primary">🎲 Realizar Sorteio Automático</button>
        <?php else: ?>
            <button type="submit" name="was_dashboard_action" value="reset" data-was-confirm="Reabrir as inscrições para refazer o sorteio?" class="was-btn was-btn-outline">🔄 Refazer Sorteio</button>
        <?php endif; ?>
        <button type="submit" name="was_dashboard_action" value="delete" data-was-confirm="Tem certeza? A página será encerrada." class="was-btn was-btn-danger">Excluir Evento</button>
    </form>
    <?php if (current_user_can('manage_options')) : ?>
        <?php $export_url = wp_nonce_url(add_query_arg(['action' => 'was_export_csv', 'gid' => $group_id], admin_url('admin-post.php')), 'was_export_csv_' . $group_id); ?>
        <a href="<?php echo esc_url($export_url); ?>" class="was-btn was-btn-outline" style="margin-top: 12px;">Exportar CSV</a>
    <?php endif; ?>
</div>

<div class="was-card">
    <h3 style="margin: 0 0 16px 0; font-size: 16px;">Participantes (<?php echo count($participants); ?>)</h3>
    <?php if(!$participants): ?>
        <p style="text-align: center; color: var(--was-text-light); font-size: 14px;">Ninguém se inscreveu ainda.</p>
    <?php else: ?>
        <?php foreach($participants as $p): ?>
            <div class="was-list-item">
                <div style="flex: 1; min-width: 0; padding-right: 10px;">
                    <div style="font-weight: 600; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo esc_html($p->name); ?></div>
                    <div style="font-size: 13px; color: var(--was-text-light); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 3px;">
                        <?php echo esc_html($p->email); ?><br>
                        <?php echo esc_html($p->whatsapp); ?>
                    </div>
                </div>
                <span style="font-size: 12px; flex-shrink: 0; color: <?php echo ($group->status === 'drawn') ? '#34d399' : 'var(--was-text-light)'; ?>;">
                    <?php echo ($group->status === 'drawn') ? '✓ Sorteado' : 'Aguardando'; ?>
                </span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
