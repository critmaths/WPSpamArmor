<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap cyberarmor-admin-wrap spamarmor-admin-wrap">
    <div class="cyberarmor-header spamarmor-header">
        <div class="cyberarmor-branding spamarmor-branding">
            <span class="dashicons dashicons-list-view cyberarmor-logo-icon spamarmor-logo-icon"></span>
            <div>
                <h1><?php esc_html_e('CyberArmor Security Logs', 'cyberarmor'); ?></h1>
                <p class="cyberarmor-subtitle spamarmor-subtitle"><?php esc_html_e('Detailed audit trail of all blocked WAF exploits, malicious bots, and spam submissions.', 'cyberarmor'); ?></p>
            </div>
        </div>
        <div class="cyberarmor-header-actions spamarmor-header-actions">
            <button type="button" class="button button-secondary" id="cyberarmor-clear-logs-btn">
                <span class="dashicons dashicons-trash"></span> <?php esc_html_e('Clear All Logs', 'cyberarmor'); ?>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="cyberarmor-filter-bar spamarmor-filter-bar">
        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
            <input type="hidden" name="page" value="cyberarmor-logs" />
            <label for="filter-form-type"><?php esc_html_e('Filter by Event Type:', 'cyberarmor'); ?></label>
            <select name="form_type" id="filter-form-type">
                <option value=""><?php esc_html_e('All Events', 'cyberarmor'); ?></option>
                <option value="firewall_waf" <?php selected($formFilter, 'firewall_waf'); ?>><?php esc_html_e('🧱 WAF Firewall Interceptions', 'cyberarmor'); ?></option>
                <option value="comment" <?php selected($formFilter, 'comment'); ?>><?php esc_html_e('Comments', 'cyberarmor'); ?></option>
                <option value="registration" <?php selected($formFilter, 'registration'); ?>><?php esc_html_e('User Registrations', 'cyberarmor'); ?></option>
                <option value="login" <?php selected($formFilter, 'login'); ?>><?php esc_html_e('Login Form', 'cyberarmor'); ?></option>
                <option value="cf7" <?php selected($formFilter, 'cf7'); ?>><?php esc_html_e('Contact Form 7', 'cyberarmor'); ?></option>
                <option value="wpforms" <?php selected($formFilter, 'wpforms'); ?>><?php esc_html_e('WPForms', 'cyberarmor'); ?></option>
                <option value="gravityforms" <?php selected($formFilter, 'gravityforms'); ?>><?php esc_html_e('Gravity Forms', 'cyberarmor'); ?></option>
                <option value="fluentforms" <?php selected($formFilter, 'fluentforms'); ?>><?php esc_html_e('Fluent Forms', 'cyberarmor'); ?></option>
                <option value="woocommerce" <?php selected($formFilter, 'woocommerce'); ?>><?php esc_html_e('WooCommerce', 'cyberarmor'); ?></option>
            </select>
            <button type="submit" class="button"><?php esc_html_e('Apply Filter', 'cyberarmor'); ?></button>
            <?php if (!empty($formFilter)): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cyberarmor-logs')); ?>" class="button button-link"><?php esc_html_e('Reset', 'cyberarmor'); ?></a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="cyberarmor-panel spamarmor-panel">
        <?php if (empty($logs)): ?>
            <div class="cyberarmor-empty-state spamarmor-empty-state">
                <span class="dashicons dashicons-saved"></span>
                <p><?php esc_html_e('No log entries found.', 'cyberarmor'); ?></p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 140px;"><?php esc_html_e('Date / Time', 'cyberarmor'); ?></th>
                        <th style="width: 130px;"><?php esc_html_e('Event Type', 'cyberarmor'); ?></th>
                        <th style="width: 130px;"><?php esc_html_e('IP Address', 'cyberarmor'); ?></th>
                        <th style="width: 70px;"><?php esc_html_e('Score', 'cyberarmor'); ?></th>
                        <th><?php esc_html_e('Detection Reasons', 'cyberarmor'); ?></th>
                        <th><?php esc_html_e('Request / Payload', 'cyberarmor'); ?></th>
                        <th style="width: 100px;"><?php esc_html_e('Actions', 'cyberarmor'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log['created_at']); ?></td>
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
                                <?php if (!empty($log['content'])): ?>
                                    <small class="content-preview"><?php echo esc_html(wp_trim_words($log['content'], 12)); ?></small>
                                <?php else: ?>
                                    <span class="description"><?php esc_html_e('Empty content', 'cyberarmor'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="button button-small cyberarmor-quick-whitelist spamarmor-quick-whitelist" data-ip="<?php echo esc_attr($log['ip']); ?>">
                                    <?php esc_html_e('Whitelist', 'cyberarmor'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php echo sprintf(esc_html__('%d items', 'cyberarmor'), $totalLogs); ?></span>
                        <span class="pagination-links">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a class="page-numbers <?php echo ($i === $paged) ? 'current' : ''; ?>" href="<?php echo esc_url(add_query_arg(['paged' => $i])); ?>">
                                    <?php echo esc_html($i); ?>
                                </a>
                            <?php endfor; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
