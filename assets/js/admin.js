/**
 * SpamArmor Admin JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Quick Whitelist IP button
        $('.spamarmor-quick-whitelist').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var ip = $btn.data('ip');

            if (!ip) return;

            $btn.prop('disabled', true).text('Adding...');

            $.post(spamarmorAdmin.ajaxUrl, {
                action: 'spamarmor_whitelist_ip',
                ip: ip,
                _ajax_nonce: spamarmorAdmin.nonce
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
        $('#spamarmor-clear-logs-btn').on('click', function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete all spam log entries? This action cannot be undone.')) {
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.post(spamarmorAdmin.ajaxUrl, {
                action: 'spamarmor_clear_logs',
                _ajax_nonce: spamarmorAdmin.nonce
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
