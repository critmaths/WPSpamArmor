<?php
namespace CyberArmor\Integrations;

use CyberArmor\Core\Config;
use CyberArmor\Core\TokenManager;

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
        <div class="cyberarmor-shield-container spamarmor-shield-container" aria-hidden="true" style="position: absolute !important; left: -9999px !important; top: -9999px !important; width: 1px !important; height: 1px !important; overflow: hidden !important; opacity: 0 !important; pointer-events: none !important;">
            <!-- Dynamic Salted Honeypot Field -->
            <label for="<?php echo esc_attr($honeypotKey); ?>">Do not fill this field</label>
            <input type="text" name="<?php echo esc_attr($honeypotKey); ?>" id="<?php echo esc_attr($honeypotKey); ?>" value="" tabindex="-1" autocomplete="new-password" />

            <!-- Secondary Decoy Honeypot -->
            <label for="cyberarmor_decoy_website">Website verification</label>
            <input type="text" name="cyberarmor_decoy_website" id="cyberarmor_decoy_website" value="" tabindex="-1" autocomplete="off" />

            <!-- HMAC Timestamp Token -->
            <input type="hidden" name="cyberarmor_time_token" class="cyberarmor-time-token spamarmor-time-token" value="<?php echo esc_attr($timeToken); ?>" />

            <!-- Micro Proof-of-Work Challenge Token & Solution Nonce -->
            <input type="hidden" name="cyberarmor_pow_token" class="cyberarmor-pow-token spamarmor-pow-token" value="<?php echo esc_attr($powChallenge['token']); ?>" />
            <input type="hidden" name="cyberarmor_pow_nonce" class="cyberarmor-pow-nonce spamarmor-pow-nonce" value="" />

            <!-- Behavioral Entropy Payload -->
            <input type="hidden" name="cyberarmor_behavior" class="cyberarmor-behavior-payload spamarmor-behavior-payload" value="" />

            <!-- Form Context -->
            <input type="hidden" name="cyberarmor_form_type" value="<?php echo esc_attr($formType); ?>" />
        </div>
        <?php
        return ob_get_clean();
    }
}
