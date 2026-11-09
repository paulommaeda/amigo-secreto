<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div style="padding: 40px 16px 20px 16px; text-align: center;">
    <div style="font-size: 56px; margin-bottom: 10px; filter: drop-shadow(0 0 10px rgba(255,77,109,0.5));">🎁</div>
    <h1 style="margin: 0 0 5px 0; font-size: 28px; font-weight: 800;">Amigo <span class="text-gradient">Secreto</span></h1>
    <p style="color: var(--was-text-light); font-size: 15px; margin:0 0 20px 0;">O melhor <strong>gerador de sorteio de Amigo Secreto online</strong>.</p>

    <a href="<?php echo esc_url(home_url('/como-funciona/')); ?>" class="was-btn was-btn-outline" style="font-size: 13px; padding: 8px 20px; width: auto; border-radius: 99px; border-color: rgba(255,255,255,0.15);">Como funciona?</a>
</div>

<?php echo $was_auth_notice ?? ''; ?>

<div class="was-card" data-was-switcher style="margin-top: 0; padding: 24px 20px;">
    <div class="was-auth-tabs">
        <button type="button" class="was-auth-tab active" data-was-view="login">Acessar</button>
        <button type="button" class="was-auth-tab" data-was-view="register">Criar Conta</button>
    </div>

    <!-- Login -->
    <form data-was-panel="login" method="post">
        <?php wp_nonce_field('was_login_nonce'); ?>
        <input type="hidden" name="was_auth_action" value="login">
        <input type="email" name="log" autocomplete="username" class="was-input" placeholder="Seu E-mail" required>
        <input type="password" name="pwd" autocomplete="current-password" class="was-input" placeholder="Sua Senha" required>
        <div style="text-align: right; margin: -10px 0 15px 0;">
            <a href="#" data-was-view="recover" style="font-size: 13px; color: var(--was-text-light); text-decoration: underline;">Esqueci minha senha</a>
        </div>
        <button type="submit" class="was-btn was-btn-primary was-btn-block">Entrar no Sistema</button>
    </form>

    <!-- Cadastro -->
    <form data-was-panel="register" method="post" hidden>
        <?php wp_nonce_field('was_register_nonce'); ?>
        <input type="hidden" name="was_auth_action" value="register">
        <input type="text" name="user_name" class="was-input" placeholder="Seu Nome Completo" required>
        <input type="email" name="user_email" class="was-input" placeholder="Seu E-mail" required>
        <input type="password" name="user_pass" autocomplete="new-password" class="was-input" placeholder="Crie uma Senha" required>
        <button type="submit" class="was-btn was-btn-primary was-btn-block">Criar Minha Conta</button>
    </form>

    <!-- Recuperação -->
    <form data-was-panel="recover" method="post" hidden>
        <?php wp_nonce_field('was_recover_nonce'); ?>
        <h3 style="margin-top: 0; color: var(--was-text);">Recuperar Senha</h3>
        <p style="font-size: 14px; color: var(--was-text-light);">Enviaremos um link de redefinição.</p>
        <input type="hidden" name="was_auth_action" value="recover">
        <input type="email" name="user_email" class="was-input" placeholder="Seu E-mail cadastrado" required>
        <button type="submit" class="was-btn was-btn-primary was-btn-block">Enviar Link</button>
        <p style="text-align: center; margin-top: 15px;"><a href="#" data-was-view="login" style="font-size: 14px; color: var(--was-text-light);">Voltar</a></p>
    </form>
</div>
