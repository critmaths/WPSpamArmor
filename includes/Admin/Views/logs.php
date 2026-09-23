<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap spamarmor-admin-wrap">
    <div class="spamarmor-header">
        <div class="spamarmor-branding">
            <span class="dashicons dashicons-list-view spamarmor-logo-icon"></span>
            <div>
                <h1><?php esc_html_e('SpamArmor Event Logs', 'spamarmor'); ?></h1>
                <p class="spamarmor-subtitle"><?php esc_html_e('Detailed audit trail of all blocked automated attacks and spam submissions.', 'spamarmor'); ?></p>
            </div>
        </div>
        <div class="spamarmor-header-actions">
            <button type="button" class="button button-secondary" id="spamarmor-clear-logs-btn">
                <span class="dashicons dashicons-trash"></span> <?php esc_html_e('Clear All Logs', 'spamarmor'); ?>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="spamarmor-filter-bar">
        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
            <input type="hidden" name="page" value="spamarmor-logs" />
            <label for="filter-form-type"><?php esc_html_e('Filter by Form:', 'spamarmor'); ?></label>
            <select name="form_type" id="filter-form-type">
                <option value=""><?php esc_html_e('All Forms', 'spamarmor'); ?></option>
                <option value="comment" <?php selected($formFilter, 'comment'); ?>><?php esc_html_e('Comments', 'spamarmor'); ?></option>
                <option value="registration" <?php selected($formFilter, 'registration'); ?>><?php esc_html_e('Registration', 'spamarmor'); ?></option>
                <option value="login" <?php selected($formFilter, 'login'); ?>><?php esc_html_e('Login', 'spamarmor'); ?></option>
                <option value="cf7" <?php selected($formFilter, 'cf7'); ?>><?php esc_html_e('Contact Form 7', 'spamarmor'); ?></option>
                <option value="wpforms" <?php selected($formFilter, 'wpforms'); ?>><?php esc_html_e('WPForms', 'spamarmor'); ?></option>
                <option value="gravityforms" <?php selected($formFilter, 'gravityforms'); ?>><?php esc_html_e('Gravity Forms', 'spamarmor'); ?></option>
                <option value="fluentforms" <?php selected($formFilter, 'fluentforms'); ?>><?php esc_html_e('Fluent Forms', 'spamarmor'); ?></option>
                <option value="woocommerce" <?php selected($formFilter, 'woocommerce'); ?>><?php esc_html_e('WooCommerce', 'spamarmor'); ?></option>
            </select>
            <button type="submit" class="button"><?php esc_html_e('Apply Filter', 'spamarmor'); ?></button>
            <?php if (!empty($formFilter)): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=spamarmor-logs')); ?>" class="button button-link"><?php esc_html_e('Reset', 'spamarmor'); ?></a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="spamarmor-panel">
        <?php if (empty($logs)): ?>
            <div class="spamarmor-empty-state">
                <span class="dashicons dashicons-saved"></span>
                <p><?php esc_html_e('No log entries found.', 'spamarmor'); ?></p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 140px;"><?php esc_html_e('Date / Time', 'spamarmor'); ?></th>
                        <th style="width: 110px;"><?php esc_html_e('Form Type', 'spamarmor'); ?></th>
                        <th style="width: 130px;"><?php esc_html_e('IP Address', 'spamarmor'); ?></th>
                        <th style="width: 70px;"><?php esc_html_e('Score', 'spamarmor'); ?></th>
                        <th><?php esc_html_e('Detection Reasons', 'spamarmor'); ?></th>
                        <th><?php esc_html_e('Payload Preview', 'spamarmor'); ?></th>
                        <th style="width: 100px;"><?php esc_html_e('Actions', 'spamarmor'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log['created_at']); ?></td>
                            <td><span class="form-tag"><?php echo esc_html(strtoupper($log['form_type'])); ?></span></td>
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
                                    <span class="description"><?php esc_html_e('Empty content', 'spamarmor'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="button button-small spamarmor-quick-whitelist" data-ip="<?php echo esc_attr($log['ip']); ?>">
                                    <?php esc_html_e('Whitelist', 'spamarmor'); ?>
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
                        <span class="displaying-num"><?php echo sprintf(esc_html__('%d items', 'spamarmor'), $totalLogs); ?></span>
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
