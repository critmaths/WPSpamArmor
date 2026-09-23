<?php
namespace SpamArmor\Protection;

use SpamArmor\Core\Config;
use SpamArmor\Core\TokenManager;
use SpamArmor\Database\LogRepository;
use SpamArmor\Database\StatsRepository;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Protection Engine.
 * Coordinates all registered detection rules, checks whitelists, computes aggregate scores,
 * logs spam events, and determines if a submission is clean or blocked.
 */
class ProtectionEngine {
    /**
     * @var RuleInterface[]
     */
    private $rules = [];

    public function __construct() {
        $this->registerDefaultRules();
    }

    /**
     * Register core protection rules.
     */
    private function registerDefaultRules() {
        $this->registerRule(new HoneypotRule());
        $this->registerRule(new TimeGateRule());
        $this->registerRule(new PoWRule());
        $this->registerRule(new BehavioralRule());
        $this->registerRule(new HeuristicContentRule());
        $this->registerRule(new NetworkRateLimitRule());
    }

    /**
     * Register a new rule.
     *
     * @param RuleInterface $rule
     */
    public function registerRule(RuleInterface $rule) {
        $this->rules[$rule->getId()] = $rule;
    }

    /**
     * Check if the current user or IP is whitelisted.
     *
     * @param string $ip
     * @return bool
     */
    public function isWhitelisted($ip) {
        // 1. Check logged-in user role whitelist
        if (Config::get('whitelist_logged_in', true) && is_user_logged_in()) {
            $user = wp_get_current_user();
            $trustedRoles = (array)Config::get('trusted_roles', ['administrator', 'editor']);
            foreach ($trustedRoles as $role) {
                if (in_array($role, (array)$user->roles, true)) {
                    return true;
                }
            }
        }

        // 2. Check IP whitelist
        $whitelistRaw = Config::get('ip_whitelist', '');
        if (!empty($whitelistRaw)) {
            $allowedIps = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $whitelistRaw))));
            foreach ($allowedIps as $allowed) {
                if ($ip === $allowed) {
                    return true;
                }
                // Basic CIDR matching support
                if (strpos($allowed, '/') !== false && $this->ipInCidr($ip, $allowed)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Evaluate a submission across all active rules.
     *
     * @param array $context
     * @return array [
     *     'is_spam'  => bool,
     *     'score'    => int,
     *     'action'   => string ('allow', 'block', 'spam', 'trash'),
     *     'reasons'  => array,
     *     'rules'    => array
     * ]
     */
    public function evaluate(array $context) {
        // If master switch disabled, bypass all checks
        if (!Config::get('enabled', true)) {
            return [
                'is_spam' => false,
                'score'   => 0,
                'action'  => 'allow',
                'reasons' => [],
                'rules'   => []
            ];
        }

        $ip = $context['ip'] ?? TokenManager::getClientIp();
        $context['ip'] = $ip;

        // Whitelist check
        if ($this->isWhitelisted($ip)) {
            return [
                'is_spam' => false,
                'score'   => 0,
                'action'  => 'allow',
                'reasons' => [__('Whitelisted user or IP address.', 'spamarmor')],
                'rules'   => []
            ];
        }

        do_action('spamarmor_before_evaluate', $context);

        $totalScore = 0;
        $reasons = [];
        $ruleBreakdown = [];
        $isCritical = false;

        foreach ($this->rules as $rule) {
            if (!$rule->isEnabled()) {
                continue;
            }

            $result = $rule->check($context);
            $ruleBreakdown[$rule->getId()] = $result;

            if (!$result['passed']) {
                $totalScore += (int)$result['score'];
                if (!empty($result['reason'])) {
                    $reasons[] = $result['reason'];
                }

                if (!empty($result['critical'])) {
                    $isCritical = true;
                    // Critical failure (honeypot or forged token) triggers immediate failure
                    break;
                }
            }
        }

        $threshold = (int)Config::get('spam_score_threshold', 50);
        $isSpam = ($isCritical || $totalScore >= $threshold);
        $configuredAction = Config::get('spam_action', 'block');
        $action = $isSpam ? $configuredAction : 'allow';

        // Log and record stats if spam detected
        if ($isSpam) {
            StatsRepository::recordSpamEvent($context['form_type'] ?? 'unknown');
            if (Config::get('log_blocked_spam', true)) {
                LogRepository::insert([
                    'form_type' => sanitize_key($context['form_type'] ?? 'unknown'),
                    'ip'        => TokenManager::maskIp($ip),
                    'score'     => min(100, $totalScore),
                    'reasons'   => implode(' | ', $reasons),
                    'content'   => substr(sanitize_textarea_field($context['content'] ?? ''), 0, 500)
                ]);
            }
            do_action('spamarmor_spam_detected', $context, $totalScore, $reasons);
        } else {
            StatsRepository::recordCleanEvent($context['form_type'] ?? 'unknown');
            do_action('spamarmor_clean_submission', $context);
        }

        return [
            'is_spam'  => $isSpam,
            'score'    => min(100, $totalScore),
            'action'   => $action,
            'reasons'  => $reasons,
            'rules'    => $ruleBreakdown
        ];
    }

    /**
     * Simple CIDR matching helper.
     */
    private function ipInCidr($ip, $range) {
        if (strpos($range, '/') === false) {
            return false;
        }
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        if ($ip === false || $subnet === false) {
            return false;
        }
        $mask = -1 << (32 - (int)$bits);
        $subnet &= $mask;
        return ($ip & $mask) === $subnet;
    }
}
