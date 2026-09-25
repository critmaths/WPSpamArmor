<div align="center">

# 🛡️ SpamArmor

### Modern, Autonomous, Privacy-First Spam & Bot Defense for WordPress

**Leave Akismet on the side of the road.**  
Zero subscriptions. Zero third-party cloud telemetry. 100% Free & Open Source.

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg?style=for-the-badge)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759b.svg?style=for-the-badge&logo=wordpress&logoColor=white)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Privacy](https://img.shields.io/badge/GDPR-100%25%20Local-success.svg?style=for-the-badge)](https://criticalmaths.synergize.co)
[![Zero Cloud](https://img.shields.io/badge/Cloud%20Dependencies-0%25-brightgreen.svg?style=for-the-badge)](https://criticalmaths.synergize.co)

[**Community & Docs**](https://criticalmaths.synergize.co) • [**GitHub Repository**](https://github.com/critmaths/WPSpamArmor) • [**Report an Issue**](https://github.com/critmaths/WPSpamArmor/issues)

---

</div>

## 📌 Table of Contents
- [Why SpamArmor?](#-why-spamarmor)
- [Akismet vs SpamArmor](#-akismet-vs-spamarmor)
- [How It Works (6-Layer Defense Pipeline)](#-how-it-works)
- [Supported Form Integrations](#-supported-form-integrations)
- [Cache-Proof Architecture](#-cache-proof-architecture)
- [Installation](#-installation)
- [Admin Tour & Features](#-admin-tour--features)
- [Developer Hooks & Custom Rules](#-developer-hooks--extensibility)
- [FAQ](#-frequently-asked-questions)
- [License & Community](#-community--license)

---

## 💡 Why SpamArmor?

For years, WordPress site owners were forced to choose between:
1. **Paying recurring monthly fees** to Akismet for commercial or even small-business sites, while transmitting all visitor comments, email addresses, and IPs to corporate cloud servers.
2. **Inflicting annoying CAPTCHAs** on visitors, forcing real humans to decipher blurry characters or click traffic lights.
3. **Enduring comment spam and contact form spam** that clutters databases and floods inboxes.

**SpamArmor solves this once and for all.** It stops spam bots and automated scrapers dead in their tracks using **defense-in-depth mathematical and behavioral verification directly on your WordPress server**. No API keys. No subscriptions. No cloud surveillance.

---

## ⚔️ Akismet vs. SpamArmor

| Feature | **Akismet** | **SpamArmor** |
| :--- | :---: | :---: |
| **Pricing** | Paid monthly fee for commercial sites | **100% Free & Open Source Forever** |
| **Data Privacy (GDPR)** | ⚠️ All submission data sent to 3rd party servers | **🔒 100% Local (Zero data leaves host)** |
| **API Key Required** | Mandatory account registration | **Zero setup (Works immediately)** |
| **Human Visitor Friction**| High false positives on legitimate users | **Zero Friction (No CAPTCHAs, no puzzles)** |
| **Headless Bot Protection**| Passive server filters | **⚡ Client-side Micro Proof-of-Work (PoW)** |
| **Speed Velocity Trap** | ❌ None | **⏱️ HMAC-signed Time-Gate (<3s detection)** |
| **Decoy Honeypots** | Basic static fields | **🍯 Dynamic Salted Obfuscated Honeypots** |
| **Caching Plugin Support**| Often breaks with full-page caches | **🔄 Asynchronous REST Token Hydration** |
| **Form Plugins Out-of-the-Box** | Requires paid add-ons or separate plugins | **🔌 CF7, WPForms, Gravity, Fluent, WooCommerce** |

---

## 🛡️ How It Works

SpamArmor filters every submission through an intelligent 6-layer defense pipeline:

```
                      [ Incoming Submission ]
                                 │
                                 ▼
       ┌───────────────────────────────────────────────────┐
       │ Layer 1: Dynamic Salted Honeypot                  │
       │ Obfuscated, decoy inputs invisible to real humans │
       └─────────────────────────┬─────────────────────────┘
                                 ▼
       ┌───────────────────────────────────────────────────┐
       │ Layer 2: Cryptographic HMAC Time-Gate             │
       │ Detects instant bot submissions (<3 seconds)      │
       └─────────────────────────┬─────────────────────────┘
                                 ▼
       ┌───────────────────────────────────────────────────┐
       │ Layer 3: Micro Proof-of-Work (PoW)                │
       │ 5-15ms client browser SHA-256 micro-puzzle        │
       └─────────────────────────┬─────────────────────────┘
                                 ▼
       ┌───────────────────────────────────────────────────┐
       │ Layer 4: Behavioral Interaction Entropy           │
       │ Measures genuine mouse, touch & typing presence   │
       └─────────────────────────┬─────────────────────────┘
                                 ▼
       ┌───────────────────────────────────────────────────┐
       │ Layer 5: Content Heuristics & Spam Scanner        │
       │ Analyzes link density, spam TLDs (.xyz), BBCode   │
       └─────────────────────────┬─────────────────────────┘
                                 ▼
       ┌───────────────────────────────────────────────────┐
       │ Layer 6: Network Rate Limiting & User-Agent Guard │
       │ Transient-based flood control per client IP       │
       └─────────────────────────┬─────────────────────────┘
                                 ▼
               [ Verdict: Allow, Spam Queue, or Block ]
```

### 1. Dynamic Salted Honeypot
Generates randomized, obfuscated decoy field names derived from your installation's cryptographic salt. Screen readers and humans never encounter them (`aria-hidden="true"`), but automated bots populate them automatically.

### 2. HMAC Time-Gate Velocity Check
Real humans take at least 3 to 10 seconds to read and write a response. Automated scrapers submit forms within milliseconds. SpamArmor signs the render timestamp with an HMAC token and flags sub-second bot requests as well as stale replay attacks.

### 3. Micro Proof-of-Work (PoW) — *The Bot Killer*
Instead of torturing human visitors with CAPTCHA puzzles, SpamArmor issues a lightweight cryptographic micro-challenge that the user's browser solves in 5–15 milliseconds using native Web Crypto (`crypto.subtle`). While invisible to a single human, this makes mass-submission botnets computationally prohibitive.

### 4. Behavioral Interaction Entropy
Tracks non-invasive interaction markers (mouse movement, scroll velocity, touch gestures, focus shifts) without recording keystroke content or collecting any personally identifiable information (PII).

### 5. Content Heuristic Engine
Inspects text content for excessive URL density, high-risk spam top-level domains (`.xyz`, `.top`, `.click`, `.loan`, `.stream`, `.win`), BBCode/HTML injections (`[url=...]`), and bad word spam patterns.

### 6. Network Rate Limiting
Uses native WordPress transients to enforce configurable flood protection per IP address, stopping rapid-fire brute-force campaigns in their tracks.

---

## 🔌 Supported Form Integrations

SpamArmor automatically detects and guards your forms with **zero configuration required**:

* **WordPress Core Comments** — Replaces Akismet on all post and page comments.
* **WordPress User Registration** — Prevents fake bot account registration waves.
* **WordPress Login** — Defends against automated brute-force login attempts.
* **Contact Form 7** — Seamless protection via native CF7 spam filters.
* **WPForms (Lite & Pro)** — Guards all contact, feedback, and custom forms.
* **Gravity Forms** — Full validation hook integration.
* **Fluent Forms** — Native action validation adapter.
* **WooCommerce** — Protects checkout registration and customer product reviews.

---

## 🔄 Cache-Proof Architecture

Traditional anti-spam plugins fail when used alongside full-page caching plugins (**WP Rocket, LiteSpeed Cache, Cloudflare, Nginx, W3 Total Cache**) because cached HTML pages serve stale security nonces.

SpamArmor features **asynchronous REST API token hydration** (`/wp-json/spamarmor/v1/token`):
- When a page is loaded from static HTML cache, lightweight background JavaScript fetches a fresh timestamp token and micro-challenge.
- Legitimate visitors are never blocked due to cache expiration.
- Fast, non-blocking, and 100% cache-plugin compatible.

---

## 📦 Installation

### Method 1: Upload ZIP in WordPress Admin *(Recommended)*
1. Download [`spamarmor.zip`](spamarmor.zip) from this repository.
2. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Select `spamarmor.zip` and click **Install Now**.
4. Click **Activate Plugin**.
5. Deactivate Akismet and enjoy clean, zero-friction protection!

### Method 2: Git Clone
```bash
cd wp-content/plugins/
git clone https://github.com/critmaths/WPSpamArmor.git spamarmor
```
Then activate **SpamArmor** from the WordPress **Plugins** screen.

### Method 3: WP-CLI
```bash
wp plugin install https://github.com/critmaths/WPSpamArmor/archive/refs/heads/main.zip --activate
```

---

## 🖥️ Admin Tour & Features

Once activated, navigate to **SpamArmor** in your WordPress admin sidebar:

* **Real-Time Analytics Dashboard:**
  - Total Spam Blocked, Clean Traffic Counter, and Spam Block Rate percentage.
  - Interactive 7-Day Activity Trend Bar Chart.
  - Live status indicators for all 6 defense layers.
* **Spam Event Audit Log:**
  - Inspect blocked attack attempts with exact timestamps, form sources, IP addresses, calculated risk scores, and triggered rules.
  - One-click **Quick Whitelist** button to unblock any false positives instantly.
* **Granular Settings:**
  - Choose your spam penalty: **Block Immediately (403)**, **Hold in Spam Queue**, or **Silent Trash**.
  - Adjust velocity thresholds (default: 3 seconds) and maximum allowed links.
  - Manage IP whitelists and role-based exemptions (exempt Admins and Editors automatically).

---

## 🛠️ Developer Hooks & Extensibility

SpamArmor is modular and developer-friendly.

### Add a Custom Protection Rule
```php
add_action('plugins_loaded', function() {
    class MyCustomRule implements \SpamArmor\Protection\RuleInterface {
        public function getId() { return 'my_rule'; }
        public function getName() { return 'Custom Business Filter'; }
        public function isEnabled() { return true; }
        public function check(array $context) {
            // $context contains: 'post', 'form_type', 'ip', 'user_agent', 'content'
            if (strpos($context['content'], 'forbidden-phrase') !== false) {
                return [
                    'passed'   => false,
                    'score'    => 100,
                    'critical' => true,
                    'reason'   => 'Triggered custom business blacklist.'
                ];
            }
            return ['passed' => true, 'score' => 0, 'critical' => false, 'reason' => ''];
        }
    }

    \SpamArmor\Core\Plugin::instance()
        ->getProtectionEngine()
        ->registerRule(new MyCustomRule());
});
```

### Action Hooks
* `spamarmor_before_evaluate`: Fires before submission inspection.
* `spamarmor_spam_detected`: Fires when spam is flagged (`$context`, `$score`, `$reasons`).
* `spamarmor_clean_submission`: Fires when a clean submission is approved.

---

## ❓ Frequently Asked Questions

<details>
<summary><strong>Does SpamArmor require an API key or account?</strong></summary>
No. SpamArmor is 100% self-contained. It operates completely on your WordPress server and requires zero API keys or account registrations.
</details>

<details>
<summary><strong>Is SpamArmor GDPR compliant?</strong></summary>
Yes, 100%. No visitor data, comments, emails, or IPs are ever transmitted to third-party cloud servers. SpamArmor also includes built-in IP anonymization to mask the last octet of IP addresses in audit logs.
</details>

<details>
<summary><strong>Will this slow down my website?</strong></summary>
Not at all. The frontend JavaScript is less than 4KB with zero external dependencies (no jQuery required). The Proof-of-Work challenge executes in 5–15 milliseconds on modern devices and is completely imperceptible to users.
</details>

<details>
<summary><strong>What happens if a bot bypasses JavaScript?</strong></summary>
If a bot disables JavaScript or submits via cURL/Python, it lacks the Micro-PoW solution, the HMAC timestamp token, and the dynamic honeypot verification, triggering an immediate block on Layers 1, 2, and 3.
</details>

---

## 🌐 Community & License

* **Project Community:** [https://criticalmaths.synergize.co](https://criticalmaths.synergize.co)
* **GitHub Repository:** [https://github.com/critmaths/WPSpamArmor](https://github.com/critmaths/WPSpamArmor)
* **Issues & Feature Requests:** [GitHub Issues](https://github.com/critmaths/WPSpamArmor/issues)

**SpamArmor** is free software licensed under the [GNU General Public License v2.0 or later](LICENSE).

---

<div align="center">
  <sub>Built with ❤️ by the <b>SpamArmor Community</b>. Say goodbye to spam and subscription fees forever.</sub>
</div>
