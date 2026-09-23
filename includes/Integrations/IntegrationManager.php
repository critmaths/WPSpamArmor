<?php
namespace SpamArmor\Integrations;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages loading and lifecycle of all integration adapters.
 */
class IntegrationManager {
    /**
     * @var IntegrationInterface[]
     */
    private $integrations = [];

    public function __construct() {
        $this->registerDefaultIntegrations();
    }

    /**
     * Register core and 3rd party plugin adapters.
     */
    private function registerDefaultIntegrations() {
        $this->add(new WordPressComments());
        $this->add(new WordPressRegistration());
        $this->add(new WordPressLogin());
        $this->add(new ContactForm7());
        $this->add(new WPForms());
        $this->add(new GravityForms());
        $this->add(new FluentForms());
        $this->add(new WooCommerce());
    }

    /**
     * Add an integration adapter.
     *
     * @param IntegrationInterface $integration
     */
    public function add(IntegrationInterface $integration) {
        $this->integrations[$integration->getId()] = $integration;
    }

    /**
     * Get all registered integrations.
     *
     * @return IntegrationInterface[]
     */
    public function getAll() {
        return $this->integrations;
    }

    /**
     * Boot all available and enabled integrations.
     */
    public function boot() {
        foreach ($this->integrations as $integration) {
            if ($integration->isAvailable() && $integration->isEnabled()) {
                $integration->registerHooks();
            }
        }
    }
}
