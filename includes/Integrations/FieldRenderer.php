<?php
namespace SpamArmor\Integrations;

use SpamArmor\Core\Config;
use SpamArmor\Core\TokenManager;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders hidden security fields (honeypot, timestamp token, PoW containers, behavioral markers)
 * in forms seamlessly.
 */
class FieldRenderer {
    /**
     * Generate HTML markup for hidden security fields.
     *
     * @param string $formType
     * @return string
     */
    public static function renderHiddenFields($formType = 'general') {
        $honeypotKey = TokenManager::getHoneypotFieldName();
        $timeToken = TokenManager::createTimeToken();
        $powChallenge = TokenManager::createPoWChallenge();

        ob_start();
        ?>
        <div class="spm-shield-container" aria-hidden="true" style="position: absolute !important; left: -9999px !important; top: -9999px !important; width: 1px !important; height: 1px !important; overflow: hidden !important; opacity: 0 !important; pointer-events: none !important;">
            <!-- Dynamic Salted Honeypot Field -->
            <label for="<?php echo esc_attr($honeypotKey); ?>">Do not fill this field</label>
            <input type="text" name="<?php echo esc_attr($honeypotKey); ?>" id="<?php echo esc_attr($honeypotKey); ?>" value="" tabindex="-1" autocomplete="new-password" />

            <!-- Secondary Decoy Honeypot -->
            <label for="spm_decoy_website">Website verification</label>
            <input type="text" name="spm_decoy_website" id="spm_decoy_website" value="" tabindex="-1" autocomplete="off" />

            <!-- HMAC Timestamp Token -->
            <input type="hidden" name="spm_time_token" class="spm-time-token" value="<?php echo esc_attr($timeToken); ?>" />

            <!-- Micro Proof-of-Work Challenge Token & Solution Nonce -->
            <input type="hidden" name="spm_pow_token" class="spm-pow-token" value="<?php echo esc_attr($powChallenge['token']); ?>" />
            <input type="hidden" name="spm_pow_nonce" class="spm-pow-nonce" value="" />

            <!-- Behavioral Entropy Payload -->
            <input type="hidden" name="spm_behavior" class="spm-behavior-payload" value="" />

            <!-- Form Context -->
            <input type="hidden" name="spm_form_type" value="<?php echo esc_attr($formType); ?>" />
        </div>
        <?php
        return ob_get_clean();
    }
}
