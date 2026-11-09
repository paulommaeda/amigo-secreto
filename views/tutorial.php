<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="was-app">
    <div class="was-app-container" style="padding: 40px 16px 100px 16px;">

        <div style="display: flex; margin-bottom: 24px;">
            <a href="<?php echo esc_url(wp_get_referer() ?: home_url('/')); ?>" class="was-btn was-btn-outline" style="width: auto; padding: 8px 16px; font-size: 13px; border-radius: 99px;">&larr; Voltar</a>
        </div>

        <div class="tutorial-header">
            <h1 class="tutorial-title">Como Funciona?</h1>
            <p class="tutorial-subtitle">Do cadastro do organizador até a consulta do resultado por PIN.</p>
        </div>

        <div class="tutorial-step">
            <div class="step-header">
                <div class="step-number">1</div>
                <div class="step-info">
                    <h3 class="step-title">Crie sua Conta Grátis</h3>
                    <p class="step-desc">Acesse a plataforma e crie sua conta em menos de 1 minuto. Você será o Organizador do evento.</p>
                </div>
            </div>
        </div>

        <div class="tutorial-step">
            <div class="step-header">
                <div class="step-number">2</div>
                <div class="step-info">
                    <h3 class="step-title">Abra uma Sala de Sorteio</h3>
                    <p class="step-desc">Clique em “Novo sorteio” e dê um nome ao grupo. Você pode usar <i>Amigo Secreto da Equipe</i>, por exemplo.</p>
                </div>
            </div>
        </div>

        <div class="tutorial-step">
            <div class="step-header">
                <div class="step-number">3</div>
                <div class="step-info">
                    <h3 class="step-title">Convide a Galera</h3>
                    <p class="step-desc">O sistema vai gerar um link único. Compartilhe e chame as pessoas para participarem do amigo oculto. As pessoas vão se cadastrar e receberão um PIN secreto.</p>
                </div>
            </div>
        </div>

        <div class="tutorial-step">
            <div class="step-header">
                <div class="step-number">4</div>
                <div class="step-info">
                    <h3 class="step-title">Faça o Sorteio</h3>
                    <p class="step-desc">Quando todos entrarem, você (Organizador) aperta o botão "Realizar Sorteio". Nosso gerador de amigo secreto garante que ninguém tire a si mesmo.</p>
                </div>
            </div>
        </div>

        <div class="tutorial-step was-tutorial-final">
            <div class="step-header">
                <div class="step-number">5</div>
                <div class="step-info">
                    <h3 class="step-title">A Revelação</h3>
                    <p class="step-desc">Depois do sorteio, o resultado é enviado por e-mail. Basta acessar o link novamente, digitar o PIN secreto e descobrir quem tirou!</p>
                </div>
            </div>
        </div>

        <div style="margin-top: 40px; text-align: center;">
            <a href="<?php echo esc_url(was_site_dashboard_url(true)); ?>" class="was-btn was-btn-primary">Criar Meu Sorteio Agora</a>
        </div>

    </div>
</div>
