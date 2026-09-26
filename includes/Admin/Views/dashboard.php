<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap spamarmor-admin-wrap">
    <div class="spamarmor-header">
        <div class="spamarmor-branding">
            <span class="dashicons dashicons-shield-alt spamarmor-logo-icon"></span>
            <div>
                <h1><?php esc_html_e('SpamArmor Dashboard', 'spamarmor-open-source-spam-bot-protection'); ?> <span class="spamarmor-badge-version">v<?php echo esc_html(SPAMARMOR_VERSION); ?></span></h1>
                <p class="spamarmor-subtitle"><?php esc_html_e('Autonomous, privacy-first open-source spam and bot defense for WordPress.', 'spamarmor-open-source-spam-bot-protection'); ?></p>
            </div>
        </div>
        <div class="spamarmor-status-pill">
            <span class="spamarmor-status-dot online"></span>
            <span><?php esc_html_e('Protection Active (Local Mode)', 'spamarmor-open-source-spam-bot-protection'); ?></span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="spamarmor-cards-grid">
        <div class="spamarmor-card">
            <div class="spamarmor-card-icon blue"><span class="dashicons dashicons-shield"></span></div>
            <div class="spamarmor-card-content">
                <span class="spamarmor-card-label"><?php esc_html_e('Total Spam Blocked', 'spamarmor-open-source-spam-bot-protection'); ?></span>
                <span class="spamarmor-card-value"><?php echo number_format_i18n($stats['total_spam']); ?></span>
            </div>
        </div>

        <div class="spamarmor-card">
            <div class="spamarmor-card-icon green"><span class="dashicons dashicons-yes-alt"></span></div>
            <div class="spamarmor-card-content">
                <span class="spamarmor-card-label"><?php esc_html_e('Clean Submissions', 'spamarmor-open-source-spam-bot-protection'); ?></span>
                <span class="spamarmor-card-value"><?php echo number_format_i18n($stats['total_clean']); ?></span>
            </div>
        </div>

        <div class="spamarmor-card">
            <div class="spamarmor-card-icon purple"><span class="dashicons dashicons-chart-pie"></span></div>
            <div class="spamarmor-card-content">
                <span class="spamarmor-card-label"><?php esc_html_e('Spam Block Rate', 'spamarmor-open-source-spam-bot-protection'); ?></span>
                <span class="spamarmor-card-value"><?php echo esc_html($stats['block_rate']); ?>%</span>
            </div>
        </div>

        <div class="spamarmor-card">
            <div class="spamarmor-card-icon amber"><span class="dashicons dashicons-clock"></span></div>
            <div class="spamarmor-card-content">
                <span class="spamarmor-card-label"><?php esc_html_e('Blocked Today', 'spamarmor-open-source-spam-bot-protection'); ?></span>
                <span class="spamarmor-card-value"><?php echo number_format_i18n($stats['today_spam']); ?></span>
            </div>
        </div>
    </div>

    <!-- 7-Day Trend Chart & Protection Layers Overview -->
    <div class="spamarmor-grid-2col">
        <!-- Weekly Trend Chart -->
        <div class="spamarmor-panel">
            <h2><?php esc_html_e('7-Day Activity Trend', 'spamarmor-open-source-spam-bot-protection'); ?></h2>
            <div class="spamarmor-bar-chart">
                <?php
                $maxVal = 1;
                foreach ($weeklyTrend as $day) {
                    $maxVal = max($maxVal, $day['spam'] + $day['clean']);
                }
                foreach ($weeklyTrend as $day):
                    $spamPct = round(($day['spam'] / $maxVal) * 100);
                    $cleanPct = round(($day['clean'] / $maxVal) * 100);
                ?>
                    <div class="spamarmor-chart-col">
                        <div class="spamarmor-bars-wrapper" title="<?php echo esc_attr(sprintf('%s: %d spam blocked, %d clean', $day['label'], $day['spam'], $day['clean'])); ?>">
                            <div class="spamarmor-bar-clean" style="height: <?php echo esc_attr($cleanPct); ?>%;"></div>
                            <div class="spamarmor-bar-spam" style="height: <?php echo esc_attr($spamPct); ?>%;"></div>
                        </div>
                        <span class="spamarmor-chart-label"><?php echo esc_html($day['label']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="spamarmor-chart-legend">
                <span class="legend-item"><span class="legend-dot spam"></span> <?php esc_html_e('Spam Blocked', 'spamarmor-open-source-spam-bot-protection'); ?></span>
                <span class="legend-item"><span class="legend-dot clean"></span> <?php esc_html_e('Clean Allowed', 'spamarmor-open-source-spam-bot-protection'); ?></span>
            </div>
        </div>

        <!-- Defense Layers Status -->
        <div class="spamarmor-panel">
            <h2><?php esc_html_e('Active Defense Layers', 'spamarmor-open-source-spam-bot-protection'); ?></h2>
            <ul class="spamarmor-layers-list">
                <li>
                    <div class="layer-info">
                        <strong><?php esc_html_e('Dynamic Honeypot', 'spamarmor-open-source-spam-bot-protection'); ?></strong>
                        <p><?php esc_html_e('Deploys randomized salted decoy fields invisible to humans.', 'spamarmor-open-source-spam-bot-protection'); ?></p>
                    </div>
                    <span class="layer-badge active"><?php esc_html_e('Armed', 'spamarmor-open-source-spam-bot-protection'); ?></span>
                </li>
                <li>
                    <div class="layer-info">
                        <strong><?php esc_html_e('HMAC Time-Gate', 'spamarmor-open-source-spam-bot-protection'); ?></strong>
                        <p><?php esc_html_e('Detects instantaneous bot clicks (<3s) and stale replays.', 'spamarmor-open-source-spam-bot-protection'); ?></p>
                    </div>
                    <span class="layer-badge active"><?php esc_html_e('Armed', 'spamarmor-open-source-spam-bot-protection'); ?></span>
                </li>
                <li>
                    <div class="layer-info">
                        <strong><?php esc_html_e('Micro Proof-of-Work (PoW)', 'spamarmor-open-source-spam-bot-protection'); ?></strong>
                        <p><?php esc_html_e('Zero-friction client crypto check stops headless scrapers.', 'spamarmor-open-source-spam-bot-protection'); ?></p>
                    </div>
                    <span class="layer-badge active"><?php esc_html_e('Armed', 'spamarmor-open-source-spam-bot-protection'); ?></span>
                </li>
                <li>
                    <div class="layer-info">
                        <strong><?php esc_html_e('Content & Heuristic Scanner', 'spamarmor-open-source-spam-bot-protection'); ?></strong>
                        <p><?php esc_html_e('Scans link counts, high-risk TLDs, BBCode, and spam vectors.', 'spamarmor-open-source-spam-bot-protection'); ?></p>
                    </div>
                    <span class="layer-badge active"><?php esc_html_e('Armed', 'spamarmor-open-source-spam-bot-protection'); ?></span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Recent Blocked Attempts Table -->
    <div class="spamarmor-panel">
        <div class="panel-header-action">
            <h2><?php esc_html_e('Recent Blocked Attacks', 'spamarmor-open-source-spam-bot-protection'); ?></h2>
            <a href="<?php echo esc_url(admin_url('admin.php?page=spamarmor-logs')); ?>" class="button button-secondary"><?php esc_html_e('View All Logs', 'spamarmor-open-source-spam-bot-protection'); ?> &raquo;</a>
        </div>

        <?php if (empty($recentLogs)): ?>
            <div class="spamarmor-empty-state">
                <span class="dashicons dashicons-shield"></span>
                <p><?php esc_html_e('No spam attacks detected recently. Your site is secure!', 'spamarmor-open-source-spam-bot-protection'); ?></p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 140px;"><?php esc_html_e('Time', 'spamarmor-open-source-spam-bot-protection'); ?></th>
                        <th style="width: 120px;"><?php esc_html_e('Form', 'spamarmor-open-source-spam-bot-protection'); ?></th>
                        <th style="width: 130px;"><?php esc_html_e('IP Address', 'spamarmor-open-source-spam-bot-protection'); ?></th>
                        <th style="width: 80px;"><?php esc_html_e('Score', 'spamarmor-open-source-spam-bot-protection'); ?></th>
                        <th><?php esc_html_e('Triggered Reason', 'spamarmor-open-source-spam-bot-protection'); ?></th>
                        <th style="width: 110px;"><?php esc_html_e('Action', 'spamarmor-open-source-spam-bot-protection'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                        <tr>
                            <td><?php echo esc_html(human_time_diff(strtotime($log['created_at']), current_time('timestamp')) . ' ' . __('ago', 'spamarmor-open-source-spam-bot-protection')); ?></td>
                            <td><span class="form-tag"><?php echo esc_html(strtoupper($log['form_type'])); ?></span></td>
                            <td><code><?php echo esc_html($log['ip']); ?></code></td>
                            <td>
                                <span class="score-badge <?php echo $log['score'] >= 80 ? 'critical' : 'warning'; ?>">
                                    <?php echo esc_html($log['score']); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($log['reasons']); ?></td>
                            <td>
                                <button type="button" class="button button-small spamarmor-quick-whitelist" data-ip="<?php echo esc_attr($log['ip']); ?>">
                                    <?php esc_html_e('Whitelist', 'spamarmor-open-source-spam-bot-protection'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
