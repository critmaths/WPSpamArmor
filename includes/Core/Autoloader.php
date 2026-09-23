<?php
namespace SpamArmor\Core;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lightweight PSR-4 autoloader for SpamArmor namespace.
 */
class Autoloader {
    /**
     * Namespace prefix.
     */
    const PREFIX = 'SpamArmor\\';

    /**
     * Register autoloader with SPL stack.
     */
    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Autoload callback.
     *
     * @param string $class Fully qualified class name.
     */
    public static function autoload($class) {
        if (strpos($class, self::PREFIX) !== 0) {
            return;
        }

        $relativeClass = substr($class, strlen(self::PREFIX));
        $file = SPAMARMOR_PLUGIN_DIR . 'includes/' . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}
