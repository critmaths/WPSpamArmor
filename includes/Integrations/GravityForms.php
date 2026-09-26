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
 * Gravity Forms spam protection adapter.
 */
class GravityForms implements IntegrationInterface {
    public function getId() {
        return 'gravityforms';
    }

    public function getName() {
        return 'Gravity Forms';
    }

    public function isAvailable() {
        return class_exists('GFCommon');
    }

    public function isEnabled() {
        return (bool)Config::get('protect_gravityforms', true);
    }

    public function registerHooks() {
        if (!$this->isEnabled() || !$this->isAvailable()) {
            return;
        }

        // Render hidden fields inside form tag
        add_filter('gform_form_after_open', [$this, 'injectFields'], 10, 2);

        // Validation filter
        add_filter('gform_validation', [$this, 'validateSubmission']);
    }

    public function injectFields($form_string, $form) {
        return $form_string . FieldRenderer::renderHiddenFields('gravityforms');
    }

    public function validateSubmission($validation_result) {
        $form = $validation_result['form'];

        $contentParts = [];
        $unslashedPost = wp_unslash($_POST);
        foreach ($unslashedPost as $k => $v) {
            if (is_string($v) && strpos($k, 'input_') === 0) {
                $contentParts[] = sanitize_text_field($v);
            }
        }

        $engine = Plugin::instance()->getProtectionEngine();
        $context = [
            'post'       => $unslashedPost,
            'form_type'  => 'gravityforms',
            'ip'         => TokenManager::getClientIp(),
            'user_agent' => TokenManager::getUserAgent(),
            'content'    => implode("\n", $contentParts),
            'user_id'    => get_current_user_id()
        ];

        $verdict = $engine->evaluate($context);

        if ($verdict['is_spam']) {
            $reason = !empty($verdict['reasons']) ? implode(' ', $verdict['reasons']) : __('Submission flagged as spam.', 'spamarmor-open-source-spam-bot-protection');
            $validation_result['is_valid'] = false;

            // Flag first field with error message
            if (!empty($form['fields'])) {
                foreach ($form['fields'] as &$field) {
                    $field->failed_validation = true;
                    $field->validation_message = sprintf(__('SpamArmor: %s', 'spamarmor-open-source-spam-bot-protection'), esc_html($reason));
                    break;
                }
            }
            $validation_result['form'] = $form;
        }

        return $validation_result;
    }
}
