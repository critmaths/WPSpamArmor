<?php
namespace CyberArmor\Admin;

use CyberArmor\Core\Config;
use CyberArmor\Database\LogRepository;
use CyberArmor\Database\StatsRepository;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Controller for CyberArmor WordPress admin menus, settings pages, and AJAX actions.
 */
class AdminController {
    public function init() {
        add_action('admin_menu', [$this, 'registerMenus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_init', [$this, 'handleSettingsSave']);

        // AJAX handlers
        add_action('wp_ajax_cyberarmor_clear_logs', [$this, 'ajaxClearLogs']);
        add_action('wp_ajax_cyberarmor_whitelist_ip', [$this, 'ajaxWhitelistIp']);
    }

    /**
     * Register admin menus.
     */
    public function registerMenus() {
        add_menu_page(
            __('CyberArmor', 'cyberarmor'),
            __('CyberArmor', 'cyberarmor'),
            'manage_options',
            'cyberarmor',
            [$this, 'renderDashboard'],
            'dashicons-shield-alt',
            80
        );

        add_submenu_page(
            'cyberarmor',
            __('CyberArmor Dashboard', 'cyberarmor'),
            __('Dashboard', 'cyberarmor'),
            'manage_options',
            'cyberarmor',
            [$this, 'renderDashboard']
        );

        add_submenu_page(
            'cyberarmor',
            __('Security Logs', 'cyberarmor'),
            __('Security Logs', 'cyberarmor'),
            'manage_options',
            'cyberarmor-logs',
            [$this, 'renderLogs']
        );

        add_submenu_page(
            'cyberarmor',
            __('CyberArmor Settings', 'cyberarmor'),
            __('Settings', 'cyberarmor'),
            'manage_options',
            'cyberarmor-settings',
            [$this, 'renderSettings']
        );
    }

    /**
     * Enqueue admin styles and scripts.
     */
    public function enqueueAssets($hook) {
        if (strpos($hook, 'cyberarmor') === false && strpos($hook, 'spamarmor') === false) {
            return;
        }

        wp_enqueue_style(
            'cyberarmor-admin-css',
            CYBERARMOR_PLUGIN_URL . 'assets/css/admin.css',
            [],
            CYBERARMOR_VERSION
        );

        wp_enqueue_script(
            'cyberarmor-admin-js',
            CYBERARMOR_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            CYBERARMOR_VERSION,
            true
        );

        wp_localize_script('cyberarmor-admin-js', 'cyberarmorAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('cyberarmor_admin_action')
        ]);
    }

    /**
     * Render the main dashboard page.
     */
    public function renderDashboard() {
        $stats = StatsRepository::getSummaryStats();
        $weeklyTrend = StatsRepository::getWeeklyTrend();
        $recentLogs = LogRepository::getRecent(8);

        include CYBERARMOR_PLUGIN_DIR . 'includes/Admin/Views/dashboard.php';
    }

    /**
     * Render the security logs inspection page.
     */
    public function renderLogs() {
        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $limit = 25;
        $offset = ($paged - 1) * $limit;
        $formFilter = isset($_GET['form_type']) ? sanitize_key(wp_unslash($_GET['form_type'])) : '';

        $totalLogs = LogRepository::getTotalCount($formFilter);
        $logs = LogRepository::getRecent($limit, $offset, $formFilter);
        $totalPages = ceil($totalLogs / $limit);

        include CYBERARMOR_PLUGIN_DIR . 'includes/Admin/Views/logs.php';
    }

    /**
     * Render the settings page.
     */
    public function renderSettings() {
        $settings = Config::getAll();
        include CYBERARMOR_PLUGIN_DIR . 'includes/Admin/Views/settings.php';
    }

    /**
     * Handle manual settings post submit.
     */
    public function handleSettingsSave() {
        if (!isset($_POST['cyberarmor_save_settings_nonce'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['cyberarmor_save_settings_nonce']));
        if (!wp_verify_nonce($nonce, 'cyberarmor_save_settings')) {
            wp_die(esc_html__('Security check failed.', 'cyberarmor'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions.', 'cyberarmor'));
        }

        $settings = isset($_POST['cyberarmor_settings']) && is_array($_POST['cyberarmor_settings'])
            ? wp_unslash($_POST['cyberarmor_settings'])
            : [];

        Config::saveAll($settings);

        wp_redirect(add_query_arg(['page' => 'cyberarmor-settings', 'updated' => 'true'], admin_url('admin.php')));
        exit;
    }

    /**
     * AJAX handler: clear logs.
     */
    public function ajaxClearLogs() {
        check_ajax_referer('cyberarmor_admin_action', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'cyberarmor'), 403);
        }

        LogRepository::clearAll();
        wp_send_json_success(__('Security logs cleared successfully.', 'cyberarmor'));
    }

    /**
     * AJAX handler: whitelist IP.
     */
    public function ajaxWhitelistIp() {
        check_ajax_referer('cyberarmor_admin_action', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'cyberarmor'), 403);
        }

        $ip = isset($_POST['ip']) ? sanitize_text_field(wp_unslash($_POST['ip'])) : '';
        if (empty($ip)) {
            wp_send_json_error(__('Invalid IP address.', 'cyberarmor'));
        }

        $currentList = Config::get('ip_whitelist', '');
        $currentList .= "\n" . $ip;
        Config::set('ip_whitelist', trim($currentList));

        wp_send_json_success(sprintf(__('IP %s added to whitelist.', 'cyberarmor'), esc_html($ip)));
    }
}
