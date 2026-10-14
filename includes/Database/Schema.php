<?php
namespace CyberArmor\Database;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles database schema migration and table creation for CyberArmor.
 */
class Schema {
    const LOGS_TABLE = 'cyberarmor_logs';
    const STATS_TABLE = 'cyberarmor_stats';

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
     * Run table creation via dbDelta and migrate legacy tables if needed.
     */
    public static function createTables() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charsetCollate = $wpdb->get_charset_collate();
        $logsTable = self::getTableName(self::LOGS_TABLE);
        $statsTable = self::getTableName(self::STATS_TABLE);

        // Security events log table
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

        // Daily aggregated metrics table
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

        // Check if legacy tables exist and copy records over if new tables are empty
        self::migrateLegacyData();

        update_option('cyberarmor_db_version', CYBERARMOR_VERSION);
    }

    /**
     * Migrate records from legacy spamarmor_* tables if they exist and target tables are empty.
     */
    private static function migrateLegacyData() {
        global $wpdb;

        $legacyLogs = self::getTableName('spamarmor_logs');
        $newLogs = self::getTableName(self::LOGS_TABLE);

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $legacyLogs)) === $legacyLogs) {
            $newCount = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$newLogs}");
            if ($newCount === 0) {
                $wpdb->query("INSERT IGNORE INTO {$newLogs} (created_at, form_type, ip, score, reasons, content) 
                              SELECT created_at, form_type, ip, score, reasons, content FROM {$legacyLogs}");
            }
        }

        $legacyStats = self::getTableName('spamarmor_stats');
        $newStats = self::getTableName(self::STATS_TABLE);

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $legacyStats)) === $legacyStats) {
            $newStatsCount = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$newStats}");
            if ($newStatsCount === 0) {
                $wpdb->query("INSERT IGNORE INTO {$newStats} (date, form_type, spam_count, clean_count) 
                              SELECT date, form_type, spam_count, clean_count FROM {$legacyStats}");
            }
        }
    }
}
