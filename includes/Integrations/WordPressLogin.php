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
 * Native WordPress Login form bot & brute-force defense adapter.
 */
class WordPressLogin implements IntegrationInterface {
    public function getId() {
        return 'login';
    }

    public function getName() {
        return 'WordPress Login Form';
    }

    public function isAvailable() {
        return true;
    }

    public function isEnabled() {
        return (bool)Config::get('protect_login', true);
    }

    public function registerHooks() {
        if (!$this->isEnabled()) {
            return;
        }

        add_action('login_form', [$this, 'renderFields']);
        add_filter('authenticate', [$this, 'validateLogin'], 21, 3);
    }

    public function renderFields() {
        echo FieldRenderer::renderHiddenFields('login');
    }

    /**
     * Inspect login attempt before checking credentials.
     *
     * @param null|\WP_User|\WP_Error $user
     * @param string $username
     * @param string $password
     * @return null|\WP_User|\WP_Error
     */
    public function validateLogin($user, $username, $password) {
        // Skip if already an error or during XML-RPC / REST requests without POST
        if (is_wp_error($user) || empty($_POST['log'])) {
            return $user;
        }

        $engine = Plugin::instance()->getProtectionEngine();
        $context = [
            'post'       => wp_unslash($_POST),
            'form_type'  => 'login',
            'ip'         => TokenManager::getClientIp(),
            'user_agent' => TokenManager::getUserAgent(),
            'content'    => sanitize_user($username),
            'user_id'    => null
        ];

        $verdict = $engine->evaluate($context);

        if ($verdict['is_spam']) {
            $reason = !empty($verdict['reasons']) ? implode(' ', $verdict['reasons']) : __('Suspicious automated login request.', 'spamarmor-open-source-spam-bot-protection');
            return new \WP_Error(
                'spamarmor_login_blocked',
                sprintf('<strong>%s:</strong> %s', esc_html__('Access Denied', 'spamarmor-open-source-spam-bot-protection'), esc_html($reason))
            );
        }

        return $user;
    }
}
