<?php

if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/header.php';
$start_url = was_site_dashboard_url(true);
$latest = was_site_public_articles(3);
$steps = was_site_steps();
$questions = was_site_questions();
$site_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
?>
<main id="was-site-content">
    <section class="was-site-hero">
        <div class="was-site-wrap was-site-hero-grid">
            <div class="was-site-hero-copy">
                <p class="was-site-kicker was-site-pill"><?php was_site_icon('sparkle'); ?> Sorteio online de amigo secreto</p>
                <h1>Organize seu<br> <span>amigo secreto</span><br> com um link.</h1>
                <p class="was-site-intro"><?php echo esc_html(was_site_intro()); ?></p>
                <div class="was-site-actions">
                    <a class="was-site-btn was-site-btn-primary" href="<?php echo esc_url($start_url); ?>">Criar meu sorteio <?php was_site_icon('arrow'); ?></a>
                    <a class="was-site-btn was-site-btn-secondary" href="#como-funciona">Como funciona</a>
                </div>
                <nav class="was-site-institutional-links" aria-label="Informações do site">
                    <?php foreach (was_site_information_navigation() as $link) : ?>
                        <?php $href = wp_parse_url($link['url'], PHP_URL_HOST) === $site_host ? wp_make_link_relative($link['url']) : $link['url']; ?>
                        <a class="was-site-text-link" href="<?php echo esc_url($href); ?>" rel="<?php echo esc_attr($link['rel']); ?>"><?php echo esc_html($link['label']); ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <div class="was-site-hero-visual" aria-hidden="true">
                <img src="<?php echo esc_url(plugins_url('assets/site-gift.svg', WAS_PLUGIN_FILE)); ?>" width="600" height="500" alt="" fetchpriority="high">
                <div class="was-site-float was-site-float-invite">
                    <span class="was-site-float-icon"><?php was_site_icon('link'); ?></span>
                    <span><strong>Convite pelo WhatsApp</strong><small>Compartilhe o link do grupo</small></span>
                </div>
                <div class="was-site-float was-site-float-pin">
                    <span class="was-site-float-icon"><?php was_site_icon('lock'); ?></span>
                    <span><strong>Resultado por PIN</strong><small>Código de 4 dígitos</small></span>
                </div>
            </div>
        </div>
        <div class="was-site-wrap">
            <dl class="was-site-facts">
                <div><dt>1</dt><dd>link por grupo</dd></div>
                <div><dt>3</dt><dd>participantes no mínimo</dd></div>
                <div><dt>4</dt><dd>dígitos no PIN</dd></div>
            </dl>
        </div>
    </section>

    <section class="was-site-section was-site-wrap" id="como-funciona">
        <div class="was-site-section-head">
            <p class="was-site-kicker">Como funciona</p>
            <h2>Do convite ao resultado</h2>
            <p>Você decide quando sortear. O sistema distribui os pares e avisa os participantes.</p>
        </div>
        <ol class="was-site-steps">
            <?php foreach ($steps as $index => [$title, $description]) : ?>
                <li>
                    <span class="was-site-step-number" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $index + 1)); ?></span>
                    <h3><?php echo esc_html($title); ?></h3>
                    <p><?php echo esc_html($description); ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section class="was-site-wrap was-site-section was-site-faq" id="perguntas">
        <div class="was-site-section-head">
            <p class="was-site-kicker">Perguntas frequentes</p>
            <h2>Antes de sortear</h2>
            <p>Veja o que o organizador e os convidados precisam para participar.</p>
            <a class="was-site-text-link" href="<?php echo esc_url(was_site_page_url('tutorial')); ?>">Ver tutorial <?php was_site_icon('arrow'); ?></a>
        </div>
        <div class="was-site-questions">
            <?php foreach ($questions as [$question, $answer]) : ?>
                <details>
                    <summary><?php echo esc_html($question); ?><span aria-hidden="true">+</span></summary>
                    <p><?php echo esc_html($answer); ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="was-site-section was-site-wrap" aria-labelledby="was-site-latest-title">
        <div class="was-site-section-head was-site-section-row">
            <div>
                <p class="was-site-kicker">Blog do Amigo Secreto</p>
                <h2 id="was-site-latest-title">Dicas para a troca de presentes</h2>
                <p>Ideias de presentes, regras de sorteio e inspiração para a confraternização.</p>
            </div>
            <a class="was-site-text-link" href="<?php echo esc_url(was_site_page_url('blog')); ?>">Ver blog <?php was_site_icon('arrow'); ?></a>
        </div>
        <?php if ($latest) : ?>
            <div class="was-site-post-grid">
                <?php $lead = false; $card_heading = 'h3'; ?>
                <?php foreach ($latest as $article) : ?>
                    <?php require __DIR__ . '/parts/post-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="was-site-empty"><p>Os primeiros artigos serão publicados em breve.</p></div>
        <?php endif; ?>
    </section>

    <?php require __DIR__ . '/parts/create-cta.php'; ?>

    <section class="was-site-wrap was-site-section was-site-institutional" aria-labelledby="was-site-institutional-title">
        <div class="was-site-section-head">
            <p class="was-site-kicker">Seu Amigo Secreto</p>
            <h2 id="was-site-institutional-title">Sobre e contato</h2>
            <p>Conheça o projeto de Paulo Maeda, fale com o suporte e consulte os termos do serviço.</p>
        </div>
        <?php require __DIR__ . '/parts/contact-details.php'; ?>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
