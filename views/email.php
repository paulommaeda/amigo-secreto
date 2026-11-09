<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<body style='background-color: #0b0f19; color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; padding: 40px 20px; margin: 0; line-height: 1.6;'>
    <div style='max-width: 600px; margin: 0 auto; background-color: #111827; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 8px 32px rgba(0,0,0,0.3);'>
        <div style='background: linear-gradient(90deg, #ff4d6d, #a855f7); padding: 30px 20px; text-align: center;'>
            <h1 style='color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;'>🎁 Amigo Secreto</h1>
        </div>
        <div style='padding: 40px 30px;'>
            <h2 style='margin-top: 0; color: #ffffff; font-size: 20px; text-align: center; margin-bottom: 30px;'><?php echo esc_html($subject); ?></h2>
            <div style='color: #cbd5e1; font-size: 16px;'>
                <?php echo $body; ?>
            </div>
        </div>
        <div style='text-align: center; padding: 20px; color: #64748b; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.05); background: #0f172a;'>
            Este é um e-mail automático. Por favor, não responda.<br>
            Organize seu sorteio de forma simples e rápida.
        </div>
    </div>
</body>
</html>
