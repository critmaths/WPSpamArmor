<?php
namespace SpamArmor\Protection;

use SpamArmor\Core\Config;
use SpamArmor\Core\TokenManager;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Honeypot detection rule.
 * Deploys hidden decoy fields that human users never see or fill,
 * but automated crawler bots automatically fill out.
 */
class HoneypotRule implements RuleInterface {
    public function getId() {
        return 'honeypot';
    }

    public function getName() {
        return 'Dynamic Honeypot';
    }

    public function isEnabled() {
        return (bool)Config::get('enable_honeypot', true);
    }

    public function check(array $context) {
        $post = $context['post'] ?? [];
        $honeypotKey = TokenManager::getHoneypotFieldName();

        // 1. Primary dynamic honeypot check
        if (isset($post[$honeypotKey]) && trim((string)$post[$honeypotKey]) !== '') {
            return [
                'passed'   => false,
                'score'    => 100,
                'critical' => true,
                'reason'   => sprintf(__('Honeypot field "%s" was filled with: "%s"', 'spamarmor-open-source-spam-bot-protection'), $honeypotKey, substr(sanitize_text_field($post[$honeypotKey]), 0, 30))
            ];
        }

        // 2. Generic decoy fields (e.g. deceptive fake URL or secondary honeypot)
        $decoys = ['spamarmor_decoy_website', 'spamarmor_hp_check', 'spm_decoy_website', 'spm_hp_check'];
        foreach ($decoys as $decoy) {
            if (isset($post[$decoy]) && trim((string)$post[$decoy]) !== '') {
                return [
                    'passed'   => false,
                    'score'    => 100,
                    'critical' => true,
                    'reason'   => sprintf(__('Secondary honeypot decoy "%s" was filled.', 'spamarmor-open-source-spam-bot-protection'), $decoy)
                ];
            }
        }

        return [
            'passed'   => true,
            'score'    => 0,
            'critical' => false,
            'reason'   => ''
        ];
    }
}
