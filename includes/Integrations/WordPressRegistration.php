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
 * Native WordPress User Registration bot and spam protection adapter.
 */
class WordPressRegistration implements IntegrationInterface {
    public function getId() {
        return 'registration';
    }

    public function getName() {
        return 'WordPress User Registration';
    }

    public function isAvailable() {
        return true;
    }

    public function isEnabled() {
        return (bool)Config::get('protect_registrations', true);
    }

    public function registerHooks() {
        if (!$this->isEnabled()) {
            return;
        }

        add_action('register_form', [$this, 'renderFields']);
        add_filter('registration_errors', [$this, 'validateRegistration'], 10, 3);
    }

    public function renderFields() {
        echo FieldRenderer::renderHiddenFields('registration');
    }

    /**
     * Inspect registration submission.
     *
     * @param \WP_Error $errors
     * @param string $sanitized_user_login
     * @param string $user_email
     * @return \WP_Error
     */
    public function validateRegistration($errors, $sanitized_user_login, $user_email) {
        $engine = Plugin::instance()->getProtectionEngine();
        $context = [
            'post'       => $_POST,
            'form_type'  => 'registration',
            'ip'         => TokenManager::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'content'    => $sanitized_user_login . ' ' . $user_email,
            'user_id'    => null
        ];

        $verdict = $engine->evaluate($context);

        if ($verdict['is_spam']) {
            $reason = !empty($verdict['reasons']) ? implode(' ', $verdict['reasons']) : __('Automated bot activity detected.', 'spamarmor');
            $errors->add(
                'spamarmor_blocked',
                sprintf('<strong>%s:</strong> %s', esc_html__('Registration Error', 'spamarmor'), esc_html($reason))
            );
        }

        return $errors;
    }
}
