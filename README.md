# 🛡️ CyberArmor - Open Source Security, Firewall & Anti-Spam for WordPress

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](LICENSE)
[![Sponsor](https://img.shields.io/badge/Sponsor-%E2%9D%A4-ea4aaa?style=flat-square&logo=github)](https://github.com/sponsors/critmaths)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759b.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg)](https://php.net)
[![Zero Cloud](https://img.shields.io/badge/Cloud%20Dependencies-0%25%20(100%25%20Local)-brightgreen.svg)]()
[![Privacy](https://img.shields.io/badge/GDPR-100%25%20Compliant-success.svg)]()

> **Enterprise-grade, privacy-first open-source security suite built for modern WordPress websites.**  
> Stop SQL injection, directory traversal, automated bots, and comment/form spam locally without external subscriptions, API keys, or cloud surveillance.

---

## 🚀 Why CyberArmor?

| Feature | **Traditional Cloud Security & Anti-Spam** | **CyberArmor** |
| :--- | :---: | :---: |
| **Pricing / License** | Monthly subscriptions / paywalls | **100% Free & Open Source (GPLv2+)** |
| **Data Privacy (GDPR)** | ⚠️ Transmits visitor data to 3rd party cloud | **100% Local Processing (Zero data leaves host)** |
| **Setup & API Keys** | Requires account registration & keys | **Zero setup required (Works instantly)** |
| **Web Application Firewall** | Often cloud proxy or heavy SaaS | **Lightweight Layer-7 WAF on your server** |
| **Core & Server Hardening** | Needs separate hardening plugins | **Built-in Security Headers & Obfuscation** |
| **Client Friction** | Distorted CAPTCHAs & false blocks | **Zero Friction (No annoying CAPTCHAs)** |
| **Headless Bot Defense** | Basic server filters | **Micro Proof-of-Work (PoW) in browser** |
| **Speed Velocity Gate** | No | **HMAC-signed Time-Gate (<3s detection)** |
| **Decoy Honeypots** | Basic | **Dynamic Salted Obfuscated Honeypots** |
| **Full-Page Cache Compatible**| Frequently breaks nonces | **Cache-Proof REST Token Hydration** |
| **Form Plugin Support** | Requires extra add-on purchases | **Universal out-of-the-box support** |

---

## ⚡ Multi-Tier Defense-in-Depth Architecture

CyberArmor safeguards your site through coordinated defensive tiers:

```
[ Incoming HTTP Request ]
         │
         ▼
┌───────────────────────────────────────────────┐
│ 1. Layer-7 Web Application Firewall (WAF)     │  Blocks SQLi, Traversal, Code Injection, Scanners
└──────────────────────┬────────────────────────┘
                       ▼
┌───────────────────────────────────────────────┐
│ 2. Core & Server Hardening Engine             │  Security Headers, XML-RPC Shield, Version Masking
└──────────────────────┬────────────────────────┘
                       ▼
┌───────────────────────────────────────────────┐
│ 3. Form & Bot Submission Defense              │  Honeypots, HMAC Time-Gate, Micro-PoW, Heuristics
└──────────────────────┬────────────────────────┘
                       ▼
            [ Verdict: Allow or Block ]
```

### 🧱 1. Web Application Firewall (WAF)
* **Directory Traversal Defense:** Drops attempts scanning `../`, `/etc/passwd`, `boot.ini`, `.env`, and sensitive files.
* **SQL Injection (SQLi) Filter:** Identifies and terminates queries containing `UNION SELECT`, `CONCAT`, or `information_schema` exploitation vectors.
* **Code Injection & XSS Guard:** Neutralizes payloads containing `eval()`, `base64_decode()`, system execution, or `<script>` tags.
* **Automated Scanner Defense:** Blocks vulnerability scanners by User-Agent signature (`sqlmap`, `nikto`, `wpscan`, `gobuster`, `dirbuster`, `acunetix`).
* **Unsafe Request Methods:** Blocks dangerous HTTP verbs (`TRACE`, `TRACK`, `DEBUG`, `CONNECT`).

### 🔒 2. Core & Server Hardening
* **HTTP Security Headers:** Injects `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, and `Permissions-Policy`.
* **XML-RPC & Pingback Shield:** Disables XML-RPC and strips pingback headers to close common DDoS and amplification vectors.
* **Information Masking:** Removes `<meta name="generator" content="WordPress ...">` and strips `?ver=` query strings from static assets for public visitors.
* **User Enumeration Defense:** Redirects `/?author=1` scans and restricts `/wp-json/wp/v2/users` to authenticated users.

### 🛡️ 3. Bot & Anti-Spam Defense
* **Dynamic Salted Honeypot:** Deploys randomized, deceptive hidden fields that screen-readers ignore (`aria-hidden="true"`) but automated bots automatically populate.
* **HMAC Time-Gate:** Enforces human composition velocity (default 3 seconds) using tamper-proof cryptographic timestamps.
* **Micro Proof-of-Work (PoW):** Lightweight client-side SHA-256 micro-puzzle solved in JavaScript (5-15ms). Imperceptible to humans, but makes bulk bot scripts computationally prohibitive.
* **Behavioral Interaction Entropy:** Verifies non-intrusive human interaction markers (mouse movements, keystroke cadence, touch) with zero tracking or keylogging.
* **Content Heuristic Engine:** Analyzes link density, high-risk spam TLDs (`.top`, `.xyz`, `.click`, `.loan`), BBCode tags, and user custom keyword lists.
* **Network Rate Limiter:** Transient-based flood mitigation preventing rapid-fire submission spikes per IP.

---

## 🔌 Universal Form Integrations

CyberArmor automatically detects and guards:
- **WordPress Native Comments**
- **WordPress User Registration**
- **WordPress Login Form**
- **Contact Form 7**
- **WPForms (Lite & Pro)**
- **Gravity Forms**
- **Fluent Forms**
- **WooCommerce (Customer Registration & Product Reviews)**

---

## 🚀 Installation

1. Clone or download this repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/critmaths/cyberarmor.git
   ```
2. Activate **CyberArmor** via the WordPress **Plugins** screen.
3. Protection is active immediately with production-ready security defaults.
4. Visit **CyberArmor > Settings** in your WordPress admin menu to review or customize your firewall, hardening, and spam options.

---

## 🛠️ Developer Hooks & Extensibility

CyberArmor is built from the ground up to be developer-friendly.

### Custom Protection Rule
```php
add_action('plugins_loaded', function() {
    class MyCustomRule implements \CyberArmor\Protection\RuleInterface {
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
    \CyberArmor\Core\Plugin::instance()->getProtectionEngine()->registerRule(new MyCustomRule());
});
```

### Action Hooks
- `cyberarmor_firewall_blocked`: Fires when an exploit attempt is intercepted by the WAF.
- `cyberarmor_before_evaluate`: Fires before form submission evaluation.
- `cyberarmor_spam_detected`: Fires when a submission is flagged as spam.
- `cyberarmor_clean_submission`: Fires when a submission passes all defense layers.

---

## 📄 License
 
CyberArmor is open-source software licensed under the [GNU General Public License v2.0 or later](LICENSE).

---

## 💖 Sponsoring & Support

If CyberArmor protects your WordPress websites from malicious traffic, please consider supporting continued open-source development:

👉 **[Become a GitHub Sponsor](https://github.com/sponsors/critmaths)**
