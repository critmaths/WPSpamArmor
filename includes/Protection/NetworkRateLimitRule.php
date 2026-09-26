<?php
namespace SpamArmor\Protection;

use SpamArmor\Core\Config;
use SpamArmor\Core\TokenManager;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Network rate limiting and User-Agent scanner rule.
 * Blocks botnet flood waves, headless scrape scripts, and brute-force rapid submissions.
 */
class NetworkRateLimitRule implements RuleInterface {
    /**
     * Known automated crawler / scraper user agents.
     */
    const SUSPICIOUS_AGENTS = [
        'curl/', 'python-requests', 'python-urllib', 'aiohttp',
        'postmanruntime', 'insomnia/', 'httpclient', 'libwww-perl',
        'scrapy', 'winhttp', 'apache-httpclient', 'go-http-client'
    ];

    public function getId() {
        return 'ratelimit';
    }

    public function getName() {
        return 'Network Rate Limiting & User-Agent Scanner';
    }

    public function isEnabled() {
        return (bool)Config::get('enable_ratelimit', true);
    }

    public function check(array $context) {
        $ip = $context['ip'] ?? TokenManager::getClientIp();
        $userAgent = strtolower($context['user_agent'] ?? TokenManager::getUserAgent());

        // 1. User-Agent sanity check
        if (empty($userAgent)) {
            return [
                'passed'   => false,
                'score'    => 70,
                'critical' => false,
                'reason'   => __('Empty User-Agent header (typical of automated bots).', 'spamarmor-open-source-spam-bot-protection')
            ];
        }

        foreach (self::SUSPICIOUS_AGENTS as $botSignature) {
            if (strpos($userAgent, $botSignature) !== false) {
                return [
                    'passed'   => false,
                    'score'    => 95,
                    'critical' => true,
                    'reason'   => sprintf(__('Automated script User-Agent detected (%s).', 'spamarmor-open-source-spam-bot-protection'), $botSignature)
                ];
            }
        }

        // 2. IP Rate Limiting (Flood Protection) using WordPress Transients
        $rateLimitMax = (int)Config::get('rate_limit_max', 5);
        $rateWindow   = (int)Config::get('rate_limit_window', 60);

        $transientKey = 'spamarmor_flood_' . md5($ip);
        $currentCount = (int)get_transient($transientKey);

        if ($currentCount >= $rateLimitMax) {
            return [
                'passed'   => false,
                'score'    => 100,
                'critical' => true,
                'reason'   => sprintf(
                    __('Rate limit exceeded (%d submissions in %d seconds). Flood blocked.', 'spamarmor-open-source-spam-bot-protection'),
                    $currentCount,
                    $rateWindow
                )
            ];
        }

        // Increment count and persist transient
        set_transient($transientKey, $currentCount + 1, $rateWindow);

        return [
            'passed'   => true,
            'score'    => 0,
            'critical' => false,
            'reason'   => ''
        ];
    }
}
