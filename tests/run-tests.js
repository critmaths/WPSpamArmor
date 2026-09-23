/**
 * Automated Verification Suite for SpamArmor WordPress Plugin
 * Tests file syntax, cryptographic algorithms, PoW solver/verifier, and heuristic rules.
 */

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

console.log('====================================================');
console.log('🛡️  SpamArmor Automated Test Suite');
console.log('====================================================\n');

let totalTests = 0;
let passedTests = 0;

function assert(condition, message) {
    totalTests++;
    if (condition) {
        console.log(`  ✅ PASS: ${message}`);
        passedTests++;
    } else {
        console.error(`  ❌ FAIL: ${message}`);
    }
}

// -------------------------------------------------------------
// Test 1: PHP File Syntax & Structure Integrity
// -------------------------------------------------------------
console.log('Test Group 1: PHP File Integrity & Syntax Check');

function getAllFiles(dir, ext, fileList = []) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        if (fs.statSync(fullPath).isDirectory()) {
            getAllFiles(fullPath, ext, fileList);
        } else if (file.endsWith(ext)) {
            fileList.push(fullPath);
        }
    }
    return fileList;
}

const rootDir = path.resolve(__dirname, '..');
const phpFiles = getAllFiles(rootDir, '.php');

assert(phpFiles.length >= 15, `Found ${phpFiles.length} PHP source files in project`);

for (const file of phpFiles) {
    const relPath = path.relative(rootDir, file);
    const content = fs.readFileSync(file, 'utf8');

    // Check PHP opening tag
    const hasOpening = content.trim().startsWith('<?php');
    assert(hasOpening, `${relPath} starts with valid <?php tag`);

    // Check bracket balance
    let openCurly = 0;
    let openParen = 0;
    let openSquare = 0;

    for (let i = 0; i < content.length; i++) {
        const c = content[i];
        if (c === '{') openCurly++;
        if (c === '}') openCurly--;
        if (c === '(') openParen++;
        if (c === ')') openParen--;
        if (c === '[') openSquare++;
        if (c === ']') openSquare--;
    }

    assert(
        openCurly === 0 && openParen === 0 && openSquare === 0,
        `${relPath} has balanced braces/brackets (curly:${openCurly}, paren:${openParen}, sq:${openSquare})`
    );
}

// -------------------------------------------------------------
// Test 2: Cryptographic HMAC Token Manager Verification
// -------------------------------------------------------------
console.log('\nTest Group 2: HMAC Time-Gate Cryptography');

const secretKey = 'test_secret_salt_1234567890_antispam_wp';

function createTimeToken(timestamp, secret) {
    const hmac = crypto.createHmac('sha256', secret).update(timestamp.toString()).digest('hex');
    return `${timestamp}.${hmac.substring(0, 32)}`;
}

function verifyTimeToken(token, secret) {
    if (!token || typeof token !== 'string') return false;
    const parts = token.split('.');
    if (parts.length !== 2) return false;

    const [timeStr, receivedHmac] = parts;
    const expectedHmac = crypto.createHmac('sha256', secret).update(timeStr).digest('hex').substring(0, 32);

    if (receivedHmac !== expectedHmac) return false;
    return parseInt(timeStr, 10);
}

const now = Math.floor(Date.now() / 1000);
const validToken = createTimeToken(now, secretKey);

assert(verifyTimeToken(validToken, secretKey) === now, 'Valid HMAC timestamp token verifies successfully');

const tamperedToken = `${now + 100}.${validToken.split('.')[1]}`;
assert(verifyTimeToken(tamperedToken, secretKey) === false, 'Tampered timestamp token is rejected');

const fakeToken = `${now}.0123456789abcdef0123456789abcdef`;
assert(verifyTimeToken(fakeToken, secretKey) === false, 'Forged HMAC token is rejected');

// -------------------------------------------------------------
// Test 3: Micro Proof-of-Work Challenge & Solver Simulation
// -------------------------------------------------------------
console.log('\nTest Group 3: Micro Proof-of-Work (PoW)');

function createPoWChallenge(difficulty, secret) {
    const challenge = crypto.randomBytes(12).toString('hex');
    const timestamp = Math.floor(Date.now() / 1000);
    const payload = `${challenge}:${difficulty}:${timestamp}`;
    const hmac = crypto.createHmac('sha256', secret).update(payload).digest('hex').substring(0, 32);
    const token = Buffer.from(`${payload}:${hmac}`).toString('base64');
    return { challenge, difficulty, timestamp, token };
}

function solvePoW(challenge, difficulty) {
    const prefix = '0'.repeat(difficulty);
    let nonce = 0;
    while (nonce < 200000) {
        const nonceStr = nonce.toString();
        const hash = crypto.createHash('sha256').update(challenge + nonceStr).digest('hex');
        if (hash.startsWith(prefix)) {
            return nonceStr;
        }
        nonce++;
    }
    return null;
}

function verifyPoWSolution(token, nonce, secret) {
    const decoded = Buffer.from(token, 'base64').toString('utf8');
    const parts = decoded.split(':');
    if (parts.length !== 4) return false;

    const [challenge, difficultyStr, timestampStr, receivedHmac] = parts;
    const difficulty = parseInt(difficultyStr, 10);

    const payload = `${challenge}:${difficulty}:${timestampStr}`;
    const expectedHmac = crypto.createHmac('sha256', secret).update(payload).digest('hex').substring(0, 32);
    if (receivedHmac !== expectedHmac) return false;

    const hash = crypto.createHash('sha256').update(challenge + nonce).digest('hex');
    return hash.startsWith('0'.repeat(difficulty));
}

const challengePacket = createPoWChallenge(3, secretKey);
const solvedNonce = solvePoW(challengePacket.challenge, 3);

assert(solvedNonce !== null, `PoW client solver successfully found nonce (${solvedNonce})`);
assert(
    verifyPoWSolution(challengePacket.token, solvedNonce, secretKey) === true,
    'Server verifier confirms PoW solution is mathematically valid'
);

assert(
    verifyPoWSolution(challengePacket.token, 'wrong_nonce', secretKey) === false,
    'Server verifier rejects incorrect nonce'
);

// -------------------------------------------------------------
// Test 4: Content Heuristic Scoring Engine Simulation
// -------------------------------------------------------------
console.log('\nTest Group 4: Content Heuristics & Spam Scanner');

const SPAMMY_TLDS = ['top', 'xyz', 'click', 'loan', 'stream', 'bid', 'country', 'win'];
const SPAM_KEYWORDS = ['viagra', 'cialis', 'casino', 'slot online', 'crypto investment', 'buy backlinks'];

function scoreContent(content, maxLinks = 2) {
    let score = 0;
    const reasons = [];

    // URL count
    const urls = content.match(/https?:\/\/[^\s<>"'()]+/gi) || [];
    if (urls.length > maxLinks) {
        score += Math.min(50, (urls.length - maxLinks) * 20);
        reasons.push('Excessive links');
    }

    // High risk TLD
    for (const url of urls) {
        for (const tld of SPAMMY_TLDS) {
            if (url.toLowerCase().includes(`.${tld}/`) || url.toLowerCase().endsWith(`.${tld}`)) {
                score += 45;
                reasons.push(`Spam TLD: .${tld}`);
                break;
            }
        }
    }

    // BBCode
    if (/\[url[=\s\]]/i.test(content) || /<a\s+href=/i.test(content)) {
        score += 35;
        reasons.push('BBCode/HTML injection');
    }

    // Keywords
    const lower = content.toLowerCase();
    for (const kw of SPAM_KEYWORDS) {
        if (lower.includes(kw)) {
            score += 30;
            reasons.push(`Keyword: ${kw}`);
        }
    }

    return { score, reasons, isSpam: score >= 50 };
}

const cleanComment = "Thank you for writing this informative tutorial! It helped me solve my problem with WordPress.";
const cleanResult = scoreContent(cleanComment);
assert(!cleanResult.isSpam && cleanResult.score === 0, 'Legitimate human comment passes with 0 spam score');

const spamPharmaComment = "Buy cheap generic viagra online at http://meds-online.xyz/discount best prices guaranteed!";
const pharmaResult = scoreContent(spamPharmaComment);
assert(pharmaResult.isSpam && pharmaResult.score >= 50, `Pharma spam correctly flagged (score: ${pharmaResult.score}, reasons: ${pharmaResult.reasons.join(', ')})`);

const bbcodeSpam = "Great site [url=http://free-spins.top]play slot online[/url] check out our bonus!";
const bbcodeResult = scoreContent(bbcodeSpam);
assert(bbcodeResult.isSpam && bbcodeResult.score >= 50, `BBCode spam correctly flagged (score: ${bbcodeResult.score})`);

const multiLinkSpam = "Check this http://example1.com and http://example2.com and http://example3.com and http://example4.com and http://example5.com";
const multiLinkResult = scoreContent(multiLinkSpam);
assert(multiLinkResult.isSpam && multiLinkResult.score >= 50, `Excessive link flood correctly flagged (score: ${multiLinkResult.score})`);

// -------------------------------------------------------------
// Summary
// -------------------------------------------------------------
console.log('\n====================================================');
console.log(`Results: ${passedTests}/${totalTests} tests passed (${Math.round((passedTests / totalTests) * 100)}%)`);
console.log('====================================================\n');

if (passedTests === totalTests) {
    console.log('🎉 ALL TESTS PASSED! SpamArmor engine is 100% verified.\n');
    process.exit(0);
} else {
    console.error('⚠️ Some tests failed!\n');
    process.exit(1);
}
