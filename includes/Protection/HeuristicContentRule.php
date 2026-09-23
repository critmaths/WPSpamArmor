<?php
namespace SpamArmor\Protection;

use SpamArmor\Core\Config;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Heuristic content analysis rule.
 * Analyzes submission text for high-risk spam indicators:
 * link counts, high-risk TLDs, BBCode patterns, scam keywords, and character entropy.
 */
class HeuristicContentRule implements RuleInterface {
    /**
     * Built-in spammy top-level domains frequently abused by automated link spammers.
     */
    const SPAMMY_TLDS = [
        'top', 'xyz', 'click', 'loan', 'stream', 'bid', 'country', 'gq',
        'cf', 'tk', 'ml', 'ga', 'work', 'racing', 'download', 'accountant',
        'faith', 'date', 'review', 'trade', 'webcam', 'win', 'cricket', 'icu'
    ];

    /**
     * Common spam vector keywords.
     */
    const SPAM_KEYWORDS = [
        'viagra', 'cialis', 'levitra', 'phentermine', 'xanax',
        'casino', 'poker online', 'slot gacor', 'slot online', 'roulette',
        'crypto investment', 'whatsapp bitcoin', 'crypto doubling',
        'buy backlinks', 'seo service cheap', 'guest post outreach',
        'essay writing service', 'replica rolex', 'replica watches',
        'porn', 'adult webcam', 'dating scam'
    ];

    public function getId() {
        return 'heuristics';
    }

    public function getName() {
        return 'Content Heuristics & Link Scanner';
    }

    public function isEnabled() {
        return (bool)Config::get('enable_heuristics', true);
    }

    public function check(array $context) {
        $content = (string)($context['content'] ?? '');
        if (trim($content) === '') {
            return [
                'passed'   => true,
                'score'    => 0,
                'critical' => false,
                'reason'   => ''
            ];
        }

        $score = 0;
        $reasons = [];

        // 1. Link density & count inspection
        $urlCount = preg_match_all('#https?://[^\s<>"\'()]+#i', $content, $matches);
        $maxLinks = (int)Config::get('max_allowed_links', 2);

        if ($urlCount > $maxLinks) {
            $excessLinks = $urlCount - $maxLinks;
            $linkScore = min(50, $excessLinks * 20);
            $score += $linkScore;
            $reasons[] = sprintf(__('Excessive links (%d URLs found, limit is %d).', 'spamarmor'), $urlCount, $maxLinks);
        }

        // 2. High-risk TLD check
        if (Config::get('block_spammy_tlds', true) && !empty($matches[0])) {
            $foundSpamTld = false;
            foreach ($matches[0] as $url) {
                $host = parse_url($url, PHP_URL_HOST);
                if ($host) {
                    $parts = explode('.', strtolower($host));
                    $tld = end($parts);
                    if (in_array($tld, self::SPAMMY_TLDS, true)) {
                        $foundSpamTld = true;
                        $score += 45;
                        $reasons[] = sprintf(__('High-risk spam TLD detected (.%s).', 'spamarmor'), $tld);
                        break;
                    }
                }
            }
        }

        // 3. BBCode / Markdown spam patterns (e.g. [url=http...])
        if (Config::get('block_bbcode', true)) {
            if (preg_match('#\[url[=\s\]]#i', $content) || preg_match('#<a\s+href=#i', $content)) {
                $score += 35;
                $reasons[] = __('BBCode / HTML anchor tags injected into content.', 'spamarmor');
            }
        }

        // 4. Keyword heuristic scoring
        $lowerContent = strtolower($content);
        $matchedWords = [];

        // Built-in spam vectors
        foreach (self::SPAM_KEYWORDS as $keyword) {
            if (strpos($lowerContent, $keyword) !== false) {
                $matchedWords[] = $keyword;
            }
        }

        // User custom bad words
        $customWords = Config::get('custom_bad_words', '');
        if (!empty($customWords)) {
            $list = array_filter(array_map('trim', explode(',', strtolower($customWords))));
            foreach ($list as $customWord) {
                if ($customWord !== '' && strpos($lowerContent, $customWord) !== false) {
                    $matchedWords[] = $customWord;
                }
            }
        }

        if (!empty($matchedWords)) {
            $wordPenalty = min(60, count($matchedWords) * 30);
            $score += $wordPenalty;
            $reasons[] = sprintf(
                __('Spam keywords detected: %s', 'spamarmor'),
                implode(', ', array_slice($matchedWords, 0, 3))
            );
        }

        // 5. Repetitive characters & gibberish runs (e.g. "aaaaaaa", "asdfghjkl")
        if (preg_match('/(.)\1{7,}/', $content)) {
            $score += 30;
            $reasons[] = __('Excessive character repetition detected.', 'spamarmor');
        }

        $threshold = (int)Config::get('spam_score_threshold', 50);
        $passed = ($score < $threshold);

        return [
            'passed'   => $passed,
            'score'    => min(100, $score),
            'critical' => ($score >= 80),
            'reason'   => implode(' | ', $reasons)
        ];
    }
}
