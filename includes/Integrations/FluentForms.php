<?php
namespace CyberArmor\Integrations;

use CyberArmor\Core\Config;
use CyberArmor\Core\Plugin;
use CyberArmor\Core\TokenManager;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fluent Forms spam protection adapter.
 */
class FluentForms implements IntegrationInterface {
    public function getId() {
        return 'fluentforms';
    }

    public function getName() {
        return 'Fluent Forms';
    }

    public function isAvailable() {
        return defined('FLUENTFORM') || function_exists('wpFluent');
    }

    public function isEnabled() {
        return (bool)Config::get('protect_fluentforms', true);
    }

    public function registerHooks() {
        if (!$this->isEnabled() || !$this->isAvailable()) {
            return;
        }

        // Render hidden fields inside form
        add_action('fluentform/form_element_start', [$this, 'renderFields'], 10, 1);

        // Validate submission before saving
        add_action('fluentform/before_submission_validation', [$this, 'validateSubmission'], 10, 3);
    }

    public function renderFields($form) {
        echo FieldRenderer::renderHiddenFields('fluentforms');
    }

    public function validateSubmission($insertData, $formData, $form) {
        $contentParts = [];
        if (is_array($formData)) {
            foreach ($formData as $k => $v) {
                if (is_string($v) && strpos($k, 'spamarmor_') === false) {
                    $contentParts[] = sanitize_text_field($v);
                }
            }
        }

        $engine = Plugin::instance()->getProtectionEngine();
        $context = [
            'post'       => wp_unslash($_POST),
            'form_type'  => 'fluentforms',
            'ip'         => TokenManager::getClientIp(),
            'user_agent' => TokenManager::getUserAgent(),
            'content'    => implode("\n", $contentParts),
            'user_id'    => get_current_user_id()
        ];

        $verdict = $engine->evaluate($context);

        if ($verdict['is_spam']) {
            $reason = !empty($verdict['reasons']) ? implode(' ', $verdict['reasons']) : __('Spam submission prevented.', 'cyberarmor');

            wp_send_json_error([
                'errors' => [
                    'cyberarmor' => [sprintf(__('SpamArmor: %s', 'cyberarmor'), esc_html($reason))]
                ]
            ], 422);
        }
    }
}
