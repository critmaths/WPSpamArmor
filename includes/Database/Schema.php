<?php
namespace SpamArmor\Database;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles database schema migration and table creation.
 */
class Schema {
    const LOGS_TABLE = 'spamarmor_logs';
    const STATS_TABLE = 'spamarmor_stats';

    /**
     * Get full table name with WP prefix.
     *
     * @param string $name
     * @return string
     */
    public static function getTableName($name) {
        global $wpdb;
        return $wpdb->prefix . $name;
    }

    /**
     * Run table creation via dbDelta.
     */
    public static function createTables() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charsetCollate = $wpdb->get_charset_collate();
        $logsTable = self::getTableName(self::LOGS_TABLE);
        $statsTable = self::getTableName(self::STATS_TABLE);

        // Logs table
        $sqlLogs = "CREATE TABLE {$logsTable} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            created_at datetime NOT NULL,
            form_type varchar(50) NOT NULL DEFAULT 'comment',
            ip varchar(64) NOT NULL DEFAULT '',
            score int(11) NOT NULL DEFAULT 0,
            reasons text NOT NULL,
            content text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY created_at (created_at),
            KEY form_type (form_type),
            KEY ip (ip)
        ) {$charsetCollate};";

        // Daily aggregated stats table
        $sqlStats = "CREATE TABLE {$statsTable} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            date date NOT NULL,
            form_type varchar(50) NOT NULL DEFAULT 'all',
            spam_count int(11) NOT NULL DEFAULT 0,
            clean_count int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY date_form (date, form_type)
        ) {$charsetCollate};";

        dbDelta($sqlLogs);
        dbDelta($sqlStats);

        update_option('spamarmor_db_version', SPAMARMOR_VERSION);
    }
}
