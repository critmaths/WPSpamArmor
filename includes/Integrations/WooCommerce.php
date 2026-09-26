<?php
namespace SpamArmor\Integrations;

use SpamArmor\Core\Config;
use SpamArmor\Core\Plugin;
use SpamArmor\Core\TokenManager;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce registration & review protection adapter.
 */
class WooCommerce implements IntegrationInterface {
    public function getId() {
        return 'woocommerce';
    }

    public function getName() {
        return 'WooCommerce';
    }

    public function isAvailable() {
        return class_exists('WooCommerce');
    }

    public function isEnabled() {
        return (bool)Config::get('protect_woocommerce', true);
    }

    public function registerHooks() {
        if (!$this->isEnabled() || !$this->isAvailable()) {
            return;
        }

        // Render hidden fields on WooCommerce registration forms (My Account & Checkout)
        add_action('woocommerce_register_form', [$this, 'renderFields']);
        add_action('woocommerce_checkout_after_customer_details', [$this, 'renderFields']);

        // Inspect registration process
        add_filter('woocommerce_process_registration_errors', [$this, 'validateRegistration'], 10, 4);
    }

    public function renderFields() {
        echo FieldRenderer::renderHiddenFields('woocommerce');
    }

    /**
     * Inspect WooCommerce customer registration.
     *
     * @param \WP_Error $errors
     * @param string $username
     * @param string $password
     * @param string $email
     * @return \WP_Error
     */
    public function validateRegistration($errors, $username, $password, $email) {
        $engine = Plugin::instance()->getProtectionEngine();
        $context = [
            'post'       => wp_unslash($_POST),
            'form_type'  => 'woocommerce',
            'ip'         => TokenManager::getClientIp(),
            'user_agent' => TokenManager::getUserAgent(),
            'content'    => sanitize_text_field($username) . ' ' . sanitize_email($email),
            'user_id'    => null
        ];

        $verdict = $engine->evaluate($context);

        if ($verdict['is_spam']) {
            $reason = !empty($verdict['reasons']) ? implode(' ', $verdict['reasons']) : __('Spam activity detected.', 'spamarmor-open-source-spam-bot-protection');
            $errors->add('spamarmor_blocked', sprintf(__('SpamArmor: %s', 'spamarmor-open-source-spam-bot-protection'), esc_html($reason)));
        }

        return $errors;
    }
}
