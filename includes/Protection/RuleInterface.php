<?php
namespace SpamArmor\Protection;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface for all SpamArmor protection detection rules.
 */
interface RuleInterface {
    /**
     * Get rule unique identifier (e.g. 'honeypot', 'timegate', 'pow', 'heuristics').
     *
     * @return string
     */
    public function getId();

    /**
     * Get human-readable rule name.
     *
     * @return string
     */
    public function getName();

    /**
     * Determine if this rule is enabled in settings.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Check the incoming submission.
     *
     * @param array $context Context array containing:
     *                       - 'post': $_POST or form fields array
     *                       - 'form_type': string ('comment', 'registration', 'login', 'cf7', etc.)
     *                       - 'ip': string client IP
     *                       - 'user_agent': string User-Agent header
     *                       - 'content': string submission text content (comment body, message, etc.)
     *                       - 'user_id': int|null
     * @return array [
     *     'passed'    => bool,      // true if clean, false if flagged
     *     'score'     => int,       // spam score penalty (0 - 100)
     *     'critical'  => bool,      // true = instant block, false = additive score
     *     'reason'    => string     // explanation for admin logs
     * ]
     */
    public function check(array $context);
}
