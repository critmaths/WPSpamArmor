<?php
namespace CyberArmor\Firewall;

use CyberArmor\Core\Config;
use CyberArmor\Core\Plugin;
use CyberArmor\Core\TokenManager;
use CyberArmor\Database\LogRepository;
use CyberArmor\Database\StatsRepository;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CyberArmor Layer-7 Web Application Firewall (WAF).
 * Provides early lightweight request inspection against SQL Injection, Directory Traversal,
 * Remote File Inclusion / Code Execution, Malicious Scanners, and Unsafe Request Methods.
 */
class FirewallEngine {
    /**
     * Known automated vulnerability scanners and malicious exploitation tools.
     */
    private static $scannerSignatures = [
        'sqlmap',
        'nikto',
        'wpscan',
        'gobuster',
        'dirbuster',
        'acunetix',
        'nmap',
        'masscan',
        'zgrab',
        'nessus',
        'openvas',
        'havij',
        'burpcollaborator',
        'beefframework',
        'commix',
        'arachni',
        'whatweb'
    ];

    /**
     * Boot the firewall listener early.
     */
    public function boot() {
        if (!Config::get('enable_firewall', true)) {
            return;
        }

        add_action('init', [$this, 'inspectRequest'], 1);
    }

    /**
     * Inspect incoming request for attack signatures.
     */
    public function inspectRequest() {
        // Skip background cron and command-line execution
        if (defined('DOING_CRON') && DOING_CRON) {
            return;
        }
        if (defined('WP_CLI') && WP_CLI) {
            return;
        }

        $ip = TokenManager::getClientIp();

        // Whitelist check
        if (Plugin::instance()->getProtectionEngine()->isWhitelisted($ip)) {
            return;
        }

        // 1. Inspect HTTP request method
        if (Config::get('firewall_block_bad_methods', true)) {
            $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
            if (in_array($method, ['TRACE', 'TRACK', 'DEBUG', 'CONNECT'], true)) {
                $this->blockRequest('Disallowed HTTP request method: ' . $method, $ip);
            }
        }

        // 2. Inspect User-Agent for known vulnerability scanners
        if (Config::get('firewall_block_scanners', true)) {
            $userAgent = strtolower(TokenManager::getUserAgent());
            if (!empty($userAgent)) {
                foreach (self::$scannerSignatures as $scanner) {
                    if (strpos($userAgent, $scanner) !== false) {
                        $this->blockRequest('Automated vulnerability scanner signature detected: ' . $scanner, $ip);
                    }
                }
            }
        }

        // Prepare combined URI and Query String for inspection
        $requestUri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
        $queryString = sanitize_text_field(wp_unslash($_SERVER['QUERY_STRING'] ?? ''));
        $combinedTarget = rawurldecode($requestUri . ' ' . $queryString);

        // 3. Inspect for Directory Traversal & Sensitive File Probing
        if (Config::get('firewall_block_traversal', true)) {
            if ($this->detectDirectoryTraversal($combinedTarget)) {
                $this->blockRequest('Directory traversal or sensitive system file probing', $ip, $requestUri);
            }
        }

        // 4. Inspect for SQL Injection (SQLi)
        if (Config::get('firewall_block_sqli', true)) {
            if ($this->detectSqlInjection($combinedTarget)) {
                $this->blockRequest('SQL injection probe detected', $ip, $requestUri);
            }
        }

        // 5. Inspect for Remote Code Execution & Script Injection
        if (Config::get('firewall_block_code_injection', true)) {
            if ($this->detectCodeInjection($combinedTarget)) {
                $this->blockRequest('Remote code execution or cross-site scripting payload detected', $ip, $requestUri);
            }
        }
    }

    /**
     * Detect directory traversal patterns and sensitive file requests.
     *
     * @param string $input
     * @return bool
     */
    private function detectDirectoryTraversal($input) {
        $patterns = [
            '/\.\.\//',
            '/\.\.\\\\/',
            '/etc\/(passwd|shadow|hosts|group)/i',
            '/boot\.ini/i',
            '/win\.ini/i',
            '/wp-config\.php/i',
            '/\.env($|\/|\?)/i',
            '/\.git(\/|$|\?)/i',
            '/\.htaccess($|\/|\?)/i',
            '/eval-stdin\.php/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Detect common SQL injection patterns.
     *
     * @param string $input
     * @return bool
     */
    private function detectSqlInjection($input) {
        $patterns = [
            '/union(\s+|\+)(all\s+|all\+)?select/i',
            '/concat\s*\(/i',
            '/information_schema/i',
            '/benchmark\s*\(\s*\d+\s*,/i',
            '/sleep\s*\(\s*\d+\s*\)/i',
            '/into(\s+|\+)outfile/i',
            '/waitfor(\s+|\+)delay/i',
            '/order(\s+|\+)by(\s+|\+)\d+/i',
            '/select.+from.+information_schema/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Detect remote code execution, eval, and cross-site scripting vectors.
     *
     * @param string $input
     * @return bool
     */
    private function detectCodeInjection($input) {
        $patterns = [
            '/base64_decode\s*\(/i',
            '/\beval\s*\(/i',
            '/passthru\s*\(/i',
            '/shell_exec\s*\(/i',
            '/\bsystem\s*\(/i',
            '/(\$_GET|\$_POST|\$_REQUEST)\[/i',
            '/<script[^>]*>/i',
            '/javascript:\s*[a-z0-9]/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle blocking a malicious request.
     *
     * @param string $reason
     * @param string $ip
     * @param string $content
     */
    private function blockRequest($reason, $ip, $content = '') {
        // Record event in stats and log
        StatsRepository::recordSpamEvent('firewall_waf');

        if (Config::get('log_blocked_spam', true)) {
            LogRepository::insert([
                'form_type' => 'firewall_waf',
                'ip'        => TokenManager::maskIp($ip),
                'score'     => 100,
                'reasons'   => 'CyberArmor WAF: ' . $reason,
                'content'   => substr($content ?: ($_SERVER['REQUEST_URI'] ?? ''), 0, 500)
            ]);
        }

        do_action('cyberarmor_firewall_blocked', $reason, $ip);

        status_header(403);
        nocache_headers();

        $referenceId = substr(md5($ip . time() . $reason), 0, 12);
        $maskedIp = TokenManager::maskIp($ip);

        $title = __('Access Denied - CyberArmor WAF', 'cyberarmor');
        $message = sprintf(
            '<h1>%s</h1><p>%s</p><hr /><p><small>%s: <code>%s</code> | %s: <code>%s</code></small></p>',
            esc_html__('Blocked by CyberArmor Web Application Firewall', 'cyberarmor'),
            esc_html__('Your request was identified as potentially malicious and was blocked by site security defenses.', 'cyberarmor'),
            esc_html__('Reference ID', 'cyberarmor'),
            esc_html($referenceId),
            esc_html__('Client IP', 'cyberarmor'),
            esc_html($maskedIp)
        );

        wp_die($message, $title, ['response' => 403, 'back_link' => false]);
    }
}

