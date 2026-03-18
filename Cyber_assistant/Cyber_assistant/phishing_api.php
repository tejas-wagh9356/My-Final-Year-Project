<?php
header("Content-Type: application/json");
date_default_timezone_set("Asia/Kolkata");

$input = trim($_POST['url'] ?? '');

if (empty($input)) {
    echo json_encode(["reply" => "Enter a valid URL"]);
    exit;
}

/* ================= NORMALIZE URL ================= */

if (!preg_match("~^https?://~i", $input)) {
    $url = "https://" . $input;
} else {
    $url = $input;
}

$parsed = parse_url($url);
$host = $parsed['host'] ?? '';

if (!$host) {
    echo json_encode(["reply" => "Invalid URL"]);
    exit;
}

$report = [];
$riskScore = 0;

/* ================= IP RESOLUTION ================= */

$ip = gethostbyname($host);

if ($ip == $host) {
    $report[] = "⚠ Unable to resolve IP";
    $riskScore += 20;
} else {
    $report[] = "🌐 IP Address: $ip";
}

/* ================= COUNTRY ================= */

$country = "Unknown";
$geo = @json_decode(@file_get_contents("http://ip-api.com/json/$ip"));

if ($geo && $geo->status == "success") {
    $country = $geo->country;
}

$report[] = "🌍 Country: $country";

/* ================= HTTPS CHECK ================= */

if (stripos($url, "https://") === 0) {
    $report[] = "🟢 HTTPS Enabled";
} else {
    $report[] = "🔴 HTTPS Missing";
    $riskScore += 30;
}

/* ================= SSL CERTIFICATE CHECK ================= */

if (stripos($url, "https://") === 0) {

    $context = stream_context_create([
        "ssl" => [
            "capture_peer_cert" => true,
            "verify_peer" => false,
            "verify_peer_name" => false
        ]
    ]);

    $client = @stream_socket_client(
        "ssl://{$host}:443",
        $errno,
        $errstr,
        5,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if ($client) {

        $params = stream_context_get_params($client);
        $cert = $params["options"]["ssl"]["peer_certificate"] ?? null;

        if ($cert) {

            $certInfo = openssl_x509_parse($cert);

            $issuedOn = date("Y-m-d", $certInfo['validFrom_time_t']);
            $validTill = date("Y-m-d", $certInfo['validTo_time_t']);
            $daysLeft = floor(($certInfo['validTo_time_t'] - time()) / 86400);

            $issuer = $certInfo['issuer']['O'] ?? "Unknown";

            if ($daysLeft < 0) {
                $report[] = "🔴 SSL Certificate Expired";
                $riskScore += 30;
            } else {
                $report[] = "🟢 SSL Certificate Valid";
                $report[] = "Issued On: $issuedOn";
                $report[] = "Valid Till: $validTill ($daysLeft days left)";
                $report[] = "Certificate Authority: $issuer";
            }

        } else {
            $report[] = "🔴 SSL Certificate Not Found";
            $riskScore += 20;
        }

    } else {
        $report[] = "🔴 TLS Handshake Failed";
        $riskScore += 20;
    }
}

/* ================= SUSPICIOUS KEYWORDS ================= */

if (preg_match("/(login|verify|secure|update|bank|account|free|bonus|wallet|crypto)/i", $url)) {
    $report[] = "⚠ Suspicious Keywords Detected";
    $riskScore += 20;
}

/* ================= DIRECT IP DOMAIN ================= */

if (filter_var($host, FILTER_VALIDATE_IP)) {
    $report[] = "⚠ Direct IP Used Instead of Domain";
    $riskScore += 20;
}

/* ================= LONG URL ================= */

if (strlen($url) > 80) {
    $report[] = "⚠ Very Long URL Detected";
    $riskScore += 10;
}

/* ================= SCORE LIMIT ================= */

if ($riskScore > 100) $riskScore = 100;

/* ================= FINAL VERDICT ================= */

if ($riskScore >= 60) {
    $verdict = "❌ HIGH RISK – UNSAFE WEBSITE";
} elseif ($riskScore >= 30) {
    $verdict = "⚠ MEDIUM RISK – SUSPICIOUS WEBSITE";
} else {
    $verdict = "✅ LOW RISK – SAFE WEBSITE";
}
/* ================= EMAIL PHISHING DETECTION ================= */

if (filter_var($input, FILTER_VALIDATE_EMAIL)) {

    $email = strtolower($input);
    $report = [];
    $riskScore = 0;

    $report[] = "📧 Email Address: $email";

    $domain = substr(strrchr($email, "@"), 1);
    $report[] = "🌐 Email Domain: $domain";

    /* ===== FREE EMAIL PROVIDERS ===== */

    $freeProviders = ["gmail.com","yahoo.com","outlook.com","hotmail.com","protonmail.com"];

    if (in_array($domain, $freeProviders)) {
        $report[] = "⚠ Free Email Provider Used";
        $riskScore += 20;
    }

    /* ===== SUSPICIOUS KEYWORDS ===== */

    if (preg_match("/(support|security|verify|update|billing|payment|wallet|crypto|bonus|free)/i", $email)) {
        $report[] = "⚠ Suspicious Email Keywords Found";
        $riskScore += 20;
    }

    /* ===== DOMAIN CHECK ===== */

    if (preg_match("/(google|paypal|amazon|facebook|instagram|bank)/i", $email) && !preg_match("/@(google|paypal|amazon|facebook|instagram)\.com$/", $email)) {
        $report[] = "⚠ Possible Brand Impersonation";
        $riskScore += 40;
    }

    /* ===== DOMAIN LENGTH ===== */

    if (strlen($domain) > 25) {
        $report[] = "⚠ Unusually Long Domain";
        $riskScore += 10;
    }

    /* ===== SCORE LIMIT ===== */

    if ($riskScore > 100) $riskScore = 100;

    /* ===== FINAL VERDICT ===== */

    if ($riskScore >= 60) {
        $verdict = "❌ HIGH RISK – PHISHING EMAIL";
    } elseif ($riskScore >= 30) {
        $verdict = "⚠ MEDIUM RISK – SUSPICIOUS EMAIL";
    } else {
        $verdict = "✅ LOW RISK – SAFE EMAIL";
    }

    echo json_encode([
        "reply" =>
        "📧 Email Phishing Analysis\n\n".
        implode("\n", $report).
        "\n\n📊 Risk Score: $riskScore / 100\n\n".
        "Final Verdict: $verdict"
    ]);

    exit;
}
/* ================= FINAL OUTPUT ================= */

echo json_encode([
    "reply" =>
        "🔍 Phishing URL Analysis\n\n" .
        "Resolved URL: $url\n\n" .
        implode("\n", $report) .
        "\n\n📊 Risk Score: $riskScore / 100\n\n" .
        "Final Verdict: $verdict"
]);
?>
