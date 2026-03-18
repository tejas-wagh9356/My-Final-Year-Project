<?php
header("Content-Type: application/json");
date_default_timezone_set("Asia/Kolkata");

$raw = $_POST['target'] ?? '';
$target = trim($raw);

if ($target == "") {
    echo json_encode(["reply" => "Please provide target URL"]);
    exit;
}

/* ================= NORMALIZE URL ================= */

if (!preg_match("/^https?:\/\//", $target)) {
    $target = "http://" . $target;
}

$parsed = parse_url($target);
$host = $parsed['host'] ?? '';

if (!$host) {
    echo json_encode(["reply" => "Invalid URL"]);
    exit;
}

$report = [];
$riskScore = 0;

/* ================= IP ADDRESS ================= */
$ip = gethostbyname($host);
$report[] = "🌐 IP Address: $ip";

/* ================= HTTPS CHECK ================= */
if (str_starts_with($target, "https://")) {
    $report[] = "🟢 HTTPS Enabled";
} else {
    $report[] = "🔴 HTTPS Missing";
    $riskScore += 20;
}

/* ================= SSL CHECK ================= */
if (str_starts_with($target, "https://")) {

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
            $info = openssl_x509_parse($cert);
            $validTo = $info['validTo_time_t'];
            $daysLeft = floor(($validTo - time()) / 86400);

            $report[] = "🟢 SSL Valid ($daysLeft days left)";

            if ($daysLeft < 10) {
                $riskScore += 15;
            }
        } else {
            $report[] = "🔴 SSL Certificate Not Found";
            $riskScore += 25;
        }
    } else {
        $report[] = "🔴 SSL Connection Failed";
        $riskScore += 25;
    }
}

/* ================= SQL INJECTION CHECK ================= */
if (preg_match("/(\?|&)(id|user|uid|cat)=/i", $target)) {
    $report[] = "🔴 Possible SQL Injection Parameter";
    $riskScore += 20;
} else {
    $report[] = "🟢 No obvious SQL Injection pattern";
}

/* ================= XSS CHECK ================= */
if (preg_match("/(<script>|onerror=|onload=)/i", $target)) {
    $report[] = "🔴 Possible XSS Pattern";
    $riskScore += 20;
} else {
    $report[] = "🟢 No obvious XSS pattern";
}

/* ================= ADMIN PANEL CHECK ================= */
$commonAdmin = ["admin", "login", "dashboard"];
foreach ($commonAdmin as $adminPath) {
    $checkUrl = "http://$host/$adminPath";
    $headers = @get_headers($checkUrl);
    if ($headers && strpos($headers[0], "200")) {
        $report[] = "🟠 Admin Panel Found: /$adminPath";
        $riskScore += 10;
        break;
    }
}

/* ================= SERVER INFO ================= */
$headers = @get_headers($target, 1);
if (isset($headers['Server'])) {
    $report[] = "🖥 Server: " . $headers['Server'];
}

/* ================= RISK LOGIC ================= */

if ($riskScore >= 60) {
    $riskLevel = "🔴 HIGH RISK";
    $final = "❌ UNSAFE WEBSITE";
} elseif ($riskScore >= 30) {
    $riskLevel = "🟠 MEDIUM RISK";
    $final = "⚠️ SUSPICIOUS WEBSITE";
} else {
    $riskLevel = "🟢 LOW RISK";
    $final = "✅ SAFE WEBSITE";
}

echo json_encode([
    "reply" =>
        "🛡 Vulnerability Scan Result\n\n" .
        "Target: $target\n\n" .
        implode("\n", $report) .
        "\n\n📊 Risk Score: $riskScore / 100\n" .
        "Risk Level: $riskLevel\n\n" .
        "Final Verdict: $final"
]);
