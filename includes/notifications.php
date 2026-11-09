<?php

if (!defined('ABSPATH')) {
    exit;
}

function was_send_html_email($recipient, $subject, $body) {
    ob_start();
    require dirname(__DIR__) . '/views/email.php';
    $message = ob_get_clean();
    return wp_mail($recipient, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
}

function was_send_password_reset($email) {
    $user = get_user_by('email', $email);
    if (!$user) {
        return;
    }

    $key = get_password_reset_key($user);
    if (is_wp_error($key)) {
        return;
    }
    $link = network_site_url('wp-login.php', 'login');
    $link = add_query_arg(['action' => 'rp', 'key' => $key, 'login' => rawurlencode($user->user_login)], $link);
    $body = '<p>Alguém solicitou a redefinição da senha da sua conta.</p>';
    $body .= '<p><a style="color:#c084fc; font-weight:bold;" href="' . esc_url($link) . '">Redefinir minha senha</a></p>';
    $body .= '<p>Se você não solicitou a redefinição, ignore este e-mail.</p>';
    was_send_html_email($email, 'Recuperação de Senha', $body);
}

function was_send_registration_email($group, $person) {
    $body = '<p>Olá, <strong>' . esc_html($person['name']) . '</strong>!</p>';
    $body .= '<p>Sua inscrição no Amigo Secreto <strong>' . esc_html($group->title) . '</strong> está confirmada.</p>';
    $body .= '<p>Seu PIN secreto é <strong style="font-size:32px; letter-spacing:6px; color:#fff;">' . esc_html($person['token']) . '</strong>.</p>';
    $body .= '<p>Guarde este e-mail. Você usará o PIN para revelar seu amigo secreto após o sorteio.</p>';
    return was_send_html_email($person['email'], 'Confirmação - ' . $group->title, $body);
}

function was_send_result_notifications($group, array $people) {
    $link = esc_url(get_permalink($group->page_id));
    $group_title = esc_html($group->title);
    $subject = 'Resultado: ' . $group->title;
    $failed = 0;

    foreach ($people as $person) {
        $body = '<p>Olá, <strong>' . esc_html($person->name) . '</strong>!</p>';
        $body .= '<p>O sorteio do Amigo Secreto <strong>' . $group_title . '</strong> foi realizado.</p>';
        $body .= '<p>Seu amigo secreto é <strong style="font-size:22px; color:#fff;">🎉 ' . esc_html($person->friend_name) . ' 🎉</strong></p>';
        $body .= '<p><a style="color:#c084fc; font-weight:bold;" href="' . $link . '">Consulte o resultado novamente usando seu PIN</a>.</p>';
        if (!was_send_html_email($person->email, $subject, $body)) {
            $failed++;
        }
    }

    return $failed;
}
