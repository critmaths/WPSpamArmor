<?php
namespace CyberArmor\Core;

use CyberArmor\Admin\AdminController;
use CyberArmor\Database\Schema;
use CyberArmor\Firewall\FirewallEngine;
use CyberArmor\Hardening\HardeningEngine;
use CyberArmor\Integrations\IntegrationManager;
use CyberArmor\Protection\ProtectionEngine;
use CyberArmor\Rest\TokenController;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main CyberArmor plugin orchestrator and service container.
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
     * @var FirewallEngine
     */
    private $firewallEngine;

    /**
     * @var HardeningEngine
     */
    private $hardeningEngine;

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
        $this->firewallEngine = new FirewallEngine();
        $this->hardeningEngine = new HardeningEngine();
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
        // 1. Boot early Web Application Firewall (WAF)
        $this->firewallEngine->boot();

        // 2. Boot Core Hardening & Security Headers
        $this->hardeningEngine->boot();

        // 3. Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        add_action('login_enqueue_scripts', [$this, 'enqueueFrontendAssets']);

        // 4. Register REST API routes
        add_action('rest_api_init', [$this->tokenController, 'registerRoutes']);

        // 5. Boot admin if in wp-admin
        if ($this->adminController !== null) {
            $this->adminController->init();
        }

        // 6. Boot all enabled form adapters
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
            'cyberarmor-front',
            CYBERARMOR_PLUGIN_URL . 'assets/css/cyberarmor-front.css',
            [],
            CYBERARMOR_VERSION
        );

        wp_enqueue_script(
            'cyberarmor-front',
            CYBERARMOR_PLUGIN_URL . 'assets/js/cyberarmor-front.js',
            [],
            CYBERARMOR_VERSION,
            true // Load in footer
        );

        wp_localize_script('cyberarmor-front', 'cyberarmorSettings', [
            'restUrl'    => esc_url_raw(rest_url('cyberarmor/v1/token')),
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
     * Get the FirewallEngine instance.
     *
     * @return FirewallEngine
     */
    public function getFirewallEngine() {
        return $this->firewallEngine;
    }

    /**
     * Get the HardeningEngine instance.
     *
     * @return HardeningEngine
     */
    public function getHardeningEngine() {
        return $this->hardeningEngine;
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
