<?php
/**
 * Plugin Name:       CyberArmor - Open Source Security, Firewall & Anti-Spam
 * Plugin URI:        https://github.com/critmaths/cyberarmor/
 * Description:       Enterprise-grade, privacy-first open-source security, Layer-7 Web Application Firewall (WAF), core hardening, and bot & spam defense for WordPress.
 * Version:           1.1.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            CyberArmor Community
 * Author URI:        https://criticalmaths.synergize.co
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cyberarmor
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants.
define('CYBERARMOR_VERSION', '1.1.0');
define('CYBERARMOR_PLUGIN_FILE', __FILE__);
define('CYBERARMOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CYBERARMOR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CYBERARMOR_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Backward compatibility constants
define('SPAMARMOR_VERSION', CYBERARMOR_VERSION);
define('SPAMARMOR_PLUGIN_FILE', CYBERARMOR_PLUGIN_FILE);
define('SPAMARMOR_PLUGIN_DIR', CYBERARMOR_PLUGIN_DIR);
define('SPAMARMOR_PLUGIN_URL', CYBERARMOR_PLUGIN_URL);

// Require the autoloader.
require_once CYBERARMOR_PLUGIN_DIR . 'includes/Core/Autoloader.php';

// Register autoloader.
\CyberArmor\Core\Autoloader::register();

/**
 * Plugin activation hook.
 */
function cyberarmor_activate() {
    \CyberArmor\Core\Plugin::instance()->activate();
}
register_activation_hook(__FILE__, 'cyberarmor_activate');

/**
 * Plugin deactivation hook.
 */
function cyberarmor_deactivate() {
    \CyberArmor\Core\Plugin::instance()->deactivate();
}
register_deactivation_hook(__FILE__, 'cyberarmor_deactivate');

/**
 * Boot the plugin instance on plugins_loaded.
 */
function cyberarmor_init() {
    \CyberArmor\Core\Plugin::instance()->boot();
}
add_action('plugins_loaded', 'cyberarmor_init');

