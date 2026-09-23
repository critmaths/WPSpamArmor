<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap spamarmor-admin-wrap">
    <div class="spamarmor-header">
        <div class="spamarmor-branding">
            <span class="dashicons dashicons-admin-settings spamarmor-logo-icon"></span>
            <div>
                <h1><?php esc_html_e('SpamArmor Settings', 'spamarmor'); ?></h1>
                <p class="spamarmor-subtitle"><?php esc_html_e('Configure detection engines, thresholds, privacy controls, and form integrations.', 'spamarmor'); ?></p>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['updated']) && $_GET['updated'] === 'true'): ?>
        <div class="notice notice-success is-dismissible">
            <p><strong><?php esc_html_e('Settings saved successfully.', 'spamarmor'); ?></strong></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=spamarmor-settings')); ?>">
        <?php wp_nonce_field('spamarmor_save_settings', 'spamarmor_save_settings_nonce'); ?>

        <!-- Master Switch & Actions -->
        <div class="spamarmor-panel">
            <h2><?php esc_html_e('General Protection & Action Policy', 'spamarmor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Protection', 'spamarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="spamarmor_settings[enabled]" value="1" <?php checked($settings['enabled']); ?> />
                            <?php esc_html_e('Activate SpamArmor protection engine globally across the site.', 'spamarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Action on Spam', 'spamarmor'); ?></th>
                    <td>
                        <select name="spamarmor_settings[spam_action]">
                            <option value="block" <?php selected($settings['spam_action'], 'block'); ?>><?php esc_html_e('Block Immediately (Reject HTTP request with 403 error)', 'spamarmor'); ?></option>
                            <option value="spam" <?php selected($settings['spam_action'], 'spam'); ?>><?php esc_html_e('Hold in Spam Queue (Send to native Spam folder)', 'spamarmor'); ?></option>
                            <option value="trash" <?php selected($settings['spam_action'], 'trash'); ?>><?php esc_html_e('Move to Trash (Silently discard into trash)', 'spamarmor'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Determines what happens when a submission fails security checks.', 'spamarmor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('GDPR IP Anonymization', 'spamarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="spamarmor_settings[anonymize_ips]" value="1" <?php checked($settings['anonymize_ips']); ?> />
                            <?php esc_html_e('Mask the last octet of visitor IP addresses in logs (e.g. 192.168.1.0).', 'spamarmor'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Detection Layers -->
        <div class="spamarmor-panel">
            <h2><?php esc_html_e('Multi-Layer Defense Configuration', 'spamarmor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Dynamic Honeypot', 'spamarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="spamarmor_settings[enable_honeypot]" value="1" <?php checked($settings['enable_honeypot']); ?> />
                            <?php esc_html_e('Deploy deceptive salted hidden fields that bots automatically fill.', 'spamarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('HMAC Time-Gate', 'spamarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="spamarmor_settings[enable_timegate]" value="1" <?php checked($settings['enable_timegate']); ?> />
                            <?php esc_html_e('Validate human submission speed with cryptographically signed tokens.', 'spamarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Micro Proof-of-Work (PoW)', 'spamarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="spamarmor_settings[enable_pow]" value="1" <?php checked($settings['enable_pow']); ?> />
                            <?php esc_html_e('Zero-friction client JS cryptographic puzzle (neutralizes headless botnets).', 'spamarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Behavioral Entropy', 'spamarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="spamarmor_settings[enable_behavioral]" value="1" <?php checked($settings['enable_behavioral']); ?> />
                            <?php esc_html_e('Analyze human interaction presence markers (mouse gestures, key cadence, touch).', 'spamarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Content Heuristics', 'spamarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="spamarmor_settings[enable_heuristics]" value="1" <?php checked($settings['enable_heuristics']); ?> />
                            <?php esc_html_e('Scan content for excessive URLs, spammy TLDs, BBCode, and bad words.', 'spamarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Rate Limiter', 'spamarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="spamarmor_settings[enable_ratelimit]" value="1" <?php checked($settings['enable_ratelimit']); ?> />
                            <?php esc_html_e('Prevent flood attacks and rapid bot submissions per IP address.', 'spamarmor'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Heuristics & Sensitivity -->
        <div class="spamarmor-panel">
            <h2><?php esc_html_e('Thresholds & Heuristics Tuning', 'spamarmor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Minimum Submission Time', 'spamarmor'); ?></th>
                    <td>
                        <input type="number" min="1" max="30" name="spamarmor_settings[min_submission_time]" value="<?php echo esc_attr($settings['min_submission_time']); ?>" class="small-text" /> <?php esc_html_e('seconds', 'spamarmor'); ?>
                        <p class="description"><?php esc_html_e('Forms submitted faster than this are flagged as automated bot scripts (default: 3 seconds).', 'spamarmor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Max Allowed Links', 'spamarmor'); ?></th>
                    <td>
                        <input type="number" min="0" max="20" name="spamarmor_settings[max_allowed_links]" value="<?php echo esc_attr($settings['max_allowed_links']); ?>" class="small-text" />
                        <p class="description"><?php esc_html_e('Content containing more links than this threshold triggers spam penalties.', 'spamarmor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Block Spammy TLDs', 'spamarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="spamarmor_settings[block_spammy_tlds]" value="1" <?php checked($settings['block_spammy_tlds']); ?> />
                            <?php esc_html_e('Flag links with high-abuse domains (.xyz, .top, .click, .loan, etc.).', 'spamarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Custom Banned Words', 'spamarmor'); ?></th>
                    <td>
                        <textarea name="spamarmor_settings[custom_bad_words]" rows="3" class="large-text"><?php echo esc_textarea($settings['custom_bad_words']); ?></textarea>
                        <p class="description"><?php esc_html_e('Comma-separated list of terms that increase spam penalty score.', 'spamarmor'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Form Integrations -->
        <div class="spamarmor-panel">
            <h2><?php esc_html_e('Form Plugin & Platform Integrations', 'spamarmor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('WordPress Core', 'spamarmor'); ?></th>
                    <td>
                        <label><input type="checkbox" name="spamarmor_settings[protect_comments]" value="1" <?php checked($settings['protect_comments']); ?> /> <?php esc_html_e('Comments', 'spamarmor'); ?></label><br />
                        <label><input type="checkbox" name="spamarmor_settings[protect_registrations]" value="1" <?php checked($settings['protect_registrations']); ?> /> <?php esc_html_e('User Registrations', 'spamarmor'); ?></label><br />
                        <label><input type="checkbox" name="spamarmor_settings[protect_login]" value="1" <?php checked($settings['protect_login']); ?> /> <?php esc_html_e('Login Form (Brute-force bot defense)', 'spamarmor'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Third-Party Forms', 'spamarmor'); ?></th>
                    <td>
                        <label><input type="checkbox" name="spamarmor_settings[protect_cf7]" value="1" <?php checked($settings['protect_cf7']); ?> /> <?php esc_html_e('Contact Form 7', 'spamarmor'); ?></label><br />
                        <label><input type="checkbox" name="spamarmor_settings[protect_wpforms]" value="1" <?php checked($settings['protect_wpforms']); ?> /> <?php esc_html_e('WPForms', 'spamarmor'); ?></label><br />
                        <label><input type="checkbox" name="spamarmor_settings[protect_gravityforms]" value="1" <?php checked($settings['protect_gravityforms']); ?> /> <?php esc_html_e('Gravity Forms', 'spamarmor'); ?></label><br />
                        <label><input type="checkbox" name="spamarmor_settings[protect_fluentforms]" value="1" <?php checked($settings['protect_fluentforms']); ?> /> <?php esc_html_e('Fluent Forms', 'spamarmor'); ?></label><br />
                        <label><input type="checkbox" name="spamarmor_settings[protect_woocommerce]" value="1" <?php checked($settings['protect_woocommerce']); ?> /> <?php esc_html_e('WooCommerce (Customer accounts & reviews)', 'spamarmor'); ?></label>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Whitelists -->
        <div class="spamarmor-panel">
            <h2><?php esc_html_e('Whitelist Rules', 'spamarmor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Bypass Trusted Roles', 'spamarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="spamarmor_settings[whitelist_logged_in]" value="1" <?php checked($settings['whitelist_logged_in']); ?> />
                            <?php esc_html_e('Automatically exempt logged-in Administrators, Editors, and Authors from spam checks.', 'spamarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('IP Whitelist', 'spamarmor'); ?></th>
                    <td>
                        <textarea name="spamarmor_settings[ip_whitelist]" rows="4" class="large-text" placeholder="192.168.1.100&#10;10.0.0.0/24"><?php echo esc_textarea($settings['ip_whitelist']); ?></textarea>
                        <p class="description"><?php esc_html_e('Enter one IP address or CIDR range per line to permanently whitelist.', 'spamarmor'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(__('Save Settings', 'spamarmor')); ?>
    </form>
</div>
