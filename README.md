# 🛡️ SpamArmor - Modern Open Source Spam & Bot Protection for WordPress

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759b.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg)](https://php.net)
[![Zero Cloud](https://img.shields.io/badge/Cloud%20Dependencies-0%25%20(100%25%20Local)-brightgreen.svg)]()
[![Privacy](https://img.shields.io/badge/GDPR-100%25%20Compliant-success.svg)]()

> **Leave Akismet on the side of the road.**  
> SpamArmor is an autonomous, privacy-first, zero-subscription open-source spam and bot defense system built for modern WordPress websites.

---

## 🚀 Why SpamArmor?

| Feature | **Akismet** | **SpamArmor** |
| :--- | :---: | :---: |
| **Pricing / License** | Paid subscription for commercial sites | **100% Free & Open Source (GPLv2+)** |
| **Data Privacy (GDPR)** | ⚠️ Transmits all visitor data to 3rd party cloud | **100% Local Processing (Zero data leaves host)** |
| **API Key Required** | Yes (Automattic account registration) | **No (Zero setup required)** |
| **Client Friction** | None / Sometimes false blocks | **Zero Friction (No annoying CAPTCHAs)** |
| **Headless Bot Defense** | Basic server filters | **Micro Proof-of-Work (PoW) in browser** |
| **Speed Velocity Gate** | No | **HMAC-signed Time-Gate (<3s detection)** |
| **Decoy Honeypots** | Basic | **Dynamic Salted Obfuscated Honeypots** |
| **Full-Page Cache Compatible**| Varies | **Cache-Proof REST Token Hydration** |
| **Form Plugin Support** | Requires extra add-ons | **Universal out-of-the-box support** |

---

## ⚡ 6-Layer Defense-in-Depth Architecture

SpamArmor evaluates submissions through a coordinated defense pipeline:

```
[ Incoming Submission ]
         │
         ▼
┌───────────────────────────────────────────────┐
│ 1. Dynamic Salted Honeypot                    │  Instant bot trap: fills hidden decoy fields
└──────────────────────┬────────────────────────┘
                       ▼
┌───────────────────────────────────────────────┐
│ 2. Cryptographic HMAC Time-Gate               │  Flags sub-second bot clicks & expired replays
└──────────────────────┬────────────────────────┘
                       ▼
┌───────────────────────────────────────────────┐
│ 3. Micro Proof-of-Work (PoW)                  │  Client solves micro-hash puzzle in 5-15ms
└──────────────────────┬────────────────────────┘
                       ▼
┌───────────────────────────────────────────────┐
│ 4. Behavioral Interaction Entropy             │  Verifies mouse gestures, touch & keystroke dynamics
└──────────────────────┬────────────────────────┘
                       ▼
┌───────────────────────────────────────────────┐
│ 5. Content & Link Heuristics                  │  Scans link density, spam TLDs (.xyz, .top), BBCode
└──────────────────────┬────────────────────────┘
                       ▼
┌───────────────────────────────────────────────┐
│ 6. Network Rate Limiting & User-Agent Guard   │  Transient-based flood protection per IP
└──────────────────────┬────────────────────────┘
                       ▼
            [ Verdict: Allow or Block ]
```

1. **Dynamic Salted Honeypot**: Generates randomized, deceptive field names based on installation salt that screen-readers ignore (`aria-hidden="true"`) but automated crawlers greedily populate.
2. **HMAC Time-Gate**: Enforces a minimum human composition duration (default 3 seconds) using tamper-proof cryptographic timestamps.
3. **Micro Proof-of-Work (PoW)**: A lightweight client-side SHA-256 micro-puzzle solved in JavaScript. Completely imperceptible to humans, but makes bulk automated spamming computationally prohibitive.
4. **Behavioral Interaction Entropy**: Collects non-intrusive presence markers (mouse movement, scroll, touch, focus) with zero PII or keylogging.
5. **Content Heuristic Engine**: Scans for high-risk TLDs (`.top`, `.xyz`, `.click`, `.loan`), excessive URLs, BBCode anchor tags, and known scam vectors.
6. **Network Rate Limiter**: Transient-based flood mitigation preventing rapid-fire submission spikes.

---

## 🔌 Universal Form Integrations

SpamArmor automatically detects and guards:
- **WordPress Native Comments**
- **WordPress User Registration**
- **WordPress Login Form (Brute-force protection)**
- **Contact Form 7**
- **WPForms (Lite & Pro)**
- **Gravity Forms**
- **Fluent Forms**
- **WooCommerce (Customer Registration & Product Reviews)**

---

## 🚀 Installation

1. Clone or download this repository into your WordPress plugin directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/spamarmor/spamarmor.git
   ```
2. Activate **SpamArmor** via the WordPress **Plugins** screen.
3. Done! Protection is active immediately with recommended security defaults.

---

## 🛠️ Developer Hooks & Extensibility

SpamArmor is built from the ground up to be developer-friendly.

### Custom Protection Rule
```php
add_action('plugins_loaded', function() {
    class MyCustomRule implements \SpamArmor\Protection\RuleInterface {
        public function getId() { return 'custom_rule'; }
        public function getName() { return 'My Custom Filter'; }
        public function isEnabled() { return true; }
        public function check(array $context) {
            // Your custom inspection logic
            return [
                'passed'   => true,
                'score'    => 0,
                'critical' => false,
                'reason'   => ''
            ];
        }
    }
    \SpamArmor\Core\Plugin::instance()->getProtectionEngine()->registerRule(new MyCustomRule());
});
```

### Action Hooks
- `spamarmor_before_evaluate`: Fires before submission evaluation.
- `spamarmor_spam_detected`: Fires when a submission is flagged as spam.
- `spamarmor_clean_submission`: Fires when a submission passes all defense layers.

---

## 📄 License

SpamArmor is open-source software licensed under the [GNU General Public License v2.0 or later](LICENSE).
