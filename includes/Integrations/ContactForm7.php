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
 * Contact Form 7 spam protection adapter.
 */
class ContactForm7 implements IntegrationInterface {
    public function getId() {
        return 'cf7';
    }

    public function getName() {
        return 'Contact Form 7';
    }

    public function isAvailable() {
        return defined('WPCF7_VERSION');
    }

    public function isEnabled() {
        return (bool)Config::get('protect_cf7', true);
    }

    public function registerHooks() {
        if (!$this->isEnabled() || !$this->isAvailable()) {
            return;
        }

        // Append hidden security fields to CF7 form output
        add_filter('wpcf7_form_elements', [$this, 'injectFields']);

        // Check submission via native CF7 spam filter
        add_filter('wpcf7_spam', [$this, 'checkSubmission']);
    }

    public function injectFields($elements) {
        return $elements . FieldRenderer::renderHiddenFields('cf7');
    }

    public function checkSubmission($spam) {
        if ($spam) {
            return $spam;
        }

        // Extract submitted text fields from CF7 submission instance
        $submission = \WPCF7_Submission::get_instance();
        $submittedData = $submission ? $submission->get_posted_data() : $_POST;

        $contentParts = [];
        foreach ($submittedData as $key => $val) {
            if (is_string($val) && strpos($key, 'spamarmor_') === false && strpos($key, '_wpcf7') === false) {
                $contentParts[] = sanitize_text_field($val);
            }
        }

        $engine = Plugin::instance()->getProtectionEngine();
        $context = [
            'post'       => wp_unslash($_POST),
            'form_type'  => 'cf7',
            'ip'         => TokenManager::getClientIp(),
            'user_agent' => TokenManager::getUserAgent(),
            'content'    => implode("\n", $contentParts),
            'user_id'    => get_current_user_id()
        ];

        $verdict = $engine->evaluate($context);

        if ($verdict['is_spam']) {
            return true;
        }

        return $spam;
    }
}
