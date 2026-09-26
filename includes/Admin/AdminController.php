<?php
namespace SpamArmor\Admin;

use SpamArmor\Core\Config;
use SpamArmor\Database\LogRepository;
use SpamArmor\Database\StatsRepository;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Controller for WordPress admin menus, settings pages, and AJAX actions.
 */
class AdminController {
    public function init() {
        add_action('admin_menu', [$this, 'registerMenus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_init', [$this, 'handleSettingsSave']);

        // AJAX handlers
        add_action('wp_ajax_spamarmor_clear_logs', [$this, 'ajaxClearLogs']);
        add_action('wp_ajax_spamarmor_whitelist_ip', [$this, 'ajaxWhitelistIp']);
    }

    /**
     * Register admin menus.
     */
    public function registerMenus() {
        add_menu_page(
            __('SpamArmor', 'spamarmor-open-source-spam-bot-protection'),
            __('SpamArmor', 'spamarmor-open-source-spam-bot-protection'),
            'manage_options',
            'spamarmor',
            [$this, 'renderDashboard'],
            'dashicons-shield-alt',
            80
        );

        add_submenu_page(
            'spamarmor',
            __('SpamArmor Dashboard', 'spamarmor-open-source-spam-bot-protection'),
            __('Dashboard', 'spamarmor-open-source-spam-bot-protection'),
            'manage_options',
            'spamarmor',
            [$this, 'renderDashboard']
        );

        add_submenu_page(
            'spamarmor',
            __('Spam Logs', 'spamarmor-open-source-spam-bot-protection'),
            __('Spam Logs', 'spamarmor-open-source-spam-bot-protection'),
            'manage_options',
            'spamarmor-logs',
            [$this, 'renderLogs']
        );

        add_submenu_page(
            'spamarmor',
            __('SpamArmor Settings', 'spamarmor-open-source-spam-bot-protection'),
            __('Settings', 'spamarmor-open-source-spam-bot-protection'),
            'manage_options',
            'spamarmor-settings',
            [$this, 'renderSettings']
        );
    }

    /**
     * Enqueue admin styles and scripts.
     */
    public function enqueueAssets($hook) {
        if (strpos($hook, 'spamarmor') === false) {
            return;
        }

        wp_enqueue_style(
            'spamarmor-admin-css',
            SPAMARMOR_PLUGIN_URL . 'assets/css/admin.css',
            [],
            SPAMARMOR_VERSION
        );

        wp_enqueue_script(
            'spamarmor-admin-js',
            SPAMARMOR_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            SPAMARMOR_VERSION,
            true
        );

        wp_localize_script('spamarmor-admin-js', 'spamarmorAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('spamarmor_admin_action')
        ]);
    }

    /**
     * Render the main dashboard page.
     */
    public function renderDashboard() {
        $stats = StatsRepository::getSummaryStats();
        $weeklyTrend = StatsRepository::getWeeklyTrend();
        $recentLogs = LogRepository::getRecent(8);

        include SPAMARMOR_PLUGIN_DIR . 'includes/Admin/Views/dashboard.php';
    }

    /**
     * Render the spam logs inspection page.
     */
    public function renderLogs() {
        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $limit = 25;
        $offset = ($paged - 1) * $limit;
        $formFilter = isset($_GET['form_type']) ? sanitize_key(wp_unslash($_GET['form_type'])) : '';

        $totalLogs = LogRepository::getTotalCount($formFilter);
        $logs = LogRepository::getRecent($limit, $offset, $formFilter);
        $totalPages = ceil($totalLogs / $limit);

        include SPAMARMOR_PLUGIN_DIR . 'includes/Admin/Views/logs.php';
    }

    /**
     * Render the settings page.
     */
    public function renderSettings() {
        $settings = Config::getAll();
        include SPAMARMOR_PLUGIN_DIR . 'includes/Admin/Views/settings.php';
    }

    /**
     * Handle manual settings post submit.
     */
    public function handleSettingsSave() {
        if (!isset($_POST['spamarmor_save_settings_nonce'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['spamarmor_save_settings_nonce']));
        if (!wp_verify_nonce($nonce, 'spamarmor_save_settings')) {
            wp_die(esc_html__('Security check failed.', 'spamarmor-open-source-spam-bot-protection'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions.', 'spamarmor-open-source-spam-bot-protection'));
        }

        $settings = isset($_POST['spamarmor_settings']) && is_array($_POST['spamarmor_settings'])
            ? wp_unslash($_POST['spamarmor_settings'])
            : [];

        Config::saveAll($settings);

        wp_redirect(add_query_arg(['page' => 'spamarmor-settings', 'updated' => 'true'], admin_url('admin.php')));
        exit;
    }

    /**
     * AJAX handler: clear logs.
     */
    public function ajaxClearLogs() {
        check_ajax_referer('spamarmor_admin_action', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'spamarmor-open-source-spam-bot-protection'), 403);
        }

        LogRepository::clearAll();
        wp_send_json_success(__('Logs cleared successfully.', 'spamarmor-open-source-spam-bot-protection'));
    }

    /**
     * AJAX handler: whitelist IP.
     */
    public function ajaxWhitelistIp() {
        check_ajax_referer('spamarmor_admin_action', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'spamarmor-open-source-spam-bot-protection'), 403);
        }

        $ip = isset($_POST['ip']) ? sanitize_text_field(wp_unslash($_POST['ip'])) : '';
        if (empty($ip)) {
            wp_send_json_error(__('Invalid IP address.', 'spamarmor-open-source-spam-bot-protection'));
        }

        $currentList = Config::get('ip_whitelist', '');
        $currentList .= "\n" . $ip;
        Config::set('ip_whitelist', trim($currentList));

        wp_send_json_success(sprintf(__('IP %s added to whitelist.', 'spamarmor-open-source-spam-bot-protection'), esc_html($ip)));
    }
}
