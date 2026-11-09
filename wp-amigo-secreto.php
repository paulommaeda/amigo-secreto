<?php
/**
 * Plugin Name: WP Amigo Secreto Pro (SEO & SaaS Edition)
 * Plugin URI: https://github.com/paulommaeda/amigo-secreto
 * Description: Crie sorteios de amigo secreto com convites via WhatsApp, revelação por PIN e integração com o FluentCRM.
 * Version: 22.4.1
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Paulo Maeda
 * Update URI: https://github.com/paulommaeda/amigo-secreto
 */

if (!defined('ABSPATH')) {
    exit;
}
if (defined('WAS_PLUGIN_FILE')) {
    return;
}

define('WAS_PLUGIN_FILE', __FILE__);
define('WAS_VERSION', '22.4.1');

require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/frontend.php';
require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/includes/site-content.php';
require_once __DIR__ . '/includes/site-seo.php';
require_once __DIR__ . '/includes/site-agents.php';
require_once __DIR__ . '/includes/updater.php';

if (is_admin()) {
    require_once __DIR__ . '/includes/admin.php';
    require_once __DIR__ . '/includes/site-admin.php';
}

register_activation_hook(__FILE__, 'was_install');
register_deactivation_hook(__FILE__, 'was_site_disable');
add_action('plugins_loaded', 'was_install', 5);
