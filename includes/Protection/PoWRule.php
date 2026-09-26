<?php
namespace SpamArmor\Protection;

use SpamArmor\Core\Config;
use SpamArmor\Core\TokenManager;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Micro Proof-of-Work (PoW) validation rule.
 * Forces client to solve a lightweight cryptographic micro-puzzle in JavaScript.
 * Takes human browsers 5-15ms unnoticeably, but creates exponential cost for spam botnets.
 */
class PoWRule implements RuleInterface {
    public function getId() {
        return 'pow';
    }

    public function getName() {
        return 'Micro Proof-of-Work';
    }

    public function isEnabled() {
        return (bool)Config::get('enable_pow', true);
    }

    public function check(array $context) {
        $post = $context['post'] ?? [];
        $token = $post['spamarmor_pow_token'] ?? ($post['spm_pow_token'] ?? '');
        $nonce = $post['spamarmor_pow_nonce'] ?? ($post['spm_pow_nonce'] ?? '');

        if (empty($token) || empty($nonce)) {
            return [
                'passed'   => false,
                'score'    => 80,
                'critical' => false,
                'reason'   => __('Missing JavaScript Proof-of-Work verification (headless bot or JS disabled).', 'spamarmor-open-source-spam-bot-protection')
            ];
        }

        $isValid = TokenManager::verifyPoWSolution($token, $nonce);
        if (!$isValid) {
            return [
                'passed'   => false,
                'score'    => 95,
                'critical' => true,
                'reason'   => __('Invalid or forged Proof-of-Work solution.', 'spamarmor-open-source-spam-bot-protection')
            ];
        }

        return [
            'passed'   => true,
            'score'    => 0,
            'critical' => false,
            'reason'   => ''
        ];
    }
}
