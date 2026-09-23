/**
 * SpamArmor - Client-Side Micro-PoW Solver & Behavioral Entropy Collector
 * Ultra-lightweight vanilla JS (zero dependencies)
 */
(function() {
    'use strict';

    var entropy = {
        m: 0, // mouse movements
        k: 0, // keystrokes
        t: 0, // touch events
        f: 0, // focus events
        p: false // paste occurred
    };

    var solvedCache = {};

    // 1. Behavioral interaction listeners
    var recordMouseMove = throttle(function() { entropy.m++; updateBehaviorPayloads(); }, 200);
    var recordTouch = throttle(function() { entropy.t++; updateBehaviorPayloads(); }, 200);

    window.addEventListener('mousemove', recordMouseMove, { passive: true });
    window.addEventListener('touchstart', recordTouch, { passive: true });
    window.addEventListener('scroll', throttle(function() { entropy.f++; }, 500), { passive: true });

    window.addEventListener('keydown', function() {
        entropy.k++;
        updateBehaviorPayloads();
    }, { passive: true });

    window.addEventListener('paste', function() {
        entropy.p = true;
        updateBehaviorPayloads();
    }, { passive: true });

    function updateBehaviorPayloads() {
        var encoded = btoa(JSON.stringify(entropy));
        var inputs = document.querySelectorAll('.spm-behavior-payload');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].value = encoded;
        }
    }

    // 2. Micro Proof-of-Work SHA-256 solver
    async function solvePoW(challenge, difficulty) {
        var prefix = '0'.repeat(difficulty);
        var nonce = 0;
        var encoder = new TextEncoder();

        while (nonce < 200000) {
            var nonceStr = nonce.toString();
            var data = encoder.encode(challenge + nonceStr);
            var hashBuffer = await crypto.subtle.digest('SHA-256', data);
            var hashArray = Array.from(new Uint8Array(hashBuffer));
            var hashHex = hashArray.map(function(b) { return b.toString(16).padStart(2, '0'); }).join('');

            if (hashHex.substring(0, difficulty) === prefix) {
                return nonceStr;
            }
            nonce++;
        }
        return nonce.toString();
    }

    // 3. Process all protected forms on the page
    async function processContainers() {
        var containers = document.querySelectorAll('.spm-shield-container');
        if (!containers.length) return;

        updateBehaviorPayloads();

        for (var i = 0; i < containers.length; i++) {
            var container = containers[i];
            var tokenInput = container.querySelector('.spm-pow-token');
            var nonceInput = container.querySelector('.spm-pow-nonce');

            if (!tokenInput || !nonceInput || nonceInput.value) continue;

            var rawToken = tokenInput.value;
            if (!rawToken) continue;

            if (solvedCache[rawToken]) {
                nonceInput.value = solvedCache[rawToken];
                continue;
            }

            try {
                var decoded = atob(rawToken);
                var parts = decoded.split(':');
                if (parts.length >= 3) {
                    var challenge = parts[0];
                    var difficulty = parseInt(parts[1], 10) || 3;
                    var nonce = await solvePoW(challenge, difficulty);
                    nonceInput.value = nonce;
                    solvedCache[rawToken] = nonce;
                }
            } catch (e) {
                // Silently fallback if subtle crypto fails
            }
        }
    }

    // 4. Cache-Busting Token Hydration via REST API
    async function hydrateCachedTokens() {
        if (!window.spamarmorSettings || !window.spamarmorSettings.restUrl) return;

        // If the page was served from static cache, fetch fresh tokens
        try {
            var response = await fetch(window.spamarmorSettings.restUrl, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            if (response.ok) {
                var data = await response.json();
                if (data && data.success) {
                    var timeInputs = document.querySelectorAll('.spm-time-token');
                    var powInputs = document.querySelectorAll('.spm-pow-token');

                    for (var i = 0; i < timeInputs.length; i++) {
                        timeInputs[i].value = data.timeToken;
                    }
                    for (var j = 0; j < powInputs.length; j++) {
                        powInputs[j].value = data.pow.token;
                    }
                    // Re-solve with new challenge
                    await processContainers();
                }
            }
        } catch (err) {
            // Ignore offline or fetch errors
        }
    }

    // Utility: throttle
    function throttle(func, limit) {
        var inThrottle;
        return function() {
            var args = arguments;
            var context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(function() { inThrottle = false; }, limit);
            }
        };
    }

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            processContainers();
            hydrateCachedTokens();
        });
    } else {
        processContainers();
        hydrateCachedTokens();
    }
})();
