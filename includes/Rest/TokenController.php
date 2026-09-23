<?php
namespace SpamArmor\Rest;

use SpamArmor\Core\Config;
use SpamArmor\Core\TokenManager;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API controller for cache-proof dynamic token issuance.
 * Provides fresh HMAC timestamp tokens and micro-PoW challenges for pages served from static full-page cache.
 */
class TokenController {
    const NAMESPACE = 'spamarmor/v1';

    /**
     * Register REST API routes.
     */
    public function registerRoutes() {
        register_rest_route(self::NAMESPACE, '/token', [
            'methods'             => 'GET',
            'callback'            => [$this, 'getTokenPayload'],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * Return fresh security tokens and challenge.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getTokenPayload(\WP_REST_Request $request) {
        $powChallenge = TokenManager::createPoWChallenge();
        $timeToken = TokenManager::createTimeToken();
        $honeypotField = TokenManager::getHoneypotFieldName();

        $data = [
            'success'   => true,
            'timeToken' => $timeToken,
            'pow'       => $powChallenge,
            'honeypot'  => $honeypotField
        ];

        $response = new \WP_REST_Response($data, 200);
        // Explicitly instruct proxies and CDNs not to cache this dynamic token endpoint
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');

        return $response;
    }
}
