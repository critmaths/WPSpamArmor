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
 * Native WordPress Comments spam protection adapter.
 */
class WordPressComments implements IntegrationInterface {
    public function getId() {
        return 'comments';
    }

    public function getName() {
        return 'WordPress Native Comments';
    }

    public function isAvailable() {
        return true; // Core feature
    }

    public function isEnabled() {
        return (bool)Config::get('protect_comments', true);
    }

    public function registerHooks() {
        if (!$this->isEnabled()) {
            return;
        }

        // Render security fields in comment form
        add_action('comment_form_after_fields', [$this, 'renderFields']);
        add_action('comment_form_logged_in_after_fields', [$this, 'renderFields']);

        // Filter and inspect incoming comment submission
        add_filter('preprocess_comment', [$this, 'filterComment']);
    }

    /**
     * Echo security fields in comment form.
     */
    public function renderFields() {
        echo FieldRenderer::renderHiddenFields('comment');
    }

    /**
     * Intercept and evaluate comment submission.
     *
     * @param array $commentdata
     * @return array
     */
    public function filterComment($commentdata) {
        // Skip trackbacks/pingbacks if needed or check them
        if (isset($commentdata['comment_type']) && in_array($commentdata['comment_type'], ['trackback', 'pingback'], true)) {
            return $commentdata;
        }

        $engine = Plugin::instance()->getProtectionEngine();
        $context = [
            'post'       => wp_unslash($_POST),
            'form_type'  => 'comment',
            'ip'         => TokenManager::getClientIp(),
            'user_agent' => TokenManager::getUserAgent(),
            'content'    => sanitize_textarea_field($commentdata['comment_content'] ?? ''),
            'user_id'    => get_current_user_id()
        ];

        $verdict = $engine->evaluate($context);

        if ($verdict['is_spam']) {
            $action = $verdict['action'];
            $reason = !empty($verdict['reasons']) ? implode(' ', $verdict['reasons']) : __('Suspicious submission pattern.', 'spamarmor-open-source-spam-bot-protection');

            if ($action === 'block') {
                wp_die(
                    sprintf(
                        '<h1>%s</h1><p>%s</p><p><a href="javascript:history.back()">&laquo; %s</a></p>',
                        esc_html__('Submission Blocked by SpamArmor', 'spamarmor-open-source-spam-bot-protection'),
                        esc_html(sprintf(__('Your comment was flagged by local spam defense (%s). If this was a mistake, please go back and try again.', 'spamarmor-open-source-spam-bot-protection'), $reason)),
                        esc_html__('Back', 'spamarmor-open-source-spam-bot-protection')
                    ),
                    esc_html__('Spam Blocked', 'spamarmor-open-source-spam-bot-protection'),
                    ['response' => 403, 'back_link' => true]
                );
            }

            if ($action === 'trash') {
                add_filter('pre_comment_approved', function() {
                    return 'trash';
                });
            } else {
                // Default 'spam' queue
                add_filter('pre_comment_approved', function() {
                    return 'spam';
                });
            }
        }

        return $commentdata;
    }
}
