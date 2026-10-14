<?php
namespace CyberArmor\Core;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages plugin configuration, options, defaults, and input sanitization.
 */
class Config {
    const OPTION_KEY = 'cyberarmor_settings';
    const LEGACY_OPTION_KEY = 'spamarmor_settings';

    /**
     * Default configuration values.
     */
    private static $defaults = [
        // Master switch
        'enabled'                          => true,

        // Bot & Anti-Spam Protection Layers
        'enable_honeypot'                  => true,
        'enable_timegate'                  => true,
        'enable_pow'                       => true,
        'enable_behavioral'                => true,
        'enable_heuristics'                => true,
        'enable_ratelimit'                 => true,

        // Time-Gate Settings
        'min_submission_time'              => 3,    // Minimum seconds required before submission
        'max_submission_time'              => 86400,// Maximum valid window (24 hours)

        // Micro Proof-of-Work Settings
        'pow_difficulty'                   => 3,    // Leading zero hex characters required (default: 3 chars, ~4096 hashes)

        // Content Heuristics Settings
        'max_allowed_links'                => 2,    // Submissions with more links trigger spam scoring
        'block_spammy_tlds'                => true, // Block suspicious TLDs (.xyz, .top, .click, .loan, etc.)
        'block_bbcode'                     => true, // Flag [url=...] or bbcode anchor spam
        'spam_score_threshold'             => 50,   // Cumulative score >= 50 marks as spam, >= 80 blocks immediately
        'custom_bad_words'                 => '',   // Comma-separated list of banned terms

        // Rate Limiting Settings
        'rate_limit_max'                   => 5,    // Max submissions per IP window
        'rate_limit_window'                => 60,   // Window in seconds (1 minute)

        // Action on Spam
        'spam_action'                      => 'block', // 'block' (403), 'spam' (spam queue), 'trash' (trash)

        // Web Application Firewall (WAF)
        'enable_firewall'                  => true,
        'firewall_block_bad_methods'       => true, // Block TRACE, TRACK, DEBUG, etc.
        'firewall_block_scanners'          => true, // Block sqlmap, nikto, wpscan, etc.
        'firewall_block_traversal'         => true, // Block ../, /etc/passwd, boot.ini
        'firewall_block_sqli'              => true, // Block SQL injection vectors
        'firewall_block_code_injection'    => true, // Block eval(, base64_decode(, XSS vectors

        // WordPress Core & Server Hardening
        'enable_hardening'                 => true,
        'hardening_security_headers'       => true, // Send X-Frame-Options, X-Content-Type-Options, etc.
        'hardening_disable_xmlrpc'         => true, // Disable XML-RPC and remove pingback
        'hardening_hide_wp_version'        => true, // Strip generator meta and version queries
        'hardening_block_user_enumeration' => true, // Redirect /?author=1 scanning
        'hardening_protect_rest_users'     => true, // Require authentication for /wp/v2/users

        // Form Integrations
        'protect_comments'                 => true,
        'protect_registrations'            => true,
        'protect_login'                    => true,
        'protect_cf7'                      => true,
        'protect_wpforms'                  => true,
        'protect_gravityforms'             => true,
        'protect_fluentforms'              => true,
        'protect_woocommerce'              => true,

        // Logging & Privacy
        'log_blocked_spam'                 => true,
        'log_retention_days'               => 30,
        'anonymize_ips'                    => true, // Mask last octet for GDPR compliance

        // Whitelisting
        'ip_whitelist'                     => '',   // One IP or CIDR per line
        'whitelist_logged_in'              => true, // Bypass check for trusted logged-in roles
        'trusted_roles'                    => ['administrator', 'editor', 'author']
    ];

    /**
     * Get all settings merged with defaults.
     *
     * @return array
     */
    public static function getAll() {
        $saved = get_option(self::OPTION_KEY, null);

        // Auto-migrate from legacy option key if needed
        if ($saved === null) {
            $legacy = get_option(self::LEGACY_OPTION_KEY, null);
            if (is_array($legacy)) {
                $saved = $legacy;
                update_option(self::OPTION_KEY, $saved);
            } else {
                $saved = [];
            }
        }

        if (!is_array($saved)) {
            $saved = [];
        }

        return wp_parse_args($saved, self::$defaults);
    }

    /**
     * Get a specific setting value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        $all = self::getAll();
        if (array_key_exists($key, $all)) {
            return $all[$key];
        }
        return $default !== null ? $default : (self::$defaults[$key] ?? null);
    }

    /**
     * Update a specific setting.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set($key, $value) {
        $all = self::getAll();
        $all[$key] = $value;
        return update_option(self::OPTION_KEY, $all);
    }

    /**
     * Update entire settings array with strict sanitization.
     *
     * @param array $input
     * @return bool
     */
    public static function saveAll(array $input) {
        $clean = [];

        // Master & Bot toggles
        $clean['enabled']                          = !empty($input['enabled']);
        $clean['enable_honeypot']                  = !empty($input['enable_honeypot']);
        $clean['enable_timegate']                  = !empty($input['enable_timegate']);
        $clean['enable_pow']                       = !empty($input['enable_pow']);
        $clean['enable_behavioral']                = !empty($input['enable_behavioral']);
        $clean['enable_heuristics']                = !empty($input['enable_heuristics']);
        $clean['enable_ratelimit']                 = !empty($input['enable_ratelimit']);

        // Time-Gate & PoW
        $clean['min_submission_time']              = max(1, min(30, intval($input['min_submission_time'] ?? 3)));
        $clean['max_submission_time']              = max(300, intval($input['max_submission_time'] ?? 86400));
        $clean['pow_difficulty']                   = max(1, min(6, intval($input['pow_difficulty'] ?? 3)));

        // Heuristics
        $clean['max_allowed_links']                = max(0, min(20, intval($input['max_allowed_links'] ?? 2)));
        $clean['block_spammy_tlds']                = !empty($input['block_spammy_tlds']);
        $clean['block_bbcode']                     = !empty($input['block_bbcode']);
        $clean['spam_score_threshold']             = max(10, min(100, intval($input['spam_score_threshold'] ?? 50)));
        $clean['custom_bad_words']                 = sanitize_textarea_field($input['custom_bad_words'] ?? '');

        // Rate Limiter
        $clean['rate_limit_max']                   = max(1, min(100, intval($input['rate_limit_max'] ?? 5)));
        $clean['rate_limit_window']                = max(10, min(3600, intval($input['rate_limit_window'] ?? 60)));

        // Action on Spam
        $allowedActions = ['block', 'spam', 'trash'];
        $clean['spam_action']                      = in_array($input['spam_action'] ?? 'block', $allowedActions, true) ? $input['spam_action'] : 'block';

        // Firewall (WAF)
        $clean['enable_firewall']                  = !empty($input['enable_firewall']);
        $clean['firewall_block_bad_methods']       = !empty($input['firewall_block_bad_methods']);
        $clean['firewall_block_scanners']          = !empty($input['firewall_block_scanners']);
        $clean['firewall_block_traversal']         = !empty($input['firewall_block_traversal']);
        $clean['firewall_block_sqli']              = !empty($input['firewall_block_sqli']);
        $clean['firewall_block_code_injection']    = !empty($input['firewall_block_code_injection']);

        // Hardening
        $clean['enable_hardening']                 = !empty($input['enable_hardening']);
        $clean['hardening_security_headers']       = !empty($input['hardening_security_headers']);
        $clean['hardening_disable_xmlrpc']         = !empty($input['hardening_disable_xmlrpc']);
        $clean['hardening_hide_wp_version']        = !empty($input['hardening_hide_wp_version']);
        $clean['hardening_block_user_enumeration'] = !empty($input['hardening_block_user_enumeration']);
        $clean['hardening_protect_rest_users']     = !empty($input['hardening_protect_rest_users']);

        // Form Integrations
        $clean['protect_comments']                 = !empty($input['protect_comments']);
        $clean['protect_registrations']            = !empty($input['protect_registrations']);
        $clean['protect_login']                    = !empty($input['protect_login']);
        $clean['protect_cf7']                      = !empty($input['protect_cf7']);
        $clean['protect_wpforms']                  = !empty($input['protect_wpforms']);
        $clean['protect_gravityforms']             = !empty($input['protect_gravityforms']);
        $clean['protect_fluentforms']              = !empty($input['protect_fluentforms']);
        $clean['protect_woocommerce']              = !empty($input['protect_woocommerce']);

        // Logging & Privacy
        $clean['log_blocked_spam']                 = !empty($input['log_blocked_spam']);
        $clean['log_retention_days']               = max(1, min(365, intval($input['log_retention_days'] ?? 30)));
        $clean['anonymize_ips']                    = !empty($input['anonymize_ips']);

        // Whitelist
        $clean['ip_whitelist']                     = sanitize_textarea_field($input['ip_whitelist'] ?? '');
        $clean['whitelist_logged_in']              = !empty($input['whitelist_logged_in']);
        $clean['trusted_roles']                    = is_array($input['trusted_roles'] ?? null)
            ? array_map('sanitize_key', $input['trusted_roles'])
            : ['administrator', 'editor'];

        return update_option(self::OPTION_KEY, $clean);
    }
}
