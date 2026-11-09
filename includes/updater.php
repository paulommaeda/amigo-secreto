<?php

if (!defined('ABSPATH')) {
    exit;
}

const WAS_GITHUB_REPO = 'paulommaeda/amigo-secreto';
const WAS_GITHUB_BRANCH = 'main';
const WAS_UPDATE_CACHE = 'was_github_version';
const WAS_UPDATE_SLUG = 'wp-amigo-secreto-pro-github';

add_filter('update_plugins_github.com', 'was_github_plugin_update', 10, 3);
add_filter('plugins_api', 'was_github_plugin_information', 10, 3);
add_filter('upgrader_source_selection', 'was_keep_plugin_directory', 10, 4);
add_action('load-update-core.php', 'was_maybe_clear_update_cache', 0);

function was_github_plugin_update($update, $plugin_data, $plugin_file) {
    if ($plugin_file !== plugin_basename(WAS_PLUGIN_FILE) || dirname($plugin_file) === '.') {
        return $update;
    }

    $release = was_get_github_version();
    if (!$release) {
        return $update;
    }

    return [
        'slug' => WAS_UPDATE_SLUG,
        'version' => $release['version'],
        'url' => 'https://github.com/' . WAS_GITHUB_REPO,
        'package' => 'https://github.com/' . WAS_GITHUB_REPO . '/archive/' . $release['sha'] . '.zip',
        'requires' => $release['requires'],
        'requires_php' => $release['requires_php'],
    ];
}

function was_github_plugin_information($result, $action, $args) {
    if ($action !== 'plugin_information' || ($args->slug ?? '') !== WAS_UPDATE_SLUG) {
        return $result;
    }

    $release = was_get_github_version();
    $repository_url = 'https://github.com/' . WAS_GITHUB_REPO;
    return (object) [
        'name' => 'WP Amigo Secreto Pro',
        'slug' => WAS_UPDATE_SLUG,
        'version' => $release['version'] ?? WAS_VERSION,
        'homepage' => $repository_url,
        'requires' => $release['requires'] ?? '5.8',
        'requires_php' => $release['requires_php'] ?? '7.4',
        'sections' => [
            'description' => 'Organize sorteios de amigo secreto com convites via WhatsApp e revelação por PIN.',
            'changelog' => '<p><a href="' . esc_url($repository_url . '/commits/' . WAS_GITHUB_BRANCH) . '">Ver alterações no GitHub</a></p>',
        ],
    ];
}

function was_get_github_version() {
    $release = get_site_transient(WAS_UPDATE_CACHE);
    if ($release === false) {
        $release = was_read_github_version();
        set_site_transient(WAS_UPDATE_CACHE, $release, $release ? HOUR_IN_SECONDS : 5 * MINUTE_IN_SECONDS);
    }
    return $release;
}

function was_read_github_version() {
    $entrypoint = basename(WAS_PLUGIN_FILE);
    $commits_url = add_query_arg([
        'sha' => WAS_GITHUB_BRANCH,
        'path' => $entrypoint,
        'per_page' => 1,
    ], 'https://api.github.com/repos/' . WAS_GITHUB_REPO . '/commits');

    $response = wp_safe_remote_get($commits_url, ['timeout' => 5, 'limit_response_size' => 65536]);
    if (wp_remote_retrieve_response_code($response) !== 200) {
        return [];
    }

    $commits = json_decode(wp_remote_retrieve_body($response), true);
    $sha = $commits[0]['sha'] ?? '';
    if (!is_string($sha) || !preg_match('/\A[a-f0-9]{40}\z/', $sha)) {
        return [];
    }

    // Read the header and download the ZIP from the same immutable commit.
    $file_url = 'https://raw.githubusercontent.com/' . WAS_GITHUB_REPO . '/' . $sha . '/' . $entrypoint;
    $response = wp_safe_remote_get($file_url, ['timeout' => 5, 'limit_response_size' => 8192]);
    if (wp_remote_retrieve_response_code($response) !== 200) {
        return [];
    }

    $header = wp_remote_retrieve_body($response);
    if (!preg_match('/^[\t *]*Version:[\t ]*(\d+(?:\.\d+){1,2})\s*$/mi', $header, $version)) {
        return [];
    }

    preg_match('/^[\t *]*Requires at least:[\t ]*(\d+(?:\.\d+){1,2})\s*$/mi', $header, $requires);
    preg_match('/^[\t *]*Requires PHP:[\t ]*(\d+(?:\.\d+){1,2})\s*$/mi', $header, $requires_php);

    return [
        'sha' => $sha,
        'version' => $version[1],
        'requires' => $requires[1] ?? '',
        'requires_php' => $requires_php[1] ?? '',
    ];
}

function was_maybe_clear_update_cache() {
    if (current_user_can('update_plugins') && isset($_GET['force-check'])) {
        delete_site_transient(WAS_UPDATE_CACHE);
        delete_site_transient('update_plugins');
    }
}

function was_keep_plugin_directory($source, $remote_source, $upgrader, $hook_extra) {
    global $wp_filesystem;

    $plugin = plugin_basename(WAS_PLUGIN_FILE);
    if (is_wp_error($source) || ($hook_extra['plugin'] ?? '') !== $plugin) {
        return $source;
    }

    $directory = dirname($plugin);
    $source = trailingslashit($source);
    if ($directory === '.' || !$wp_filesystem->is_file($source . basename($plugin))) {
        return new WP_Error('was_invalid_update', 'O pacote do Amigo Secreto está incompleto.');
    }
    if (basename(untrailingslashit($source)) === $directory) {
        return $source;
    }

    $destination = trailingslashit($remote_source) . $directory;
    if (!$wp_filesystem->move(untrailingslashit($source), $destination)) {
        return new WP_Error('was_update_directory', 'Não foi possível preparar a pasta do Amigo Secreto.');
    }

    return trailingslashit($destination);
}
