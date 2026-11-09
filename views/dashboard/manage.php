<?php

if (!defined('ABSPATH')) {
    exit;
}

$is_open = $group->status === 'open';
$share_text = 'Participe do Amigo Secreto ' . $group->title . '. Abra o link para se inscrever.' . "\n" . $invite_url;
$share_url = 'https://api.whatsapp.com/send?text=' . rawurlencode($share_text);
?>
<header class="was-dashboard-heading">
    <div>
        <h1><?php echo esc_html($group->title); ?></h1>
        <span class="was-badge <?php echo $is_open ? 'was-badge-open' : 'was-badge-drawn'; ?>">
            <?php echo $is_open ? 'Inscrições abertas' : 'Sorteado'; ?>
        </span>
    </div>
    <a href="<?php echo esc_url($dashboard_url); ?>" class="was-dashboard-back">Voltar aos sorteios</a>
</header>

<div class="was-dashboard-grid was-manage-grid">
    <section class="was-card">
        <h2>Convite e sorteio</h2>
        <p>Compartilhe este link com os participantes.</p>
        <a href="<?php echo esc_url($invite_url); ?>" class="was-invite-url" data-was-copy-link target="_blank" rel="noopener"><?php echo esc_html($invite_url); ?></a>
        <a href="<?php echo esc_url($share_url); ?>" target="_blank" rel="noopener" class="was-btn was-btn-whatsapp">Enviar pelo WhatsApp</a>

        <form method="post" class="was-event-form">
            <?php wp_nonce_field('was_manage_' . $group_id); ?>
            <?php if ($is_open) : ?>
                <button type="submit" name="was_dashboard_action" value="draw" data-was-confirm="Realizar o sorteio e avisar os participantes?" class="was-btn was-btn-primary">Realizar sorteio</button>
            <?php else : ?>
                <button type="submit" name="was_dashboard_action" value="reset" data-was-confirm="Reabrir as inscrições para refazer o sorteio?" class="was-btn was-btn-outline">Refazer sorteio</button>
            <?php endif; ?>
            <button type="submit" name="was_dashboard_action" value="delete" data-was-confirm="Encerrar e excluir este evento?" class="was-btn was-btn-danger">Excluir evento</button>
        </form>
        <?php if (current_user_can('manage_options')) : ?>
            <?php $export_url = wp_nonce_url(add_query_arg(['action' => 'was_export_csv', 'gid' => $group_id], admin_url('admin-post.php')), 'was_export_csv_' . $group_id); ?>
            <a href="<?php echo esc_url($export_url); ?>" class="was-event-link">Exportar CSV</a>
        <?php endif; ?>
    </section>

    <section class="was-card">
        <h2>Participantes (<?php echo count($participants); ?>)</h2>
        <?php if (!$participants) : ?>
            <p>Ninguém se inscreveu ainda.</p>
        <?php else : ?>
            <ul class="was-participants-list">
                <?php foreach ($participants as $person) : ?>
                    <li class="was-list-item">
                        <div class="was-participant-contact">
                            <strong><?php echo esc_html($person->name); ?></strong>
                            <span><?php echo esc_html($person->email); ?></span>
                            <span><?php echo esc_html($person->whatsapp); ?></span>
                        </div>
                        <span class="was-participant-status"><?php echo $is_open ? 'Aguardando' : 'Sorteado'; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
