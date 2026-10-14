<?php
namespace CyberArmor\Protection;

use CyberArmor\Core\Config;
use CyberArmor\Core\TokenManager;

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
        $token = $post['cyberarmor_time_token'] ?? ($post['spamarmor_time_token'] ?? ($post['spm_time_token'] ?? ''));

        if (empty($token)) {
            // Missing token: likely direct curl / raw POST script bypassing the form page
            return [
                'passed'   => false,
                'score'    => 75,
                'critical' => false,
                'reason'   => __('Missing timestamp security token (possible direct HTTP request).', 'cyberarmor')
            ];
        }

        $formRenderedAt = TokenManager::verifyTimeToken($token);
        if ($formRenderedAt === false) {
            return [
                'passed'   => false,
                'score'    => 100,
                'critical' => true,
                'reason'   => __('Forged or invalid timestamp HMAC signature.', 'cyberarmor')
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
                    __('Form submitted too fast (%d seconds, minimum allowed is %d seconds).', 'cyberarmor'),
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
                    __('Form submission expired (rendered %d hours ago).', 'cyberarmor'),
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
