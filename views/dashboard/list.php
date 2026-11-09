<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<header class="was-dashboard-heading">
    <div>
        <p>Olá, <?php echo esc_html($first_name); ?></p>
        <h1>Meus sorteios</h1>
    </div>
    <a href="<?php echo esc_url(add_query_arg('was_act', 'create', $dashboard_url)); ?>" class="was-btn was-btn-primary was-dashboard-action">Novo sorteio</a>
</header>

<?php if (!$groups) : ?>
    <section class="was-card was-dashboard-empty">
        <h2>Nenhum sorteio ainda</h2>
        <p>Crie um grupo para compartilhar o convite com seus amigos.</p>
    </section>
<?php else : ?>
    <div class="was-dashboard-grid">
        <?php foreach ($groups as $g) : ?>
            <article class="was-card was-group-card">
                <span class="was-badge <?php echo $g->status === 'open' ? 'was-badge-open' : 'was-badge-drawn'; ?>">
                    <?php echo $g->status === 'open' ? 'Inscrições abertas' : 'Sorteado'; ?>
                </span>
                <h2><?php echo esc_html($g->title); ?></h2>
                <a href="<?php echo esc_url(add_query_arg(['was_act' => 'manage', 'gid' => $g->id], $dashboard_url)); ?>">Gerenciar sorteio</a>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
