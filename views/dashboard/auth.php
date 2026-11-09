<?php

if (!defined('ABSPATH')) {
    exit;
}

$copy = [
    'login' => ['Acesso do organizador', 'Entre para continuar seu sorteio.', 'Abra seus grupos, acompanhe as inscrições e gerencie os convites em um só lugar.', 'Entrar na minha conta'],
    'register' => ['Conta do organizador', 'Crie sua conta e abra seu grupo.', 'Depois do cadastro, dê um nome ao sorteio e compartilhe o link com seus convidados.', 'Criar conta'],
    'recover' => ['Recuperação de acesso', 'Volte ao seu painel.', 'Use o e-mail cadastrado para receber um link de redefinição de senha.', 'Recuperar senha'],
];
[$eyebrow, $headline, $intro, $form_title] = $copy[$view];
$id = wp_unique_id('was-auth-');
$email_field = $view === 'login' ? 'log' : 'user_email';
$password_field = $view === 'login' ? 'pwd' : 'user_pass';
$email = $was_auth_view === $view ? was_request_value($_POST, $email_field) : '';
$name = $was_auth_view === 'register' ? was_request_value($_POST, 'user_name') : '';
?>
<div class="was-auth-layout">
    <section class="was-auth-intro" aria-labelledby="<?php echo esc_attr($id . 'heading'); ?>">
        <p class="was-auth-eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h1 id="<?php echo esc_attr($id . 'heading'); ?>"><?php echo esc_html($headline); ?></h1>
        <p class="was-auth-description"><?php echo esc_html($intro); ?></p>
        <div class="was-auth-illustration"><?php was_site_icon($view === 'recover' ? 'lock' : 'gift'); ?></div>
    </section>

    <section class="was-auth-card" aria-labelledby="<?php echo esc_attr($id . 'title'); ?>">
        <h2 id="<?php echo esc_attr($id . 'title'); ?>"><?php echo esc_html($form_title); ?></h2>
        <?php if ($view === 'register') : ?>
            <p class="was-auth-card-intro">A conta é para quem organiza. Se você recebeu um convite, abra o link do seu sorteio para participar.</p>
        <?php endif; ?>
        <?php echo $was_auth_notice ?? ''; ?>

        <form method="post" data-was-panel="<?php echo esc_attr($view); ?>" class="was-auth-form">
            <?php wp_nonce_field('was_' . $view . '_nonce'); ?>
            <input type="hidden" name="was_auth_action" value="<?php echo esc_attr($view); ?>">

            <?php if ($view === 'register') : ?>
                <div class="was-auth-field">
                    <label for="<?php echo esc_attr($id . 'name'); ?>">Nome completo</label>
                    <input id="<?php echo esc_attr($id . 'name'); ?>" type="text" name="user_name" autocomplete="name" value="<?php echo esc_attr($name); ?>" required>
                </div>
            <?php endif; ?>

            <div class="was-auth-field">
                <label for="<?php echo esc_attr($id . 'email'); ?>"><?php echo $view === 'recover' ? 'E-mail cadastrado' : 'E-mail'; ?></label>
                <input id="<?php echo esc_attr($id . 'email'); ?>" type="email" name="<?php echo esc_attr($email_field); ?>" autocomplete="<?php echo $view === 'login' ? 'username' : 'email'; ?>" value="<?php echo esc_attr($email); ?>" placeholder="voce@exemplo.com" required>
            </div>

            <?php if ($view !== 'recover') : ?>
                <div class="was-auth-field">
                    <label for="<?php echo esc_attr($id . 'password'); ?>"><?php echo $view === 'register' ? 'Crie uma senha' : 'Senha'; ?></label>
                    <div class="was-auth-password">
                        <input id="<?php echo esc_attr($id . 'password'); ?>" type="password" name="<?php echo esc_attr($password_field); ?>" autocomplete="<?php echo $view === 'register' ? 'new-password' : 'current-password'; ?>"<?php if ($view === 'register') : ?> minlength="10" aria-describedby="<?php echo esc_attr($id . 'hint'); ?>"<?php endif; ?> required>
                        <button type="button" data-was-password-toggle aria-controls="<?php echo esc_attr($id . 'password'); ?>" aria-label="Mostrar ou ocultar senha" aria-pressed="false">Mostrar</button>
                    </div>
                    <?php if ($view === 'register') : ?>
                        <p id="<?php echo esc_attr($id . 'hint'); ?>" class="was-auth-hint">Use pelo menos 10 caracteres, incluindo um número.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($view === 'register') : ?>
                <div class="was-auth-field">
                    <label for="<?php echo esc_attr($id . 'confirm'); ?>">Confirme a senha</label>
                    <div class="was-auth-password">
                        <input id="<?php echo esc_attr($id . 'confirm'); ?>" type="password" name="user_pass_confirm" autocomplete="new-password" required>
                        <button type="button" data-was-password-toggle aria-controls="<?php echo esc_attr($id . 'confirm'); ?>" aria-label="Mostrar ou ocultar confirmação de senha" aria-pressed="false">Mostrar</button>
                    </div>
                </div>

                <?php if ($terms_url) : ?>
                    <div class="was-form-check">
                        <input id="<?php echo esc_attr($id . 'terms'); ?>" type="checkbox" name="was_accept_terms" value="1" <?php checked(was_request_value($_POST, 'was_accept_terms'), '1'); ?> required>
                        <label for="<?php echo esc_attr($id . 'terms'); ?>">
                            Aceito os <a href="<?php echo esc_url($terms_url); ?>" target="_blank" rel="noopener">Termos de Uso</a><?php if ($privacy_url) : ?> e li a <a href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener">Política de Privacidade</a><?php endif; ?>.
                        </label>
                    </div>
                <?php endif; ?>
            <?php elseif ($view === 'login') : ?>
                <a class="was-auth-recover" href="<?php echo esc_url(was_site_auth_url('recover', $create)); ?>">Esqueceu a senha?</a>
            <?php endif; ?>

            <button type="submit" class="was-auth-submit">
                <?php echo esc_html(['login' => 'Entrar', 'register' => 'Criar conta e continuar', 'recover' => 'Enviar link'][$view]); ?>
                <?php was_site_icon('arrow'); ?>
            </button>
        </form>

        <p class="was-auth-alternative">
            <?php if ($view === 'login') : ?>
                Primeira vez aqui? <a href="<?php echo esc_url($register_url); ?>">Criar conta</a>
            <?php elseif ($view === 'register') : ?>
                Já tem conta? <a href="<?php echo esc_url($login_url); ?>">Entrar</a>
            <?php else : ?>
                <a href="<?php echo esc_url($login_url); ?>">Voltar para o login</a>
            <?php endif; ?>
        </p>
    </section>
</div>
