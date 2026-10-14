=== CyberArmor - Open Source Security, Firewall & Anti-Spam ===
Contributors: critmaths
Tags: security, firewall, anti-spam, bot protection, honeypot
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Next-generation open-source security, Layer-7 Web Application Firewall (WAF), core hardening, and bot/spam defense for WordPress. Zero external subscriptions, zero cloud telemetry.

== Description ==

**CyberArmor** is a modern, privacy-focused open-source security suite and bot defense system for WordPress. It delivers defense-in-depth site protection directly on your WordPress server with **zero cloud dependencies, zero external subscriptions, and complete GDPR compliance**.

Unlike commercial services that transmit your visitors' comments, email addresses, and IP records to external cloud servers, CyberArmor processes all firewall heuristics, site hardening, and spam filters locally.

### 🛡️ CyberArmor Security Modules

* **🧱 Layer-7 Web Application Firewall (WAF):** Early request inspection blocking directory traversal (`../`, `/etc/passwd`), SQL injection probes (`UNION SELECT`, `CONCAT`), remote code execution (`eval`, `base64_decode`, XSS), automated vulnerability scanners (`sqlmap`, `nikto`, `wpscan`, `gobuster`), and unsafe HTTP methods.
* **🔒 Core & Server Hardening:** Injects industry-standard HTTP security headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`), hides WordPress version generator tags, strips asset version query strings from static assets, blocks author enumeration (`/?author=1`), and restricts the REST API user directory.
* **⚡ XML-RPC & Pingback Shield:** One-click disable for XML-RPC and pingback headers, eliminating major DDoS and brute-force amplification vectors.
* **🤖 Micro Proof-of-Work (PoW):** An ultra-fast (5-15ms) client-side cryptographic challenge that neutralizes headless botnets and automated scrapers without annoying human visitors.
* **🍯 Dynamic Salted Honeypot:** Randomized, deceptive decoy fields that bots automatically fill.
* **⏱️ HMAC Time-Gate Velocity:** Detects automated submissions occurring faster than human reading speed (<3 seconds).
* **🧠 Content Heuristic Engine:** Analyzes excessive URLs, high-risk spam TLDs (.xyz, .top, .click, etc.), BBCode tags, and bad word vectors.
* **🚀 Full-Page Cache Compatible:** Built-in REST API token hydration (`/wp-json/cyberarmor/v1/token`) prevents stale nonce issues with WP Rocket, LiteSpeed Cache, Cloudflare, and W3 Total Cache.
* **🔌 Universal Form Integrations:** Out-of-the-box protection for WordPress Comments, User Registrations, Logins, Contact Form 7, WPForms, Gravity Forms, Fluent Forms, and WooCommerce.

### ⚡ Supported Integrations
* **WordPress Core Comments**
* **WordPress User Registration & Login**
* **Contact Form 7**
* **WPForms (Lite & Pro)**
* **Gravity Forms**
* **Fluent Forms**
* **WooCommerce (Reviews & Customer Registration)**

== Installation ==

1. Upload the `cyberarmor` directory to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **CyberArmor > Settings** in your WordPress admin menu to review or customize your protection rules.
4. That's it! Protection is active immediately with zero API keys required.

== Frequently Asked Questions ==

= Does CyberArmor require an API key or subscription? =
No! CyberArmor is 100% free and open-source under the GPLv2 license. It runs entirely on your own server with zero external subscriptions, account creation, or cloud telemetry.

= How does CyberArmor protect against bots without CAPTCHAs? =
CyberArmor combines coordinated defense layers: dynamic honeypots, HMAC time-gate verification, micro Proof-of-Work client puzzles, human interaction behavioral entropy, content link heuristics, and network rate limiting.

= What does the Web Application Firewall (WAF) do? =
The WAF inspects incoming HTTP requests at request initialization. It identifies and drops common exploit patterns including SQL injection, directory traversal, code execution attempts, scanner user agents, and unauthorized methods before database execution.

= Is CyberArmor compliant with GDPR? =
Yes! CyberArmor does not send any visitor data to external servers. It also features optional IP masking (anonymizing the last octet) for complete privacy compliance.

= Does it work with caching plugins? =
Yes! CyberArmor uses an asynchronous REST API token endpoint (`/wp-json/cyberarmor/v1/token`) to provide fresh security tokens, so full-page caching from WP Rocket, LiteSpeed, or Cloudflare never breaks protection.

== Changelog ==

= 1.1.0 =
* Rebranded to CyberArmor.
* Added Layer-7 Web Application Firewall (WAF) with SQLi, directory traversal, and scanner defense.
* Added Core & Server Hardening with HTTP security headers, author enumeration defense, XML-RPC shield, and REST user directory restriction.
* Upgraded admin interface with WAF exploit metrics and combined event logs.

= 1.0.0 =
* Initial public open source release.
* Multi-layer defense engine: Honeypot, Time-gate, Micro-PoW, Behavioral entropy, Heuristics, and Rate Limiting.
* Form integrations for Comments, Registration, Login, Contact Form 7, WPForms, Gravity Forms, Fluent Forms, and WooCommerce.
* Administrative dashboard with 7-day trend chart and security event audit log.
