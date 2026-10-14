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
                <h1><?php esc_html_e('CyberArmor Security Settings', 'cyberarmor'); ?></h1>
                <p class="cyberarmor-subtitle spamarmor-subtitle"><?php esc_html_e('Configure Web Application Firewall (WAF), core hardening, privacy controls, and bot/spam defense.', 'cyberarmor'); ?></p>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['updated']) && $_GET['updated'] === 'true'): ?>
        <div class="notice notice-success is-dismissible">
            <p><strong><?php esc_html_e('Settings saved successfully.', 'cyberarmor'); ?></strong></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=cyberarmor-settings')); ?>">
        <?php wp_nonce_field('cyberarmor_save_settings', 'cyberarmor_save_settings_nonce'); ?>

        <!-- Master Switch & Actions -->
        <div class="cyberarmor-panel spamarmor-panel">
            <h2><?php esc_html_e('General Protection & Action Policy', 'cyberarmor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Protection', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[enabled]" value="1" <?php checked($settings['enabled']); ?> />
                            <?php esc_html_e('Activate CyberArmor security engines globally across the site.', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Action on Spam & Threats', 'cyberarmor'); ?></th>
                    <td>
                        <select name="cyberarmor_settings[spam_action]">
                            <option value="block" <?php selected($settings['spam_action'], 'block'); ?>><?php esc_html_e('Block Immediately (Reject HTTP request with 403 error)', 'cyberarmor'); ?></option>
                            <option value="spam" <?php selected($settings['spam_action'], 'spam'); ?>><?php esc_html_e('Hold in Spam Queue (Send to native Spam folder)', 'cyberarmor'); ?></option>
                            <option value="trash" <?php selected($settings['spam_action'], 'trash'); ?>><?php esc_html_e('Move to Trash (Silently discard into trash)', 'cyberarmor'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Determines how suspicious form submissions are handled.', 'cyberarmor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('GDPR IP Anonymization', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[anonymize_ips]" value="1" <?php checked($settings['anonymize_ips']); ?> />
                            <?php esc_html_e('Mask the last octet of visitor IP addresses in security logs (e.g. 192.168.1.0).', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Web Application Firewall (WAF) -->
        <div class="cyberarmor-panel spamarmor-panel">
            <h2><?php esc_html_e('🧱 Web Application Firewall (WAF)', 'cyberarmor'); ?></h2>
            <p class="description"><?php esc_html_e('Early Layer-7 request inspection that drops common exploit attempts before WordPress loads themes or queries database.', 'cyberarmor'); ?></p>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Firewall Protection', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[enable_firewall]" value="1" <?php checked($settings['enable_firewall']); ?> />
                            <strong><?php esc_html_e('Enable CyberArmor Web Application Firewall', 'cyberarmor'); ?></strong>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Firewall Rules', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[firewall_block_traversal]" value="1" <?php checked($settings['firewall_block_traversal']); ?> />
                            <?php esc_html_e('Block Directory Traversal & Sensitive File Probes (../, /etc/passwd, boot.ini, wp-config.php, .env)', 'cyberarmor'); ?>
                        </label><br />
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[firewall_block_sqli]" value="1" <?php checked($settings['firewall_block_sqli']); ?> />
                            <?php esc_html_e('Block SQL Injection Probes (UNION SELECT, CONCAT, information_schema, etc.)', 'cyberarmor'); ?>
                        </label><br />
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[firewall_block_code_injection]" value="1" <?php checked($settings['firewall_block_code_injection']); ?> />
                            <?php esc_html_e('Block Code Injection & Cross-Site Scripting (eval, base64_decode, system commands, <script>)', 'cyberarmor'); ?>
                        </label><br />
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[firewall_block_scanners]" value="1" <?php checked($settings['firewall_block_scanners']); ?> />
                            <?php esc_html_e('Block Automated Vulnerability Scanners (sqlmap, nikto, wpscan, gobuster, dirbuster, acunetix)', 'cyberarmor'); ?>
                        </label><br />
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[firewall_block_bad_methods]" value="1" <?php checked($settings['firewall_block_bad_methods']); ?> />
                            <?php esc_html_e('Block Unsafe HTTP Methods (TRACE, TRACK, DEBUG, CONNECT)', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <!-- WordPress Core & Server Hardening -->
        <div class="cyberarmor-panel spamarmor-panel">
            <h2><?php esc_html_e('🔒 Core & Server Hardening', 'cyberarmor'); ?></h2>
            <p class="description"><?php esc_html_e('Enforce security headers, prevent enumeration, and reduce attack surfaces.', 'cyberarmor'); ?></p>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Hardening Engine', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[enable_hardening]" value="1" <?php checked($settings['enable_hardening']); ?> />
                            <strong><?php esc_html_e('Enable System & Core Hardening', 'cyberarmor'); ?></strong>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Hardening Controls', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[hardening_security_headers]" value="1" <?php checked($settings['hardening_security_headers']); ?> />
                            <?php esc_html_e('Inject HTTP Security Headers (X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy)', 'cyberarmor'); ?>
                        </label><br />
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[hardening_disable_xmlrpc]" value="1" <?php checked($settings['hardening_disable_xmlrpc']); ?> />
                            <?php esc_html_e('Disable XML-RPC & Pingback (Eliminates a primary vector for DDoS and brute-force amplification)', 'cyberarmor'); ?>
                        </label><br />
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[hardening_hide_wp_version]" value="1" <?php checked($settings['hardening_hide_wp_version']); ?> />
                            <?php esc_html_e('Hide WordPress Version (Strips generator meta tag and removes ?ver= from public scripts/styles)', 'cyberarmor'); ?>
                        </label><br />
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[hardening_block_user_enumeration]" value="1" <?php checked($settings['hardening_block_user_enumeration']); ?> />
                            <?php esc_html_e('Block Author Query Enumeration (Redirects /?author=1 scanning to homepage)', 'cyberarmor'); ?>
                        </label><br />
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[hardening_protect_rest_users]" value="1" <?php checked($settings['hardening_protect_rest_users']); ?> />
                            <?php esc_html_e('Restrict REST API User Directory (Requires authentication for /wp-json/wp/v2/users)', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Bot & Anti-Spam Defense Layers -->
        <div class="cyberarmor-panel spamarmor-panel">
            <h2><?php esc_html_e('🛡️ Bot & Anti-Spam Defense Layers', 'cyberarmor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Dynamic Honeypot', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[enable_honeypot]" value="1" <?php checked($settings['enable_honeypot']); ?> />
                            <?php esc_html_e('Deploy deceptive salted hidden fields that bots automatically fill.', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('HMAC Time-Gate', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[enable_timegate]" value="1" <?php checked($settings['enable_timegate']); ?> />
                            <?php esc_html_e('Validate human submission speed with cryptographically signed tokens.', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Micro Proof-of-Work (PoW)', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[enable_pow]" value="1" <?php checked($settings['enable_pow']); ?> />
                            <?php esc_html_e('Zero-friction client JS cryptographic puzzle (neutralizes headless botnets).', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Behavioral Entropy', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[enable_behavioral]" value="1" <?php checked($settings['enable_behavioral']); ?> />
                            <?php esc_html_e('Analyze human interaction presence markers (mouse gestures, key cadence, touch).', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Content Heuristics', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[enable_heuristics]" value="1" <?php checked($settings['enable_heuristics']); ?> />
                            <?php esc_html_e('Scan content for excessive URLs, spammy TLDs, BBCode, and bad words.', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Rate Limiter', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[enable_ratelimit]" value="1" <?php checked($settings['enable_ratelimit']); ?> />
                            <?php esc_html_e('Prevent flood attacks and rapid bot submissions per IP address.', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Heuristics & Sensitivity -->
        <div class="cyberarmor-panel spamarmor-panel">
            <h2><?php esc_html_e('Thresholds & Heuristics Tuning', 'cyberarmor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Minimum Submission Time', 'cyberarmor'); ?></th>
                    <td>
                        <input type="number" min="1" max="30" name="cyberarmor_settings[min_submission_time]" value="<?php echo esc_attr($settings['min_submission_time']); ?>" class="small-text" /> <?php esc_html_e('seconds', 'cyberarmor'); ?>
                        <p class="description"><?php esc_html_e('Forms submitted faster than this are flagged as automated bot scripts (default: 3 seconds).', 'cyberarmor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Max Allowed Links', 'cyberarmor'); ?></th>
                    <td>
                        <input type="number" min="0" max="20" name="cyberarmor_settings[max_allowed_links]" value="<?php echo esc_attr($settings['max_allowed_links']); ?>" class="small-text" />
                        <p class="description"><?php esc_html_e('Content containing more links than this threshold triggers spam penalties.', 'cyberarmor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Block Spammy TLDs', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[block_spammy_tlds]" value="1" <?php checked($settings['block_spammy_tlds']); ?> />
                            <?php esc_html_e('Flag links with high-abuse domains (.xyz, .top, .click, .loan, etc.).', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Custom Banned Words', 'cyberarmor'); ?></th>
                    <td>
                        <textarea name="cyberarmor_settings[custom_bad_words]" rows="3" class="large-text"><?php echo esc_textarea($settings['custom_bad_words']); ?></textarea>
                        <p class="description"><?php esc_html_e('Comma-separated list of terms that increase spam penalty score.', 'cyberarmor'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Form Integrations -->
        <div class="cyberarmor-panel spamarmor-panel">
            <h2><?php esc_html_e('Form Plugin & Platform Integrations', 'cyberarmor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('WordPress Core', 'cyberarmor'); ?></th>
                    <td>
                        <label><input type="checkbox" name="cyberarmor_settings[protect_comments]" value="1" <?php checked($settings['protect_comments']); ?> /> <?php esc_html_e('Comments', 'cyberarmor'); ?></label><br />
                        <label><input type="checkbox" name="cyberarmor_settings[protect_registrations]" value="1" <?php checked($settings['protect_registrations']); ?> /> <?php esc_html_e('User Registrations', 'cyberarmor'); ?></label><br />
                        <label><input type="checkbox" name="cyberarmor_settings[protect_login]" value="1" <?php checked($settings['protect_login']); ?> /> <?php esc_html_e('Login Form', 'cyberarmor'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Third-Party Forms', 'cyberarmor'); ?></th>
                    <td>
                        <label><input type="checkbox" name="cyberarmor_settings[protect_cf7]" value="1" <?php checked($settings['protect_cf7']); ?> /> <?php esc_html_e('Contact Form 7', 'cyberarmor'); ?></label><br />
                        <label><input type="checkbox" name="cyberarmor_settings[protect_wpforms]" value="1" <?php checked($settings['protect_wpforms']); ?> /> <?php esc_html_e('WPForms', 'cyberarmor'); ?></label><br />
                        <label><input type="checkbox" name="cyberarmor_settings[protect_gravityforms]" value="1" <?php checked($settings['protect_gravityforms']); ?> /> <?php esc_html_e('Gravity Forms', 'cyberarmor'); ?></label><br />
                        <label><input type="checkbox" name="cyberarmor_settings[protect_fluentforms]" value="1" <?php checked($settings['protect_fluentforms']); ?> /> <?php esc_html_e('Fluent Forms', 'cyberarmor'); ?></label><br />
                        <label><input type="checkbox" name="cyberarmor_settings[protect_woocommerce]" value="1" <?php checked($settings['protect_woocommerce']); ?> /> <?php esc_html_e('WooCommerce (Customer accounts & reviews)', 'cyberarmor'); ?></label>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Whitelists -->
        <div class="cyberarmor-panel spamarmor-panel">
            <h2><?php esc_html_e('Whitelist Rules', 'cyberarmor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Bypass Trusted Roles', 'cyberarmor'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cyberarmor_settings[whitelist_logged_in]" value="1" <?php checked($settings['whitelist_logged_in']); ?> />
                            <?php esc_html_e('Automatically exempt logged-in Administrators, Editors, and Authors from security checks.', 'cyberarmor'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('IP Whitelist', 'cyberarmor'); ?></th>
                    <td>
                        <textarea name="cyberarmor_settings[ip_whitelist]" rows="4" class="large-text" placeholder="192.168.1.100&#10;10.0.0.0/24"><?php echo esc_textarea($settings['ip_whitelist']); ?></textarea>
                        <p class="description"><?php esc_html_e('Enter one IP address or CIDR range per line to permanently whitelist.', 'cyberarmor'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(__('Save Settings', 'cyberarmor')); ?>
    </form>
</div>
