<?php
namespace CyberArmor\Hardening;

use CyberArmor\Core\Config;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CyberArmor WordPress Core & Server Hardening Engine.
 * Injects security HTTP headers, disables XML-RPC, strips generator tags & script versions,
 * blocks author enumeration, and protects the REST API user directory.
 */
class HardeningEngine {
    /**
     * Boot the hardening filters and actions.
     */
    public function boot() {
        if (!Config::get('enable_hardening', true)) {
            return;
        }

        // 1. Inject HTTP Security Headers
        if (Config::get('hardening_security_headers', true)) {
            add_action('send_headers', [$this, 'injectSecurityHeaders']);
        }

        // 2. Disable XML-RPC and remove pingback header
        if (Config::get('hardening_disable_xmlrpc', true)) {
            add_filter('xmlrpc_enabled', '__return_false');
            add_filter('wp_headers', [$this, 'removePingbackHeader']);
        }

        // 3. Information Masking (Hide WP Generator & version strings)
        if (Config::get('hardening_hide_wp_version', true)) {
            remove_action('wp_head', 'wp_generator');
            add_filter('the_generator', '__return_empty_string');
            add_filter('script_loader_src', [$this, 'removeAssetVersionQuery'], 15);
            add_filter('style_loader_src', [$this, 'removeAssetVersionQuery'], 15);
        }

        // 4. Block Author / User Enumeration
        if (Config::get('hardening_block_user_enumeration', true)) {
            add_action('template_redirect', [$this, 'blockAuthorEnumeration']);
        }

        // 5. Protect REST API User Directory
        if (Config::get('hardening_protect_rest_users', true)) {
            add_filter('rest_authentication_errors', [$this, 'restrictRestUserEndpoint']);
        }
    }

    /**
     * Inject modern HTTP Security Headers.
     */
    public function injectSecurityHeaders() {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('X-XSS-Protection: 1; mode=block');
    }

    /**
     * Remove X-Pingback header from HTTP responses.
     *
     * @param array $headers
     * @return array
     */
    public function removePingbackHeader($headers) {
        if (isset($headers['X-Pingback'])) {
            unset($headers['X-Pingback']);
        }
        return $headers;
    }

    /**
     * Remove ?ver= query string from public scripts and styles.
     *
     * @param string $src
     * @return string
     */
    public function removeAssetVersionQuery($src) {
        if (is_admin()) {
            return $src;
        }
        if (strpos($src, 'ver=') !== false) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }

    /**
     * Block author enumeration attempts via ?author=1 or /author/username query.
     */
    public function blockAuthorEnumeration() {
        if (is_admin() || is_user_logged_in()) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $isAuthorQuery = isset($_REQUEST['author']) && is_numeric($_REQUEST['author']);
        $isAuthorArchive = preg_match('/\/author\/[^\/]+/i', $uri);

        if ($isAuthorQuery || $isAuthorArchive) {
            wp_safe_redirect(home_url('/'), 301);
            exit;
        }
    }

    /**
     * Restrict REST API user enumeration (/wp-json/wp/v2/users).
     *
     * @param mixed $result
     * @return mixed
     */
    public function restrictRestUserEndpoint($result) {
        if (!empty($result)) {
            return $result;
        }

        $restRoute = $GLOBALS['wp']->query_vars['rest_route'] ?? '';
        if (empty($restRoute) && isset($_SERVER['REQUEST_URI'])) {
            $restRoute = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
        }

        if (preg_match('#/wp/v2/users#i', $restRoute) && !is_user_logged_in()) {
            return new \WP_Error(
                'cyberarmor_rest_forbidden',
                __('User directory access is restricted to authenticated users.', 'cyberarmor'),
                ['status' => rest_authorization_required_code()]
            );
        }

        return $result;
    }
}

