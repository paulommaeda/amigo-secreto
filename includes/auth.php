<?php

if (!defined('ABSPATH')) {
    exit;
}

function was_process_auth_forms() {
    global $was_auth_notice, $was_auth_view;

    if (is_user_logged_in()) {
        return;
    }
    $action = was_request_value($_POST, 'was_auth_action');
    if (!in_array($action, ['login', 'register', 'recover'], true)) {
        return;
    }
    $was_auth_view = $action;
    if (!wp_verify_nonce(was_request_value($_POST, '_wpnonce'), 'was_' . $action . '_nonce')) {
        $was_auth_notice = was_notice_markup('Sessão expirada. Atualize a página e tente novamente.');
        return;
    }

    if ($action === 'login') {
        $user = wp_signon([
            'user_login' => sanitize_text_field(was_request_value($_POST, 'log')),
            'user_password' => was_request_value($_POST, 'pwd'),
            'remember' => true,
        ], is_ssl());
        if (is_wp_error($user)) {
            $was_auth_notice = was_notice_markup('E-mail ou senha incorretos.');
            return;
        }
    } elseif ($action === 'register') {
        $user_id = was_register_organizer();
        if (is_wp_error($user_id)) {
            $was_auth_notice = was_notice_markup($user_id->get_error_message());
            return;
        }
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
    } else {
        require_once __DIR__ . '/notifications.php';
        was_send_password_reset(sanitize_email(was_request_value($_POST, 'user_email')));
        $was_auth_notice = was_notice_markup('Se o e-mail estiver cadastrado, enviaremos um link de redefinição.', true);
        return;
    }

    $role = was_site_current_role(['login', 'register']);
    $create = $action === 'register' || was_request_value($_GET, 'was_act') === 'create';
    $destination = $role ? was_site_account_destination($create) : remove_query_arg(['was_auth_action', 'was_auth']);
    wp_safe_redirect($destination, 303);
    exit;
}

function was_register_organizer() {
    $name = sanitize_text_field(was_request_value($_POST, 'user_name'));
    $email = sanitize_email(was_request_value($_POST, 'user_email'));
    $password = was_request_value($_POST, 'user_pass');
    if ($name === '' || !is_email($email) || $password === '') {
        return new WP_Error('was_invalid_account', 'Informe seu nome, um e-mail válido e uma senha.');
    }
    if (strlen($password) < 10 || !preg_match('/[0-9]/', $password)) {
        return new WP_Error('was_short_password', 'Use uma senha com pelo menos 10 caracteres e um número.');
    }
    if ($password !== was_request_value($_POST, 'user_pass_confirm')) {
        return new WP_Error('was_password_mismatch', 'As senhas digitadas não coincidem.');
    }
    if (was_site_page_id('terms') && was_request_value($_POST, 'was_accept_terms') !== '1') {
        return new WP_Error('was_terms_required', 'Aceite os Termos de Uso para criar a conta.');
    }
    if (email_exists($email)) {
        return new WP_Error('was_existing_account', 'Este e-mail já está em uso. Faça login.');
    }

    $base = sanitize_user(explode('@', $email, 2)[0], true) ?: 'organizador';
    $username = substr($base, 0, 60);
    for ($suffix = 1; username_exists($username); $suffix++) {
        $username = substr($base, 0, 60 - strlen((string) $suffix)) . $suffix;
    }

    return wp_insert_user([
        'user_login' => $username,
        'user_email' => $email,
        'user_pass' => $password,
        'display_name' => $name,
        'first_name' => $name,
        'role' => 'subscriber',
    ]);
}

function was_render_auth() {
    global $was_auth_notice, $was_auth_view;

    $role = was_site_current_role(['login', 'register']);
    $view = $was_auth_view ?? was_request_value($_GET, 'was_auth');
    if (!in_array($view, ['login', 'register', 'recover'], true)) {
        $view = $role === 'register' ? 'register' : 'login';
    }
    $create = $role === 'register' || was_request_value($_GET, 'was_act') === 'create';
    $login_url = was_site_auth_url('login', $create);
    $register_url = was_site_auth_url('register', true);
    $terms_url = was_site_page_url('terms');
    $privacy_url = was_site_page_url('privacy');

    ob_start();
    require dirname(__DIR__) . '/views/dashboard/auth.php';
    return ob_get_clean();
}
