<?php

if (!defined('ABSPATH')) {
    exit;
}

function was_site_mcp_versions() {
    return ['2026-07-28', '2025-11-25', '2025-06-18', '2025-03-26'];
}

function was_site_mcp_identity() {
    return [
        'name' => 'br.com.seuamigosecreto/publico',
        'title' => 'Seu Amigo Secreto',
        'version' => WAS_VERSION,
    ];
}

function was_site_mcp_capabilities() {
    return ['tools' => ['listChanged' => false]];
}

function was_site_mcp_tools() {
    $annotations = ['readOnlyHint' => true, 'destructiveHint' => false, 'openWorldHint' => false];
    return [
        [
            'name' => 'buscar_artigos',
            'title' => 'Buscar artigos do blog',
            'description' => 'Encontra guias, regras e ideias de presentes publicados no blog do Seu Amigo Secreto.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'consulta' => ['type' => 'string', 'minLength' => 2, 'maxLength' => 100, 'description' => 'Palavras-chave para a busca no blog.'],
                ],
                'required' => ['consulta'],
                'additionalProperties' => false,
            ],
            'annotations' => $annotations,
        ],
        [
            'name' => 'guia_do_sorteio',
            'title' => 'Consultar o guia do sorteio',
            'description' => 'Consulta as etapas do sorteio, as perguntas frequentes e os links públicos de suporte.',
            'inputSchema' => ['type' => 'object', 'properties' => (object) [], 'additionalProperties' => false],
            'annotations' => $annotations,
        ],
    ];
}

function was_site_mcp_discovery($route) {
    $identity = was_site_mcp_identity();
    $versions = was_site_mcp_versions();
    $endpoint = home_url('/mcp');
    $description = 'Guia, perguntas frequentes e busca no blog do Seu Amigo Secreto.';
    if ($route === '/.well-known/ai-catalog.json') {
        $host = wp_parse_url(home_url('/'), PHP_URL_HOST);
        return ['application/ai-catalog+json', [
            'specVersion' => '1.0',
            'entries' => [[
                'identifier' => 'urn:air:' . $host . ':mcp:publico',
                'type' => 'application/mcp-server-card+json',
                'url' => home_url('/mcp/server-card'),
            ]],
        ]];
    }
    if ($route === '/.well-known/mcp/server-card.json') {
        return ['application/json', $identity + [
            'description' => $description,
            'protocolVersion' => $versions[1],
            'serverInfo' => $identity,
            'endpoint' => $endpoint,
            'transport' => ['type' => 'streamable-http', 'endpoint' => $endpoint],
            'capabilities' => was_site_mcp_capabilities(),
            'authentication' => ['required' => false],
            'tools' => was_site_mcp_tools(),
        ]];
    }
    return ['application/mcp-server-card+json', $identity + [
        '$schema' => 'https://static.modelcontextprotocol.io/schemas/v1/server-card.schema.json',
        'description' => $description,
        'websiteUrl' => home_url('/'),
        'repository' => ['url' => 'https://github.com/paulommaeda/amigo-secreto', 'source' => 'github'],
        'remotes' => [[
            'type' => 'streamable-http',
            'url' => $endpoint,
            'supportedProtocolVersions' => $versions,
        ]],
    ]];
}

function was_site_mcp_send($status, $message = null) {
    status_header($status);
    if ($message !== null) {
        header('Content-Type: application/json; charset=UTF-8');
        echo wp_json_encode(['jsonrpc' => '2.0'] + $message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    exit;
}

function was_site_mcp_error($status, $id, $code, $message, $data = null) {
    $error = ['code' => $code, 'message' => $message];
    if ($data !== null) {
        $error['data'] = $data;
    }
    was_site_mcp_send($status, ['id' => $id, 'error' => $error]);
}

function was_site_mcp_origin_allowed($origin) {
    $home = wp_parse_url(home_url('/'));
    $allowed = $home['scheme'] . '://' . $home['host'] . (isset($home['port']) ? ':' . $home['port'] : '');
    return strcasecmp($origin, $allowed) === 0;
}

function was_site_mcp_validate_headers($version, $method, $params, $id) {
    $meta = $params->_meta ?? (object) [];
    $name_header = was_request_value($_SERVER, 'HTTP_MCP_NAME');
    if (preg_match('/^=\?base64\?([^?]*)\?=$/D', $name_header, $encoded)) {
        $name_header = base64_decode($encoded[1], true);
    }
    if (was_request_value($_SERVER, 'HTTP_MCP_PROTOCOL_VERSION') !== $version
        || ($meta->{'io.modelcontextprotocol/protocolVersion'} ?? null) !== $version
        || was_request_value($_SERVER, 'HTTP_MCP_METHOD') !== $method
        || ($method === 'tools/call' && $name_header !== ($params->name ?? null))) {
        was_site_mcp_error(400, $id, -32020, 'MCP headers do not match the request.');
    }
    $client = $meta->{'io.modelcontextprotocol/clientInfo'} ?? null;
    if (!is_string($client->name ?? null) || !is_string($client->version ?? null)
        || !is_object($meta->{'io.modelcontextprotocol/clientCapabilities'} ?? null)) {
        was_site_mcp_error(400, $id, -32602, 'Missing MCP client metadata.');
    }
}

function was_site_mcp_handle($http_method) {
    nocache_headers();
    do_action('litespeed_control_set_nocache', 'Amigo Secreto MCP');
    if (isset($_SERVER['HTTP_ORIGIN']) && !was_site_mcp_origin_allowed(was_request_value($_SERVER, 'HTTP_ORIGIN'))) {
        was_site_mcp_send(403);
    }
    if ($http_method !== 'POST') {
        header('Allow: POST');
        was_site_mcp_send(405);
    }
    $accept = was_request_value($_SERVER, 'HTTP_ACCEPT');
    if ($accept !== '' && was_site_media_quality($accept, 'application/json') === 0.0) {
        was_site_mcp_send(406);
    }
    $content_type = strtolower(trim(explode(';', was_request_value($_SERVER, 'CONTENT_TYPE'), 2)[0]));
    if ($content_type !== 'application/json') {
        was_site_mcp_send(415);
    }

    $payload = file_get_contents('php://input', false, null, 0, 32769);
    if ($payload === false || strlen($payload) > 32768) {
        was_site_mcp_send(413);
    }
    $request = json_decode($payload, false, 32);
    if (json_last_error() !== JSON_ERROR_NONE) {
        was_site_mcp_error(400, null, -32700, 'Invalid JSON.');
    }
    $id = $request->id ?? null;
    if (!is_object($request) || ($request->jsonrpc ?? '') !== '2.0' || !is_string($request->method ?? null)
        || (property_exists($request, 'id') && !is_int($id) && !is_string($id))) {
        was_site_mcp_error(400, null, -32600, 'Invalid JSON-RPC request.');
    }
    $params = $request->params ?? (object) [];
    if (!is_object($params)) {
        was_site_mcp_error(400, $id, -32602, 'Expected named parameters.');
    }

    $versions = was_site_mcp_versions();
    $header_version = was_request_value($_SERVER, 'HTTP_MCP_PROTOCOL_VERSION');
    $version = $header_version ?: '2025-03-26';
    if (!in_array($version, $versions, true)) {
        was_site_mcp_error(400, $id, -32022, 'Unsupported protocol version.', ['supported' => $versions, 'requested' => $version]);
    }
    $method = $request->method;
    $modern = $version === $versions[0];
    if ($modern || $method === 'server/discover' || isset($params->_meta->{'io.modelcontextprotocol/protocolVersion'})) {
        was_site_mcp_validate_headers($versions[0], $method, $params, $id);
    }
    if ($id === null) {
        if (!$modern && in_array($method, ['notifications/initialized', 'notifications/cancelled'], true)) {
            was_site_mcp_send(202);
        }
        was_site_mcp_send(400);
    }

    if ($method === 'initialize' && !$modern) {
        $offered = $params->protocolVersion ?? '';
        if (!is_string($offered) || $offered === '') {
            was_site_mcp_error(400, $id, -32602, 'Expected a protocol version.');
        }
        $result = [
            'protocolVersion' => in_array($offered, array_slice($versions, 1), true) ? $offered : $versions[1],
            'serverInfo' => was_site_mcp_identity(),
            'capabilities' => was_site_mcp_capabilities(),
        ];
    } elseif ($method === 'server/discover' && $modern) {
        $result = [
            'supportedVersions' => $versions,
            'capabilities' => was_site_mcp_capabilities(),
            '_meta' => ['io.modelcontextprotocol/serverInfo' => was_site_mcp_identity()],
            'ttlMs' => 300000,
            'cacheScope' => 'public',
        ];
    } elseif ($method === 'tools/list') {
        $result = ['tools' => was_site_mcp_tools()];
        if ($modern) {
            $result += ['ttlMs' => 300000, 'cacheScope' => 'public'];
        }
    } elseif ($method === 'tools/call') {
        $result = was_site_mcp_call($id, $params);
    } elseif ($method === 'ping' && !$modern) {
        $result = (object) [];
    } else {
        was_site_mcp_error($modern ? 404 : 200, $id, -32601, 'Method not found.');
    }

    if ($modern) {
        $result['resultType'] = 'complete';
    }
    was_site_mcp_send(200, ['id' => $id, 'result' => $result]);
}

function was_site_mcp_call($id, $params) {
    $args = $params->arguments ?? (object) [];
    if (!is_object($args)) {
        was_site_mcp_error(400, $id, -32602, 'Expected a tool arguments object.');
    }
    $name = $params->name ?? '';
    if ($name === 'guia_do_sorteio' && !get_object_vars($args)) {
        $links = was_site_public_links() + ['E-mail de suporte' => 'mailto:' . was_site_contact_email()];
        $text = was_site_guide_markdown() . "\n\n## Links públicos\n\n" . was_site_markdown_links($links);
    } elseif ($name === 'buscar_artigos' && is_string($args->consulta ?? null) && count(get_object_vars($args)) === 1) {
        $search = sanitize_text_field($args->consulta);
        if (strlen($search) < 2 || strlen($search) > 400 || preg_match('/^.{2,100}$/uD', $search) !== 1) {
            was_site_mcp_error(400, $id, -32602, 'Informe uma consulta entre 2 e 100 caracteres.');
        }
        $text = was_site_markdown_articles(was_site_public_articles(5, $search)) ?: 'Nenhum artigo público encontrado.';
    } else {
        was_site_mcp_error(400, $id, -32602, 'Ferramenta ou argumentos inválidos.');
    }
    return ['content' => [['type' => 'text', 'text' => $text]]];
}
