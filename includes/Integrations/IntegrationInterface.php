<?php
namespace SpamArmor\Integrations;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface for all form and platform integration adapters.
 */
interface IntegrationInterface {
    /**
     * Unique identifier for the integration.
     *
     * @return string
     */
    public function getId();

    /**
     * Human-readable name.
     *
     * @return string
     */
    public function getName();

    /**
     * Check if the target plugin or core feature is currently active.
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * Check if the integration is enabled in SpamArmor settings.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Attach all relevant hooks, filters, and asset injections.
     */
    public function registerHooks();
}
