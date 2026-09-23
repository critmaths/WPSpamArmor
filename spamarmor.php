<?php
/**
 * Plugin Name:       SpamArmor - Open Source Spam & Bot Protection
 * Plugin URI:        https://github.com/spamarmor/spamarmor
 * Description:       Next-generation, privacy-first open-source spam and bot protection for WordPress. Zero subscriptions, zero external cloud dependencies.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            SpamArmor Community
 * Author URI:        https://github.com/spamarmor
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       spamarmor
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants.
define('SPAMARMOR_VERSION', '1.0.0');
define('SPAMARMOR_PLUGIN_FILE', __FILE__);
define('SPAMARMOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SPAMARMOR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SPAMARMOR_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Require the autoloader.
require_once SPAMARMOR_PLUGIN_DIR . 'includes/Core/Autoloader.php';

// Register autoloader.
\SpamArmor\Core\Autoloader::register();

/**
 * Plugin activation hook.
 */
function spamarmor_activate() {
    \SpamArmor\Core\Plugin::instance()->activate();
}
register_activation_hook(__FILE__, 'spamarmor_activate');

/**
 * Plugin deactivation hook.
 */
function spamarmor_deactivate() {
    \SpamArmor\Core\Plugin::instance()->deactivate();
}
register_deactivation_hook(__FILE__, 'spamarmor_deactivate');

/**
 * Boot the plugin instance on plugins_loaded.
 */
function spamarmor_init() {
    \SpamArmor\Core\Plugin::instance()->boot();
}
add_action('plugins_loaded', 'spamarmor_init');
