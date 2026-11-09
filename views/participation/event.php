<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="was-app was-event">
    <div class="was-app-container was-event-container">
        <?php if (!$group) : ?>
            <section class="was-card was-event-card">
                <h1>Evento encerrado</h1>
                <p>O organizador encerrou este sorteio.</p>
                <a href="<?php echo esc_url(was_site_dashboard_url(true)); ?>" class="was-btn was-btn-primary">Criar meu sorteio</a>
            </section>
        <?php else : ?>
            <header class="was-event-heading">
                <p class="was-event-kicker">Convite de amigo secreto</p>
                <h1><?php echo esc_html($group->title); ?></h1>
                <p><?php echo $group->status === 'open' ? 'Inscreva-se para participar e receber seu PIN.' : 'O sorteio foi realizado. Use seu PIN para consultar o resultado.'; ?></p>
            </header>

            <section class="was-card was-event-card">
                <?php echo $result['notice'] ?? ''; ?>

                <?php if (isset($result['pin'])) : ?>
                    <div class="was-event-result">
                        <h2>Inscrição confirmada</h2>
                        <p>Guarde este PIN para consultar o resultado após o sorteio.</p>
                        <div class="was-pin-value"><?php echo esc_html($result['pin']); ?></div>
                        <p><?php echo $result['sent'] ? 'Enviamos uma cópia para o seu e-mail.' : 'Use o PIN exibido aqui para consultar seu resultado.'; ?></p>
                    </div>
                <?php elseif (isset($result['revealed'])) : ?>
                    <div class="was-event-result">
                        <p>Olá, <strong><?php echo esc_html($result['revealed']->name); ?></strong>! Seu amigo secreto é</p>
                        <h2 class="text-gradient"><?php echo esc_html($result['revealed']->friend_name); ?></h2>
                        <p>Guarde a surpresa para o dia da troca de presentes.</p>
                    </div>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="was-event-link">Voltar</a>
                <?php elseif ($group->status === 'open') : ?>
                    <h2>Confirmar participação</h2>
                    <?php require __DIR__ . '/register.php'; ?>
                <?php else : ?>
                    <h2>Resultado do sorteio</h2>
                    <?php if (isset($result['recovered'])) : ?>
                        <div class="was-alert was-alert-success">
                            <p>Olá, <strong><?php echo esc_html($result['recovered']->name); ?></strong>. Seu PIN é</p>
                            <div class="was-pin-value"><?php echo esc_html($result['recovered']->token); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php require __DIR__ . '/pin.php'; ?>
                <?php endif; ?>
            </section>
            <a href="<?php echo esc_url(was_site_page_url('tutorial')); ?>" class="was-event-link">Como funciona o sorteio</a>
        <?php endif; ?>
    </div>
</div>
