<?php
namespace SpamArmor\Protection;

use SpamArmor\Core\Config;
use SpamArmor\Core\TokenManager;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Time-Gate detection rule.
 * Humans require several seconds to read, formulate, and type a response.
 * Automated spam bots submit forms in under 1 second.
 */
class TimeGateRule implements RuleInterface {
    public function getId() {
        return 'timegate';
    }

    public function getName() {
        return 'Time-Gate Velocity Check';
    }

    public function isEnabled() {
        return (bool)Config::get('enable_timegate', true);
    }

    public function check(array $context) {
        $post = $context['post'] ?? [];
        $token = $post['spm_time_token'] ?? '';

        if (empty($token)) {
            // Missing token: likely direct curl / raw POST script bypassing the form page
            return [
                'passed'   => false,
                'score'    => 75,
                'critical' => false,
                'reason'   => __('Missing timestamp security token (possible direct HTTP request).', 'spamarmor')
            ];
        }

        $formRenderedAt = TokenManager::verifyTimeToken($token);
        if ($formRenderedAt === false) {
            return [
                'passed'   => false,
                'score'    => 100,
                'critical' => true,
                'reason'   => __('Forged or invalid timestamp HMAC signature.', 'spamarmor')
            ];
        }

        $now = time();
        $elapsed = $now - $formRenderedAt;
        $minTime = (int)Config::get('min_submission_time', 3);
        $maxTime = (int)Config::get('max_submission_time', 86400);

        // Submissions faster than humanly possible (e.g. < 3 seconds)
        if ($elapsed < $minTime) {
            return [
                'passed'   => false,
                'score'    => 90,
                'critical' => true,
                'reason'   => sprintf(
                    __('Form submitted too fast (%d seconds, minimum allowed is %d seconds).', 'spamarmor'),
                    $elapsed,
                    $minTime
                )
            ];
        }

        // Submissions older than allowed window (expired replay)
        if ($elapsed > $maxTime) {
            return [
                'passed'   => false,
                'score'    => 60,
                'critical' => false,
                'reason'   => sprintf(
                    __('Form submission expired (rendered %d hours ago).', 'spamarmor'),
                    round($elapsed / 3600, 1)
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
