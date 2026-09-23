<?php
namespace SpamArmor\Core;

use SpamArmor\Admin\AdminController;
use SpamArmor\Database\Schema;
use SpamArmor\Integrations\IntegrationManager;
use SpamArmor\Protection\ProtectionEngine;
use SpamArmor\Rest\TokenController;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin orchestrator and service container.
 */
class Plugin {
    /**
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * @var ProtectionEngine
     */
    private $protectionEngine;

    /**
     * @var IntegrationManager
     */
    private $integrationManager;

    /**
     * @var AdminController|null
     */
    private $adminController;

    /**
     * @var TokenController
     */
    private $tokenController;

    /**
     * Get singleton instance.
     *
     * @return Plugin
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->protectionEngine = new ProtectionEngine();
        $this->integrationManager = new IntegrationManager();
        $this->tokenController = new TokenController();

        if (is_admin()) {
            $this->adminController = new AdminController();
        }
    }

    /**
     * Plugin activation handler.
     */
    public function activate() {
        Schema::createTables();
    }

    /**
     * Plugin deactivation handler.
     */
    public function deactivate() {
        // Clear transients or temporary caches if needed
    }

    /**
     * Boot all plugin services, hooks, and assets.
     */
    public function boot() {
        // Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        add_action('login_enqueue_scripts', [$this, 'enqueueFrontendAssets']);

        // Register REST API routes
        add_action('rest_api_init', [$this->tokenController, 'registerRoutes']);

        // Boot admin if in wp-admin
        if ($this->adminController !== null) {
            $this->adminController->init();
            add_action('admin_init', function() {
                if (get_option('spamarmor_db_version') !== SPAMARMOR_VERSION) {
                    Schema::createTables();
                }
            });
        }

        // Boot all enabled form adapters
        $this->integrationManager->boot();
    }

    /**
     * Enqueue frontend protection styles and scripts.
     */
    public function enqueueFrontendAssets() {
        if (!Config::get('enabled', true)) {
            return;
        }

        wp_enqueue_style(
            'spamarmor-front',
            SPAMARMOR_PLUGIN_URL . 'assets/css/spamarmor-front.css',
            [],
            SPAMARMOR_VERSION
        );

        wp_enqueue_script(
            'spamarmor-front',
            SPAMARMOR_PLUGIN_URL . 'assets/js/spamarmor-front.js',
            [],
            SPAMARMOR_VERSION,
            true // Load in footer
        );

        wp_localize_script('spamarmor-front', 'spamarmorSettings', [
            'restUrl'    => esc_url_raw(rest_url('spamarmor/v1/token')),
            'difficulty' => (int)Config::get('pow_difficulty', 3)
        ]);
    }

    /**
     * Get the ProtectionEngine instance.
     *
     * @return ProtectionEngine
     */
    public function getProtectionEngine() {
        return $this->protectionEngine;
    }

    /**
     * Get the IntegrationManager instance.
     *
     * @return IntegrationManager
     */
    public function getIntegrationManager() {
        return $this->integrationManager;
    }
}
