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
 * WPForms spam protection adapter.
 */
class WPForms implements IntegrationInterface {
    public function getId() {
        return 'wpforms';
    }

    public function getName() {
        return 'WPForms';
    }

    public function isAvailable() {
        return defined('WPFORMS_VERSION') || function_exists('wpforms');
    }

    public function isEnabled() {
        return (bool)Config::get('protect_wpforms', true);
    }

    public function registerHooks() {
        if (!$this->isEnabled() || !$this->isAvailable()) {
            return;
        }

        // Render hidden fields inside form
        add_action('wpforms_display_fields_after', [$this, 'renderFields'], 10, 2);

        // Intercept validation during submission processing
        add_action('wpforms_process', [$this, 'validateSubmission'], 10, 3);
    }

    public function renderFields($fields, $form_data) {
        echo FieldRenderer::renderHiddenFields('wpforms');
    }

    public function validateSubmission($fields, $entry, $form_data) {
        $contentParts = [];
        if (!empty($fields) && is_array($fields)) {
            foreach ($fields as $field) {
                if (!empty($field['value']) && is_string($field['value'])) {
                    $contentParts[] = $field['value'];
                }
            }
        }

        $engine = Plugin::instance()->getProtectionEngine();
        $context = [
            'post'       => $_POST,
            'form_type'  => 'wpforms',
            'ip'         => TokenManager::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'content'    => implode("\n", $contentParts),
            'user_id'    => get_current_user_id()
        ];

        $verdict = $engine->evaluate($context);

        if ($verdict['is_spam']) {
            $formId = absint($form_data['id'] ?? 0);
            $reason = !empty($verdict['reasons']) ? implode(' ', $verdict['reasons']) : __('Spam activity detected.', 'spamarmor');

            if (function_exists('wpforms') && isset(wpforms()->process)) {
                wpforms()->process->errors[$formId]['header'] = sprintf(
                    __('Spam protection alert: %s', 'spamarmor'),
                    esc_html($reason)
                );
            }
        }
    }
}
