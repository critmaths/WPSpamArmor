<?php
namespace SpamArmor\Database;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles fast aggregation and retrieval of daily spam/clean submission statistics.
 */
class StatsRepository {
    /**
     * Record a spam event for today.
     *
     * @param string $formType
     */
    public static function recordSpamEvent($formType = 'comment') {
        self::incrementCounter($formType, 'spam');
    }

    /**
     * Record a clean submission event for today.
     *
     * @param string $formType
     */
    public static function recordCleanEvent($formType = 'comment') {
        self::incrementCounter($formType, 'clean');
    }

    /**
     * Increment the spam or clean counter for today.
     *
     * @param string $formType
     * @param string $type 'spam' or 'clean'
     */
    private static function incrementCounter($formType, $type) {
        global $wpdb;

        $table = Schema::getTableName(Schema::STATS_TABLE);
        $today = current_time('Y-m-d');
        $column = ($type === 'spam') ? 'spam_count' : 'clean_count';
        $formType = sanitize_key($formType ?: 'comment');

        // Upsert for specific form type
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$table} (date, form_type, {$column}) 
                 VALUES (%s, %s, 1) 
                 ON DUPLICATE KEY UPDATE {$column} = {$column} + 1",
                $today,
                $formType
            )
        );

        // Also update total 'all' row
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$table} (date, form_type, {$column}) 
                 VALUES (%s, 'all', 1) 
                 ON DUPLICATE KEY UPDATE {$column} = {$column} + 1",
                $today
            )
        );
    }

    /**
     * Get summary metrics for admin dashboard cards.
     *
     * @return array
     */
    public static function getSummaryStats() {
        global $wpdb;

        $table = Schema::getTableName(Schema::STATS_TABLE);
        $today = current_time('Y-m-d');

        // All-time totals from 'all' form_type
        $allTime = $wpdb->get_row(
            "SELECT COALESCE(SUM(spam_count), 0) as total_spam, COALESCE(SUM(clean_count), 0) as total_clean 
             FROM {$table} WHERE form_type = 'all'",
            ARRAY_A
        );

        // Today's numbers
        $todayStats = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT spam_count as today_spam, clean_count as today_clean 
                 FROM {$table} WHERE date = %s AND form_type = 'all'",
                $today
            ),
            ARRAY_A
        );

        if (!is_array($allTime)) {
            $allTime = [];
        }
        if (!is_array($todayStats)) {
            $todayStats = [];
        }

        $totalSpam = (int)($allTime['total_spam'] ?? 0);
        $totalClean = (int)($allTime['total_clean'] ?? 0);
        $todaySpam = (int)($todayStats['today_spam'] ?? 0);
        $todayClean = (int)($todayStats['today_clean'] ?? 0);

        $totalProcessed = $totalSpam + $totalClean;
        $blockRate = $totalProcessed > 0 ? round(($totalSpam / $totalProcessed) * 100, 1) : 0;

        return [
            'total_spam'      => $totalSpam,
            'total_clean'     => $totalClean,
            'today_spam'      => $todaySpam,
            'today_clean'     => $todayClean,
            'block_rate'      => $blockRate,
            'total_processed' => $totalProcessed
        ];
    }

    /**
     * Get 7-day trend for interactive dashboard chart.
     *
     * @return array Array of days with date, spam, clean
     */
    public static function getWeeklyTrend() {
        global $wpdb;

        $table = Schema::getTableName(Schema::STATS_TABLE);
        $days = [];

        // Build list of last 7 dates
        for ($i = 6; $i >= 0; $i--) {
            $d = gmdate('Y-m-d', strtotime("-{$i} days"));
            $days[$d] = [
                'date'  => $d,
                'label' => gmdate('M j', strtotime($d)),
                'spam'  => 0,
                'clean' => 0
            ];
        }

        $startDate = gmdate('Y-m-d', strtotime('-6 days'));
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT date, spam_count, clean_count 
                 FROM {$table} 
                 WHERE form_type = 'all' AND date >= %s 
                 ORDER BY date ASC",
                $startDate
            ),
            ARRAY_A
        );

        if ($results) {
            foreach ($results as $row) {
                if (isset($days[$row['date']])) {
                    $days[$row['date']]['spam'] = (int)$row['spam_count'];
                    $days[$row['date']]['clean'] = (int)$row['clean_count'];
                }
            }
        }

        return array_values($days);
    }
}
