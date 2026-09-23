<?php
namespace SpamArmor\Core;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles cryptographic HMAC tokens, Proof-of-Work challenge issuance & verification,
 * dynamic honeypot field names, and IP privacy masking.
 */
class TokenManager {
    /**
     * Get site secret key for signing tokens.
     *
     * @return string
     */
    public static function getSecret() {
        if (defined('NONCE_SALT') && NONCE_SALT) {
            return NONCE_SALT;
        }
        if (defined('AUTH_KEY') && AUTH_KEY) {
            return AUTH_KEY;
        }
        // Fallback site salt stored in options if salts are undefined
        $salt = get_option('spamarmor_fallback_salt');
        if (!$salt) {
            $salt = function_exists('wp_generate_password') 
                ? wp_generate_password(64, true, true) 
                : bin2hex(random_bytes(32));
            update_option('spamarmor_fallback_salt', $salt);
        }
        return $salt;
    }

    /**
     * Get a deterministic, deceptive honeypot field name for this installation.
     * Changes periodically (e.g. weekly or monthly based on secret hash) or remains stable per site salt.
     *
     * @return string
     */
    public static function getHoneypotFieldName() {
        $hash = substr(hash_hmac('sha256', 'honeypot_field_v1', self::getSecret()), 0, 10);
        return 'spm_hp_' . $hash;
    }

    /**
     * Generate an HMAC signed timestamp token.
     * Format: timestamp.hmac
     *
     * @param int|null $time
     * @return string
     */
    public static function createTimeToken($time = null) {
        if ($time === null) {
            $time = time();
        }
        $hmac = hash_hmac('sha256', (string)$time, self::getSecret());
        return $time . '.' . substr($hmac, 0, 32);
    }

    /**
     * Validate an HMAC signed timestamp token.
     *
     * @param string $token
     * @return int|false Returns original timestamp on success, false on invalid signature
     */
    public static function verifyTimeToken($token) {
        if (empty($token) || !is_string($token)) {
            return false;
        }

        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return false;
        }

        list($timeStr, $receivedHmac) = $parts;
        if (!is_numeric($timeStr)) {
            return false;
        }

        $expectedHmac = substr(hash_hmac('sha256', (string)$timeStr, self::getSecret()), 0, 32);
        if (!hash_equals($expectedHmac, $receivedHmac)) {
            return false;
        }

        return (int)$timeStr;
    }

    /**
     * Generate a Proof-of-Work challenge packet.
     * Format: challenge_string.difficulty.timestamp.hmac
     *
     * @param int|null $difficulty
     * @return array Challenge payload
     */
    public static function createPoWChallenge($difficulty = null) {
        if ($difficulty === null) {
            $difficulty = (int)Config::get('pow_difficulty', 3);
        }

        $timestamp = time();
        $challenge = wp_generate_password(24, false, false);
        $payload = $challenge . ':' . $difficulty . ':' . $timestamp;
        $hmac = substr(hash_hmac('sha256', $payload, self::getSecret()), 0, 32);

        $token = base64_encode($payload . ':' . $hmac);

        return [
            'challenge'  => $challenge,
            'difficulty' => $difficulty,
            'timestamp'  => $timestamp,
            'token'      => $token
        ];
    }

    /**
     * Verify a Proof-of-Work solution.
     *
     * @param string $token The challenge token received from client
     * @param string $nonce The nonce discovered by client
     * @return bool True if valid, false otherwise
     */
    public static function verifyPoWSolution($token, $nonce) {
        if (empty($token) || !is_string($token) || !is_string($nonce)) {
            return false;
        }

        $decoded = base64_decode($token, true);
        if (!$decoded) {
            return false;
        }

        $parts = explode(':', $decoded);
        if (count($parts) !== 4) {
            return false;
        }

        list($challenge, $difficultyStr, $timestampStr, $receivedHmac) = $parts;
        $difficulty = (int)$difficultyStr;
        $timestamp = (int)$timestampStr;

        // Verify challenge signature
        $payload = $challenge . ':' . $difficulty . ':' . $timestamp;
        $expectedHmac = substr(hash_hmac('sha256', $payload, self::getSecret()), 0, 32);
        if (!hash_equals($expectedHmac, $receivedHmac)) {
            return false;
        }

        // Check if challenge is expired (max 2 hours)
        if (time() - $timestamp > 7200 || $timestamp > time() + 300) {
            return false;
        }

        // Verify PoW hash condition: sha256(challenge + nonce) starts with $difficulty zeros
        $hash = hash('sha256', $challenge . $nonce);
        $prefix = str_repeat('0', $difficulty);

        return (substr($hash, 0, $difficulty) === $prefix);
    }

    /**
     * Get real client IP address from server headers.
     *
     * @return string
     */
    public static function getClientIp() {
        $ip = '';
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Standard proxy
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $rawList = explode(',', $_SERVER[$header]);
                $candidate = trim($rawList[0]);
                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    $ip = $candidate;
                    break;
                }
            }
        }

        return $ip ?: '127.0.0.1';
    }

    /**
     * Mask/anonymize IP address for GDPR privacy compliance (e.g. 192.168.1.0 or 2001:db8::).
     *
     * @param string $ip
     * @return string
     */
    public static function maskIp($ip) {
        if (!Config::get('anonymize_ips', true)) {
            return $ip;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/(\d+)\.(\d+)\.(\d+)\.\d+/', '$1.$2.$3.0', $ip);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            if (count($parts) >= 4) {
                return $parts[0] . ':' . $parts[1] . ':' . $parts[2] . '::';
            }
        }

        return $ip;
    }
}
