<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('parse_request', 'was_site_machine_routes');
add_action('template_redirect', 'was_site_markdown_negotiation', 1);
add_action('wp_head', 'was_site_markdown_alternate');
add_action('wp_enqueue_scripts', 'was_site_enqueue_webmcp');
add_filter('litespeed_optm_js_defer_exc', 'was_site_webmcp_no_delay');
add_filter('litespeed_optm_gm_js_exc', 'was_site_webmcp_no_delay');

function was_site_media_quality($accept, $type, $exact = false) {
    $types = [$type, strstr($type, '/', true) . '/*', '*/*'];
    $priority = count($types);
    $quality = 0.0;
    foreach (explode(',', strtolower($accept)) as $range) {
        $parts = array_map('trim', explode(';', $range));
        $match = array_search(array_shift($parts), $types, true);
        if ($match === false || $match > $priority || ($exact && $match !== 0)) {
            continue;
        }
        $weight = 1.0;
        foreach ($parts as $parameter) {
            if (preg_match('/^q\s*=\s*(.*)$/', $parameter, $value)) {
                $weight = is_numeric($value[1]) && $value[1] >= 0 && $value[1] <= 1 ? (float) $value[1] : 0.0;
                break;
            }
        }
        $quality = $match < $priority ? $weight : max($quality, $weight);
        $priority = $match;
    }
    return $quality;
}

function was_site_markdown_negotiation() {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, ['GET', 'HEAD'], true) || !is_front_page() || is_feed()
        || is_embed() || is_preview() || was_site_view() !== 'home' || !was_site_public_enabled()) {
        return;
    }

    header('Vary: Accept', false);
    $accept = was_request_value($_SERVER, 'HTTP_ACCEPT');
    $markdown = was_site_media_quality($accept, 'text/markdown', true);
    if ($markdown > 0 && $markdown >= was_site_media_quality($accept, 'text/html')) {
        was_site_send_markdown($method, true);
    }
}

function was_site_markdown_alternate() {
    if (is_front_page() && was_site_public_enabled()) {
        printf("<link rel=\"alternate\" type=\"text/markdown\" href=\"%s\">\n", esc_url(home_url('/index.md')));
    }
}

function was_site_machine_routes() {
    $path = wp_parse_url(was_request_value($_SERVER, 'REQUEST_URI'), PHP_URL_PATH);
    $root = untrailingslashit(wp_parse_url(home_url('/'), PHP_URL_PATH) ?: '/');
    if (!$path || strpos($path, $root . '/') !== 0) {
        return;
    }
    $route = untrailingslashit(substr($path, strlen($root)));
    $routes = ['/index.md', '/llms.txt', '/mcp', '/mcp/server-card', '/.well-known/mcp/server-card.json', '/.well-known/ai-catalog.json'];
    if (!in_array($route, $routes, true) || !was_site_public_enabled()) {
        return;
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    header('X-Robots-Tag: noindex');
    header('X-Content-Type-Options: nosniff');
    if ($route === '/mcp') {
        require_once __DIR__ . '/site-mcp.php';
        was_site_mcp_handle($method);
    }
    if (!in_array($method, ['GET', 'HEAD'], true)) {
        header('Allow: GET, HEAD');
        status_header(405);
        exit;
    }

    if ($route === '/index.md') {
        was_site_send_markdown($method);
    }
    status_header(200);
    header('Cache-Control: public, max-age=300');
    if ($route === '/llms.txt') {
        header('Content-Type: text/plain; charset=UTF-8');
        if ($method === 'GET') {
            echo was_site_llms_text();
        }
    } else {
        require_once __DIR__ . '/site-mcp.php';
        [$type, $document] = was_site_mcp_discovery($route);
        header('Content-Type: ' . $type);
        if ($method === 'GET') {
            echo wp_json_encode($document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    }
    exit;
}

function was_site_send_markdown($method, $negotiated = false) {
    status_header(200);
    header('Content-Type: text/markdown; charset=UTF-8');
    header('Content-Language: ' . get_bloginfo('language'));
    if ($negotiated) {
        nocache_headers();
        do_action('litespeed_control_set_nocache', 'Amigo Secreto Markdown negotiation');
    } else {
        header('Cache-Control: public, max-age=300');
    }
    if ($method === 'GET') {
        echo was_site_home_markdown();
    }
    exit;
}

function was_site_markdown_escape($text) {
    return strtr($text, [
        '\\' => '\\\\', '[' => '\\[', ']' => '\\]', '*' => '\\*', '_' => '\\_',
        '`' => '\\`', '<' => '&lt;', '>' => '&gt;',
    ]);
}

function was_site_markdown_link($label, $url) {
    return '[' . was_site_markdown_escape($label) . '](' . str_replace(['(', ')'], ['%28', '%29'], esc_url_raw($url)) . ')';
}

function was_site_markdown_links(array $links) {
    $lines = [];
    foreach ($links as $label => $url) {
        $lines[] = '- ' . was_site_markdown_link($label, $url);
    }
    return implode("\n", $lines);
}

function was_site_markdown_articles(array $posts) {
    $lines = [];
    foreach ($posts as $post) {
        $link = was_site_markdown_link(was_site_plain_text(get_the_title($post)), get_permalink($post));
        $excerpt = was_site_excerpt($post);
        $lines[] = '- ' . $link . ($excerpt ? ' — ' . was_site_markdown_escape($excerpt) : '');
    }
    return implode("\n", $lines);
}

function was_site_guide_markdown() {
    $lines = ['## Como funciona', ''];
    foreach (was_site_steps() as $i => [$title, $body]) {
        $lines[] = ($i + 1) . '. **' . $title . '** — ' . $body;
    }
    $lines[] = "\n## Perguntas frequentes";
    foreach (was_site_questions() as [$question, $answer]) {
        $lines[] = "\n### " . $question . "\n\n" . $answer;
    }
    return implode("\n", $lines);
}

function was_site_home_markdown() {
    $links = ['Criar conta' => was_site_auth_url('register', true), 'Entrar' => was_site_auth_url('login')] + was_site_public_links();
    $links += was_site_contact_links();
    $articles = was_site_markdown_articles(was_site_public_articles(3));
    return "# Sorteio de Amigo Secreto Online Grátis\n\n"
        . was_site_intro() . "\n\n" . was_site_guide_markdown()
        . "\n\n## Páginas do site\n\n" . was_site_markdown_links($links)
        . ($articles ? "\n\n## Dicas para a troca de presentes\n\n" . $articles : '') . "\n";
}

function was_site_llms_text() {
    $links = ['Página inicial em Markdown' => home_url('/index.md')] + was_site_public_links();
    $articles = was_site_markdown_articles(was_site_public_articles(6));
    $discovery = ['MCP Server Card' => home_url('/mcp/server-card')];
    if (wp_sitemaps_get_server()->sitemaps_enabled()) {
        $discovery['Sitemap XML'] = wp_sitemaps_get_server()->index->get_index_url();
    }
    return '# ' . was_site_markdown_escape(was_site_plain_text(was_site_brand()))
        . "\n\n> " . was_site_description()
        . "\n\n## Páginas principais\n\n" . was_site_markdown_links($links)
        . ($articles ? "\n\n## Artigos recentes\n\n" . $articles : '')
        . "\n\n## Optional\n\n" . was_site_markdown_links($discovery) . "\n";
}

function was_site_enqueue_webmcp() {
    if (!is_front_page() || !was_site_public_enabled()) {
        return;
    }
    require_once __DIR__ . '/site-mcp.php';
    wp_enqueue_script('was-site-webmcp', plugins_url('assets/webmcp.js', WAS_PLUGIN_FILE), [], WAS_VERSION, true);
    wp_add_inline_script('was-site-webmcp', 'window.wasSiteWebMcp = ' . wp_json_encode([
        'endpoint' => home_url('/mcp'),
        'protocolVersion' => was_site_mcp_versions()[0],
        'tools' => was_site_mcp_tools(),
    ], JSON_HEX_TAG | JSON_HEX_AMP) . ';', 'before');
}

function was_site_webmcp_no_delay($excludes) {
    $excludes[] = '/assets/webmcp.js';
    $excludes[] = 'window.wasSiteWebMcp';
    return $excludes;
}
