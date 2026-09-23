<?php
namespace SpamArmor\Database;

use SpamArmor\Core\Config;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles persistence, queries, and pruning for the spam events log table.
 */
class LogRepository {
    /**
     * Insert a new spam log entry.
     *
     * @param array $data
     * @return int|false Inserted ID or false
     */
    public static function insert(array $data) {
        global $wpdb;

        $table = Schema::getTableName(Schema::LOGS_TABLE);

        $inserted = $wpdb->insert(
            $table,
            [
                'created_at' => current_time('mysql'),
                'form_type'  => sanitize_key($data['form_type'] ?? 'comment'),
                'ip'         => sanitize_text_field($data['ip'] ?? ''),
                'score'      => intval($data['score'] ?? 0),
                'reasons'    => sanitize_textarea_field($data['reasons'] ?? ''),
                'content'    => sanitize_textarea_field($data['content'] ?? '')
            ],
            ['%s', '%s', '%s', '%d', '%s', '%s']
        );

        // Periodically prune old logs (1% chance per insert)
        if (mt_rand(1, 100) === 1) {
            self::pruneOldLogs();
        }

        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * Retrieve paginated log entries.
     *
     * @param int $limit
     * @param int $offset
     * @param string $formType
     * @return array
     */
    public static function getRecent($limit = 50, $offset = 0, $formType = '') {
        global $wpdb;

        $table = Schema::getTableName(Schema::LOGS_TABLE);
        $limit = max(1, min(200, (int)$limit));
        $offset = max(0, (int)$offset);

        if (!empty($formType) && $formType !== 'all') {
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table} WHERE form_type = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
                    $formType,
                    $limit,
                    $offset
                ),
                ARRAY_A
            ) ?: [];
        }

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get total count of log entries.
     *
     * @param string $formType
     * @return int
     */
    public static function getTotalCount($formType = '') {
        global $wpdb;

        $table = Schema::getTableName(Schema::LOGS_TABLE);

        if (!empty($formType) && $formType !== 'all') {
            return (int)$wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE form_type = %s", $formType)
            );
        }

        return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    /**
     * Prune logs older than configured retention period.
     *
     * @param int|null $days
     * @return int Number of deleted rows
     */
    public static function pruneOldLogs($days = null) {
        global $wpdb;

        if ($days === null) {
            $days = (int)Config::get('log_retention_days', 30);
        }

        $table = Schema::getTableName(Schema::LOGS_TABLE);
        $cutoffDate = gmdate('Y-m-d H:i:s', strtotime("-{$days} days"));

        return (int)$wpdb->query(
            $wpdb->prepare("DELETE FROM {$table} WHERE created_at < %s", $cutoffDate)
        );
    }

    /**
     * Clear all log entries.
     *
     * @return bool
     */
    public static function clearAll() {
        global $wpdb;
        $table = Schema::getTableName(Schema::LOGS_TABLE);
        return $wpdb->query("TRUNCATE TABLE {$table}") !== false;
    }
}
