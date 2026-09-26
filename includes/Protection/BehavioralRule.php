<?php
namespace SpamArmor\Protection;

use SpamArmor\Core\Config;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Behavioral interaction entropy rule.
 * Inspects client-side human presence markers: mouse gestures, keystrokes, touch, scroll.
 * Zero keystrokes / mouse movements on large forms indicates automated DOM injection.
 */
class BehavioralRule implements RuleInterface {
    public function getId() {
        return 'behavioral';
    }

    public function getName() {
        return 'Behavioral Interaction Check';
    }

    public function isEnabled() {
        return (bool)Config::get('enable_behavioral', true);
    }

    public function check(array $context) {
        $post = $context['post'] ?? [];
        $behaviorRaw = $post['spamarmor_behavior'] ?? ($post['spm_behavior'] ?? '');

        if (empty($behaviorRaw)) {
            // Behavioral data missing: suspicious but not critical on its own
            return [
                'passed'   => false,
                'score'    => 35,
                'critical' => false,
                'reason'   => __('No human behavioral entropy received from browser.', 'spamarmor-open-source-spam-bot-protection')
            ];
        }

        $decoded = json_decode(base64_decode($behaviorRaw), true);
        if (!is_array($decoded)) {
            return [
                'passed'   => false,
                'score'    => 50,
                'critical' => false,
                'reason'   => __('Corrupted or malformed behavioral telemetry payload.', 'spamarmor-open-source-spam-bot-protection')
            ];
        }

        $mouseMoves = intval($decoded['m'] ?? 0);
        $keyPresses = intval($decoded['k'] ?? 0);
        $touches    = intval($decoded['t'] ?? 0);
        $focusCount = intval($decoded['f'] ?? 0);
        $hasPasted  = !empty($decoded['p']);

        $totalInteractions = $mouseMoves + $keyPresses + $touches + $focusCount;

        // If form has non-trivial text content but zero keystrokes and zero touch/mouse interactions:
        $content = trim((string)($context['content'] ?? ''));
        if (strlen($content) > 30 && $totalInteractions < 2 && !$hasPasted) {
            return [
                'passed'   => false,
                'score'    => 65,
                'critical' => false,
                'reason'   => sprintf(
                    __('Abnormal interaction entropy (%d actions recorded for %d chars of content).', 'spamarmor-open-source-spam-bot-protection'),
                    $totalInteractions,
                    strlen($content)
                )
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
