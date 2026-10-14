/**
 * CyberArmor Admin JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        var adminConfig = window.cyberarmorAdmin || window.spamarmorAdmin;
        if (!adminConfig) return;

        // Quick Whitelist IP button
        $(document).on('click', '.cyberarmor-quick-whitelist, .spamarmor-quick-whitelist', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var ip = $btn.data('ip');

            if (!ip) return;

            $btn.prop('disabled', true).text('Adding...');

            $.post(adminConfig.ajaxUrl, {
                action: 'cyberarmor_whitelist_ip',
                ip: ip,
                _ajax_nonce: adminConfig.nonce
            }, function(response) {
                if (response.success) {
                    $btn.replaceWith('<span style="color:#059669; font-weight:600; font-size:11px;">Whitelisted</span>');
                } else {
                    alert(response.data || 'Failed to whitelist IP');
                    $btn.prop('disabled', false).text('Whitelist');
                }
            }).fail(function() {
                alert('Request failed');
                $btn.prop('disabled', false).text('Whitelist');
            });
        });

        // Clear All Logs button
        $(document).on('click', '#cyberarmor-clear-logs-btn, #spamarmor-clear-logs-btn', function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete all security log entries? This action cannot be undone.')) {
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.post(adminConfig.ajaxUrl, {
                action: 'cyberarmor_clear_logs',
                _ajax_nonce: adminConfig.nonce
            }, function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.data || 'Failed to clear logs.');
                    $btn.prop('disabled', false);
                }
            }).fail(function() {
                alert('Request failed');
                $btn.prop('disabled', false);
            });
        });
    });
})(jQuery);
