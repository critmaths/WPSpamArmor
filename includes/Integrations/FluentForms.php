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
                if (is_string($v) && strpos($k, 'spm_') === false) {
                    $contentParts[] = $v;
                }
            }
        }

        $engine = Plugin::instance()->getProtectionEngine();
        $context = [
            'post'       => $_POST,
            'form_type'  => 'fluentforms',
            'ip'         => TokenManager::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'content'    => implode("\n", $contentParts),
            'user_id'    => get_current_user_id()
        ];

        $verdict = $engine->evaluate($context);

        if ($verdict['is_spam']) {
            $reason = !empty($verdict['reasons']) ? implode(' ', $verdict['reasons']) : __('Spam submission prevented.', 'spamarmor');

            wp_send_json_error([
                'errors' => [
                    'spamarmor' => [sprintf(__('SpamArmor: %s', 'spamarmor'), esc_html($reason))]
                ]
            ], 422);
        }
    }
}
