<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="was-app" style="display: flex; justify-content: center; align-items: flex-start; min-height: 100vh;">
    <div class="was-app-container" style="padding: 40px 16px;">
        <?php if (!$group) : ?>
            <div class="was-card" style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 10px;">🔒</div>
                <h2 style="margin-top: 0;">Evento Encerrado</h2>
                <p style="color: var(--was-text-light);">O organizador encerrou este sorteio de Amigo Secreto.</p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="was-btn was-btn-primary">Criar meu próprio sorteio</a>
            </div>
        <?php else : ?>
            <div style="text-align: center; margin-bottom: 15px;">
                <div style="font-size: 48px; margin-bottom: 10px; filter: drop-shadow(0 0 12px rgba(255,77,109,0.5));">🎁</div>
                <h1 style="margin: 0; font-size: 26px; color: var(--was-text);">Sorteio de Amigo Secreto<br><?php echo esc_html($group->title); ?></h1>
                <p style="color: var(--was-text-light); margin-top: 8px;">Convide seus amigos pelo WhatsApp e divirta-se!</p>
                <a href="<?php echo esc_url(home_url('/como-funciona/')); ?>" class="was-btn was-btn-outline" style="font-size: 13px; padding: 8px 20px; width: auto; border-radius: 99px;">Como funciona?</a>
            </div>

            <div class="was-card" style="margin: 10px 0 0; width: 100%;">
                <?php echo $result['notice'] ?? ''; ?>

                <?php if (isset($result['pin'])) : ?>
                    <div class="was-alert was-alert-success" style="text-align: center;">
                        <h3 style="margin-top: 0; color: var(--was-success);">Sucesso! Você está dentro.</h3>
                        <p style="color: #fff;">Guarde este PIN para revelar seu amigo secreto após o sorteio.</p>
                        <div class="was-pin-value"><?php echo esc_html($result['pin']); ?></div>
                        <p style="font-size: 13px; margin-bottom: 0; color: var(--was-text-light);">
                            <?php echo $result['sent'] ? 'Enviamos uma cópia para o seu e-mail.' : 'Guarde o PIN exibido aqui. O WordPress não conseguiu enviar a cópia por e-mail.'; ?>
                        </p>
                    </div>
                <?php elseif (isset($result['revealed'])) : ?>
                    <div style="background: rgba(255,255,255,0.05); padding: 30px; border-radius: 16px; border: 1px solid var(--was-border); text-align: center;">
                        <p style="margin: 0; font-size: 18px;">Olá, <strong><?php echo esc_html($result['revealed']->name); ?></strong>!</p>
                        <p style="color: var(--was-text-light);">Seu amigo secreto é</p>
                        <h2 style="font-size: 32px; margin: 20px 0;" class="text-gradient">🎉 <?php echo esc_html($result['revealed']->friend_name); ?> 🎉</h2>
                        <p style="font-size: 14px; margin: 0; color: var(--was-text-light);">🤫 Guarde segredo!</p>
                    </div>
                    <a href="" class="was-btn was-btn-outline" style="margin-top: 24px;">Voltar</a>
                <?php elseif ($group->status === 'open') : ?>
                    <?php require __DIR__ . '/register.php'; ?>
                <?php else : ?>
                    <?php if (isset($result['recovered'])) : ?>
                        <div class="was-alert was-alert-success" style="text-align: center;">
                            <h3 style="margin-top: 0; color: var(--was-success);">PIN Recuperado!</h3>
                            <p style="color: #fff;">Olá, <strong><?php echo esc_html($result['recovered']->name); ?></strong>. Seu PIN é</p>
                            <div class="was-pin-value"><?php echo esc_html($result['recovered']->token); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php require __DIR__ . '/pin.php'; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
