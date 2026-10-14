<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap cyberarmor-admin-wrap spamarmor-admin-wrap">
    <div class="cyberarmor-header spamarmor-header">
        <div class="cyberarmor-branding spamarmor-branding">
            <span class="dashicons dashicons-shield-alt cyberarmor-logo-icon spamarmor-logo-icon"></span>
            <div>
                <h1><?php esc_html_e('CyberArmor Security Dashboard', 'cyberarmor'); ?> <span class="cyberarmor-badge-version spamarmor-badge-version">v<?php echo esc_html(CYBERARMOR_VERSION); ?></span></h1>
                <p class="cyberarmor-subtitle spamarmor-subtitle"><?php esc_html_e('Enterprise open-source Web Application Firewall (WAF), core hardening, and bot/spam defense.', 'cyberarmor'); ?></p>
            </div>
        </div>
        <div class="cyberarmor-status-pill spamarmor-status-pill">
            <span class="cyberarmor-status-dot spamarmor-status-dot online"></span>
            <span><?php esc_html_e('CyberArmor Armed (100% Local)', 'cyberarmor'); ?></span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="cyberarmor-cards-grid spamarmor-cards-grid">
        <div class="cyberarmor-card spamarmor-card">
            <div class="cyberarmor-card-icon spamarmor-card-icon blue"><span class="dashicons dashicons-shield"></span></div>
            <div class="cyberarmor-card-content spamarmor-card-content">
                <span class="cyberarmor-card-label spamarmor-card-label"><?php esc_html_e('Total Threats Blocked', 'cyberarmor'); ?></span>
                <span class="cyberarmor-card-value spamarmor-card-value"><?php echo number_format_i18n($stats['total_spam']); ?></span>
            </div>
        </div>

        <div class="cyberarmor-card spamarmor-card">
            <div class="cyberarmor-card-icon spamarmor-card-icon red"><span class="dashicons dashicons-warning"></span></div>
            <div class="cyberarmor-card-content spamarmor-card-content">
                <span class="cyberarmor-card-label spamarmor-card-label"><?php esc_html_e('WAF Exploits Blocked', 'cyberarmor'); ?></span>
                <span class="cyberarmor-card-value spamarmor-card-value"><?php echo number_format_i18n($stats['total_waf']); ?></span>
            </div>
        </div>

        <div class="cyberarmor-card spamarmor-card">
            <div class="cyberarmor-card-icon spamarmor-card-icon green"><span class="dashicons dashicons-yes-alt"></span></div>
            <div class="cyberarmor-card-content spamarmor-card-content">
                <span class="cyberarmor-card-label spamarmor-card-label"><?php esc_html_e('Clean Traffic Allowed', 'cyberarmor'); ?></span>
                <span class="cyberarmor-card-value spamarmor-card-value"><?php echo number_format_i18n($stats['total_clean']); ?></span>
            </div>
        </div>

        <div class="cyberarmor-card spamarmor-card">
            <div class="cyberarmor-card-icon spamarmor-card-icon amber"><span class="dashicons dashicons-clock"></span></div>
            <div class="cyberarmor-card-content spamarmor-card-content">
                <span class="cyberarmor-card-label spamarmor-card-label"><?php esc_html_e('Blocked Today', 'cyberarmor'); ?></span>
                <span class="cyberarmor-card-value spamarmor-card-value"><?php echo number_format_i18n($stats['today_spam']); ?></span>
            </div>
        </div>
    </div>

    <!-- 7-Day Trend Chart & Protection Layers Overview -->
    <div class="cyberarmor-grid-2col spamarmor-grid-2col">
        <!-- Weekly Trend Chart -->
        <div class="cyberarmor-panel spamarmor-panel">
            <h2><?php esc_html_e('7-Day Activity Trend', 'cyberarmor'); ?></h2>
            <div class="cyberarmor-bar-chart spamarmor-bar-chart">
                <?php
                $maxVal = 1;
                foreach ($weeklyTrend as $day) {
                    $maxVal = max($maxVal, $day['spam'] + $day['clean']);
                }
                foreach ($weeklyTrend as $day):
                    $spamPct = round(($day['spam'] / $maxVal) * 100);
                    $cleanPct = round(($day['clean'] / $maxVal) * 100);
                ?>
                    <div class="cyberarmor-chart-col spamarmor-chart-col">
                        <div class="cyberarmor-bars-wrapper spamarmor-bars-wrapper" title="<?php echo esc_attr(sprintf('%s: %d threats blocked, %d clean', $day['label'], $day['spam'], $day['clean'])); ?>">
                            <div class="cyberarmor-bar-clean spamarmor-bar-clean" style="height: <?php echo esc_attr($cleanPct); ?>%;"></div>
                            <div class="cyberarmor-bar-spam spamarmor-bar-spam" style="height: <?php echo esc_attr($spamPct); ?>%;"></div>
                        </div>
                        <span class="cyberarmor-chart-label spamarmor-chart-label"><?php echo esc_html($day['label']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="cyberarmor-chart-legend spamarmor-chart-legend">
                <span class="legend-item"><span class="legend-dot spam"></span> <?php esc_html_e('Threats Blocked', 'cyberarmor'); ?></span>
                <span class="legend-item"><span class="legend-dot clean"></span> <?php esc_html_e('Clean Allowed', 'cyberarmor'); ?></span>
            </div>
        </div>

        <!-- Defense Layers Status -->
        <div class="cyberarmor-panel spamarmor-panel">
            <h2><?php esc_html_e('Active CyberArmor Defense Shields', 'cyberarmor'); ?></h2>
            <ul class="cyberarmor-layers-list spamarmor-layers-list">
                <li>
                    <div class="layer-info">
                        <strong><?php esc_html_e('Web Application Firewall (WAF)', 'cyberarmor'); ?></strong>
                        <p><?php esc_html_e('Blocks SQLi, directory traversal, code injection, and vulnerability scanners.', 'cyberarmor'); ?></p>
                    </div>
                    <span class="layer-badge active"><?php esc_html_e('Armed', 'cyberarmor'); ?></span>
                </li>
                <li>
                    <div class="layer-info">
                        <strong><?php esc_html_e('Core & Server Hardening', 'cyberarmor'); ?></strong>
                        <p><?php esc_html_e('Security HTTP headers, generator masking, and user enumeration shield.', 'cyberarmor'); ?></p>
                    </div>
                    <span class="layer-badge active"><?php esc_html_e('Armed', 'cyberarmor'); ?></span>
                </li>
                <li>
                    <div class="layer-info">
                        <strong><?php esc_html_e('Micro Proof-of-Work (PoW)', 'cyberarmor'); ?></strong>
                        <p><?php esc_html_e('Client-side cryptographic puzzle stops headless botnets and automated scrapers.', 'cyberarmor'); ?></p>
                    </div>
                    <span class="layer-badge active"><?php esc_html_e('Armed', 'cyberarmor'); ?></span>
                </li>
                <li>
                    <div class="layer-info">
                        <strong><?php esc_html_e('Dynamic Honeypot & Time-Gate', 'cyberarmor'); ?></strong>
                        <p><?php esc_html_e('Catches automated crawlers and instant bot form submissions.', 'cyberarmor'); ?></p>
                    </div>
                    <span class="layer-badge active"><?php esc_html_e('Armed', 'cyberarmor'); ?></span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Recent Blocked Attempts Table -->
    <div class="cyberarmor-panel spamarmor-panel">
        <div class="panel-header-action">
            <h2><?php esc_html_e('Recent Intercepted Attacks', 'cyberarmor'); ?></h2>
            <a href="<?php echo esc_url(admin_url('admin.php?page=cyberarmor-logs')); ?>" class="button button-secondary"><?php esc_html_e('View All Logs', 'cyberarmor'); ?> &raquo;</a>
        </div>

        <?php if (empty($recentLogs)): ?>
            <div class="cyberarmor-empty-state spamarmor-empty-state">
                <span class="dashicons dashicons-shield"></span>
                <p><?php esc_html_e('No malicious attacks detected recently. Your site is secure!', 'cyberarmor'); ?></p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 140px;"><?php esc_html_e('Time', 'cyberarmor'); ?></th>
                        <th style="width: 130px;"><?php esc_html_e('Type', 'cyberarmor'); ?></th>
                        <th style="width: 130px;"><?php esc_html_e('IP Address', 'cyberarmor'); ?></th>
                        <th style="width: 80px;"><?php esc_html_e('Score', 'cyberarmor'); ?></th>
                        <th><?php esc_html_e('Triggered Reason', 'cyberarmor'); ?></th>
                        <th style="width: 110px;"><?php esc_html_e('Action', 'cyberarmor'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                        <tr>
                            <td><?php echo esc_html(human_time_diff(strtotime($log['created_at']), current_time('timestamp')) . ' ' . __('ago', 'cyberarmor')); ?></td>
                            <td>
                                <?php if ($log['form_type'] === 'firewall_waf'): ?>
                                    <span class="form-tag" style="background:#fee2e2;color:#991b1b;border:1px solid #f87171;font-weight:600;">WAF SHIELD</span>
                                <?php else: ?>
                                    <span class="form-tag"><?php echo esc_html(strtoupper($log['form_type'])); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><code><?php echo esc_html($log['ip']); ?></code></td>
                            <td>
                                <span class="score-badge <?php echo $log['score'] >= 80 ? 'critical' : 'warning'; ?>">
                                    <?php echo esc_html($log['score']); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($log['reasons']); ?></td>
                            <td>
                                <button type="button" class="button button-small cyberarmor-quick-whitelist spamarmor-quick-whitelist" data-ip="<?php echo esc_attr($log['ip']); ?>">
                                    <?php esc_html_e('Whitelist', 'cyberarmor'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
