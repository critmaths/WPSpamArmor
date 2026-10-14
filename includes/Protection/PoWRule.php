<?php
namespace CyberArmor\Protection;

use CyberArmor\Core\Config;
use CyberArmor\Core\TokenManager;

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
        $token = $post['cyberarmor_pow_token'] ?? ($post['spamarmor_pow_token'] ?? ($post['spm_pow_token'] ?? ''));
        $nonce = $post['cyberarmor_pow_nonce'] ?? ($post['spamarmor_pow_nonce'] ?? ($post['spm_pow_nonce'] ?? ''));

        if (empty($token) || empty($nonce)) {
            return [
                'passed'   => false,
                'score'    => 80,
                'critical' => false,
                'reason'   => __('Missing JavaScript Proof-of-Work verification (headless bot or JS disabled).', 'cyberarmor')
            ];
        }

        $isValid = TokenManager::verifyPoWSolution($token, $nonce);
        if (!$isValid) {
            return [
                'passed'   => false,
                'score'    => 95,
                'critical' => true,
                'reason'   => __('Invalid or forged Proof-of-Work solution.', 'cyberarmor')
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
