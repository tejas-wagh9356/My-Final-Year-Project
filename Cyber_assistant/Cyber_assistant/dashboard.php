<?php
session_start();
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = "Vilas Bamanavat";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cyber Security Dashboard</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    
<!-- ================= HEADER ================= -->
<div class="header">
    <div class="left">
        Welcome, <b style="color:#22c55e"><?php echo $_SESSION['user']; ?></b>
    </div>

    <div class="center">
        <img src="Images/logo2.png" class="logo" id="logoBtn">
        <div class="title">Cyber Security Dashboard</div>
    </div>

    <div class="right">
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

<!-- ================= MAIN WRAPPER ================= -->
<div class="wrapper">

    <div class="card main-scroll-card">

        <!-- ===== CHAT SECTION ===== -->
        <h2>🔐 Cyber Security Assistant</h2>
        <p class="subtitle">Scanning for cybercrime indicators...</p>

        <div id="chat"></div>

        <div class="input-area">
            <input id="cmd" placeholder="Enter text or use mic...">
            <button class="mic-btn" id="mic">🎤</button>

            <select class="voice-select" id="voiceSelect">
                <option value="male">Male Voice</option>
                <option value="female" selected>Female Voice</option>
            </select>

            <button class="send-btn" onclick="sendCmd()">Send</button>
        </div>

        <!-- ===== SECTION DIVIDER ===== -->
        <hr class="section-divider">

        <!-- ===== VULNERABILITY SCANNER ===== -->
        <div class="vuln-section">
            <h3 class="vuln-heading">
                🛡️ ⚡ CYBER OWASP TOP 10 VULNERABILITY SCANNER ⚡
            </h3>
            <p class="vuln-subtext">
                ⚡ Real-time Cyber Threat Detection Engine
            </p>

            <div class="vuln-input-box">
                <input type="text" id="scanUrl"
                       placeholder="🔗 Enter website URL (https://example.com)">
                <button class="vuln-btn" onclick="startScan()">
                    🚀 Scan Now
                </button>
            </div>

            <div id="scanResult"></div>

        </div>

    </div>
</div>

<!-- ================= FOOTER ================= -->
<div class="dev-bar">
    <div class="dev-text">
        🚀 Developed By Tejas • Vilas • Pruthviraj • Cyber Security Assistant Project 🚀
    </div>
</div>

<!-- ================= HELP POPUP ================= -->
<div id="helpBox" style="display:none;">
    <div class="help-content">
        <h3>Help & Contact</h3>
        <p>
            📧 vilasbamanavat05@gmail.com<br>
            📞 +91 9371726135
        </p>
        <p>
            📧 tejaswagh594@gmail.com<br>
            📞 +91 9356662063
        </p>
        <p>
            📧 pruthvirajs056@gmail.com<br>
            📞 +91 9422007444
        </p>
        <button onclick="closeHelp()">Close</button>
    </div>
</div>

<script src="script.js"></script>

</body>
</html>