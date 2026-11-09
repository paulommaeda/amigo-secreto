<?php

if (!defined('ABSPATH')) {
    exit;
}

function was_site_brand() {
    return get_bloginfo('name') ?: 'Seu Amigo Secreto';
}

function was_site_contact_email() {
    return 'contato@paulomaeda.com';
}

function was_site_business_settings() {
    return wp_parse_args(get_option('was_site_business', []), [
        'telephone' => '+5541992087325',
        'streetAddress' => '',
        'postalCode' => '',
    ]);
}

function was_site_business_address() {
    $business = was_site_business_settings();
    return [
        '@type' => 'PostalAddress',
        'addressLocality' => 'Curitiba',
        'addressRegion' => 'PR',
        'addressCountry' => 'BR',
    ] + array_filter(array_intersect_key($business, ['streetAddress' => true, 'postalCode' => true]));
}

function was_site_contact_links() {
    return [
        'E-mail de suporte' => 'mailto:' . was_site_contact_email(),
        'Telefone de suporte' => 'tel:' . was_site_business_settings()['telephone'],
    ];
}

function was_site_description() {
    return 'Crie um sorteio de amigo secreto grátis, convide pelo WhatsApp e consulte o resultado por e-mail ou com um PIN de quatro dígitos.';
}

function was_site_intro() {
    return 'Crie o grupo, convide pelo WhatsApp e sorteie quando todos entrarem. Cada participante recebe um PIN para consultar o resultado.';
}

function was_site_steps() {
    return [
        ['Abra o seu grupo', 'Crie sua conta de organizador, dê um nome ao grupo e abra as inscrições.'],
        ['Envie o convite', 'Compartilhe o link pelo WhatsApp. Cada pessoa preenche seus dados e recebe um PIN.'],
        ['Realize o sorteio', 'Confira os inscritos no painel. Com pelo menos três pessoas, clique em Realizar sorteio.'],
        ['Descubra quem saiu', 'Os resultados são enviados por e-mail. Cada participante também pode consultar o seu pelo link e pelo PIN.'],
    ];
}

function was_site_questions() {
    return [
        ['O sorteio de amigo secreto é grátis?', 'Sim. Criar a conta, organizar o sorteio e consultar o resultado são serviços gratuitos.'],
        ['Quem precisa de conta?', 'O organizador usa uma conta para criar e gerenciar os grupos. Os convidados se inscrevem pelo link com nome, e-mail e WhatsApp.'],
        ['Quantas pessoas podem participar?', 'São necessárias pelo menos três pessoas. Confira os inscritos no painel antes de realizar o sorteio.'],
        ['Como consulto quem eu tirei?', 'Abra o link do convite depois do sorteio e digite o seu PIN de quatro dígitos. Você também pode conferir o resultado enviado ao seu e-mail.'],
        ['Como recupero o PIN?', 'Abra o link do evento e selecione Esqueci meu PIN. Informe o e-mail e o WhatsApp usados na inscrição.'],
        ['Posso refazer o sorteio?', 'O organizador pode reabrir as inscrições pelo painel e realizar um novo sorteio.'],
    ];
}

function was_site_public_enabled() {
    $home = was_site_page_id('home');
    return $home && (int) get_option('blog_public') && get_option('show_on_front') === 'page'
        && $home === (int) get_option('page_on_front') && get_post_field('post_password', $home) === '';
}

function was_site_information_links() {
    return array_filter([
        'Sobre' => was_site_page_url('about'),
        'Contato' => was_site_page_url('contact') ?: home_url('/#contato'),
        'Termos de Uso' => was_site_page_url('terms'),
        'Política de Privacidade' => was_site_page_url('privacy'),
    ]);
}

function was_site_public_links() {
    return array_filter([
        'Como funciona' => was_site_page_id('tutorial') ? was_site_page_url('tutorial') : home_url('/#como-funciona'),
        'Blog' => was_site_page_id('blog') ? was_site_page_url('blog') : '',
    ]) + was_site_information_links();
}

function was_site_purge_information_cache($include_robots = false) {
    $urls = [home_url('/'), home_url('/index.md'), home_url('/llms.txt')];
    if ($include_robots) {
        $urls[] = home_url('/robots.txt');
        $urls[] = wp_sitemaps_get_server()->index->get_index_url();
        foreach (was_site_information_paths() as [$slug]) {
            $urls[] = was_site_information_path_url($slug);
        }
    }
    foreach (['about', 'contact', 'terms', 'privacy'] as $role) {
        $url = was_site_page_url($role);
        if ($url) {
            $urls[] = $url;
        }
    }
    foreach (array_unique($urls) as $url) {
        do_action('litespeed_purge_url', $url);
    }
}

function was_site_public_articles($limit, $search = '', $orderby = 'date') {
    return get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'has_password' => false,
        's' => $search,
        'orderby' => $orderby,
        'ignore_sticky_posts' => true,
        'suppress_filters' => false,
        'was_site_public_only' => true,
    ]);
}

function was_site_plain_text($value) {
    $value = strip_shortcodes((string) $value);
    $value = preg_replace('~</(?:p|div|li|h[1-6])>|<br\s*/?>~i', ' ', $value);
    $value = html_entity_decode(wp_strip_all_tags($value, true), ENT_QUOTES, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
}

function was_site_excerpt($post) {
    $excerpt = was_site_plain_text($post->post_excerpt);
    return wp_trim_words($excerpt ?: was_site_plain_text($post->post_content), 26, '…');
}
