<?php
header("Content-Type: application/json");
error_reporting(0);

/* ================= API KEYS ================= */

$NEWS_API_KEY = "78eb3268f3a4de133b230809f6cc6e6d";
$WEATHER_API_KEY = "5a8eecbe8d4c471fa05102708261902";

/* ================= GET COMMAND ================= */

if(!isset($_POST['command'])){
    echo json_encode(["reply"=>"No command received.","url"=>""]);
    exit;
}

$command = strtolower(trim($_POST['command']));
$response = ["reply"=>"","url"=>""];

/* =====================================================
   OPEN WEBSITE COMMANDS
===================================================== */

$sites = [
    "google"=>"https://www.google.com",
    "youtube"=>"https://www.youtube.com",
    "github"=>"https://www.github.com",
    "facebook"=>"https://www.facebook.com",
    "instagram"=>"https://www.instagram.com",
    "amazon"=>"https://www.amazon.in",
    "calculator"=>"https://www.google.com/search?q=calculator"
];

foreach($sites as $name=>$url){
    if(strpos($command,"open ".$name)!==false){
        echo json_encode(["reply"=>"Opening ".ucfirst($name)."...","url"=>$url]);
        exit;
    }
}

/* =====================================================
   WEATHER
===================================================== */

if(strpos($command,"today weather")===0){

    $city = trim(str_replace("today weather","",$command));

    if(empty($city)){
        echo json_encode(["reply"=>"Please enter city name."]);
        exit;
    }

    $api = "https://api.weatherapi.com/v1/current.json?key=$WEATHER_API_KEY&q=".urlencode($city)."&aqi=no";
    $data = json_decode(@file_get_contents($api),true);

    if(isset($data['current'])){
        $reply = "🌦 Weather Report for ".$data['location']['name'].", ".$data['location']['country']."\n\n";
        $reply .= "🌡 Temp: ".$data['current']['temp_c']."°C\n";
        $reply .= "💧 Humidity: ".$data['current']['humidity']."%\n";
        $reply .= "🌬 Wind: ".$data['current']['wind_kph']." kph\n";
        $reply .= "☁ Condition: ".$data['current']['condition']['text'];
    } else {
        $reply = "City not found.";
    }

    echo json_encode(["reply"=>$reply]);
    exit;
}

/* =====================================================
   TIME
===================================================== */

if(strpos($command,"time")===0){

    $city = trim(str_replace("time","",$command));

    if(empty($city)){
        echo json_encode(["reply"=>"🕒 Current Server Time: ".date("h:i A")]);
        exit;
    }

    $api = "https://api.weatherapi.com/v1/timezone.json?key=$WEATHER_API_KEY&q=".urlencode($city);
    $data = json_decode(@file_get_contents($api),true);

    if(isset($data['location'])){
        $reply = "🕒 Current Time in ".$data['location']['name'].", ".$data['location']['country']."\n";
        $reply .= date("h:i A", strtotime($data['location']['localtime']));
    } else {
        $reply = "City not found.";
    }

    echo json_encode(["reply"=>$reply]);
    exit;
}

/* =====================================================
   NEWS (FIXED)
===================================================== */

if($command=="today news"){

    $api = "https://gnews.io/api/v4/top-headlines?country=in&token=78eb3268f3a4de133b230809f6cc6e6d";
    $data = json_decode(@file_get_contents($api),true);

    if(isset($data['articles'])){
        $reply = "📰 Top Indian Headlines:\n\n";
        foreach($data['articles'] as $article){
            $reply .= "• ".$article['title']."\n\n";
        }
    } else {
        $reply = "News not available.";
    }

    echo json_encode(["reply"=>$reply]);
    exit;
}

/* =====================================================
   IP INFO
===================================================== */

if(strpos($command,"ip info")===0){

    $ip = trim(str_replace("ip info","",$command));

    if(filter_var($ip, FILTER_VALIDATE_IP)){

        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)){
            $data = json_decode(@file_get_contents("http://ip-api.com/json/$ip"),true);

            if($data['status']=="success"){
                $reply = "🌐 IP: ".$ip."\n";
                $reply .= "📍 City: ".$data['city']."\n";
                $reply .= "🌍 Country: ".$data['country']."\n";
                $reply .= "🏢 ISP: ".$data['isp'];
            } else {
                $reply = "IP info not found.";
            }

        } else {
            $reply = "⚠ Private IP Address (Local Network)";
        }

    } else {
        $reply = "Invalid IP address.";
    }

    echo json_encode(["reply"=>$reply]);
    exit;
}

/* =====================================================
   CALCULATOR
===================================================== */

if(strpos($command,"calculate")===0){

    $expr = trim(str_replace("calculate","",$command));

    if(preg_match('/^[0-9+\-*\/(). ]+$/',$expr)){
        $result = @eval("return ($expr);");
        echo json_encode(["reply"=>"🧮 Result: ".$result]);
    } else {
        echo json_encode(["reply"=>"Invalid calculation."]);
    }

    exit;
}

/* =====================================================
   ADVANCED PHISHING
===================================================== */
/* =====================================================
   EMAIL PHISHING DETECTION
===================================================== */

if(strpos($command,"check email")===0){

    $email = trim(str_replace("check email","",$command));

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo json_encode(["reply"=>"❌ Invalid Email Format."]);
        exit;
    }

    $domain = substr(strrchr($email, "@"), 1);
    $risk = 0;

    $result = "📧 Email Phishing Analysis\n\n";
    $result .= "Email: $email\n";
    $result .= "Domain: $domain\n\n";

    /* DOMAIN CHECK */

    $ip = gethostbyname($domain);

    if($ip == $domain){
        $result .= "⚠ Domain not resolvable\n";
        $risk += 40;
    } else {
        $result .= "🌐 Domain IP: $ip\n";
    }

    /* SUSPICIOUS WORDS */

    if(preg_match("/(secure|login|verify|update|account|support|bank|paypal|crypto)/i",$domain)){
        $result .= "⚠ Suspicious Words Detected\n";
        $risk += 20;
    }

    /* LONG DOMAIN */

    if(strlen($domain) > 30){
        $result .= "⚠ Very Long Domain\n";
        $risk += 10;
    }

    /* FAKE BRAND DETECTION */

    $brands = ["google","paypal","amazon","facebook","instagram","apple","microsoft"];

    foreach($brands as $brand){
        if(strpos($domain,$brand)!==false && strpos($domain,$brand.".com")==false){
            $result .= "⚠ Possible Brand Impersonation ($brand)\n";
            $risk += 30;
        }
    }

    /* FINAL SCORE */

    if($risk > 100) $risk = 100;

    $result .= "\n📊 Risk Score: $risk / 100\n\n";

    if($risk >= 60){
        $result .= "❌ HIGH RISK – Possible Phishing Email";
    }
    elseif($risk >= 30){
        $result .= "⚠ MEDIUM RISK – Suspicious Email";
    }
    else{
        $result .= "✅ LOW RISK – Probably Safe";
    }

    echo json_encode(["reply"=>$result]);
    exit;
}

if(strpos($command,"check phishing")===0){

    $riskScore=0;
    $parts = explode(" ",$command);
    $url = trim(end($parts));

    if(!preg_match("~^(?:f|ht)tps?://~i",$url)){
        $url="https://".$url;
    }

    if(!filter_var($url,FILTER_VALIDATE_URL)){
        echo json_encode(["reply"=>"Invalid URL format."]);
        exit;
    }

    $host=parse_url($url,PHP_URL_HOST);
    $ip=gethostbyname($host);

    $result="🔍 PHISHING URL ANALYSIS\n\n";
    $result.="Resolved URL: $url\n\n";
    $result.="🌐 IP Address: $ip\n";

    $countryData=json_decode(@file_get_contents("http://ip-api.com/json/$ip"),true);
    $result.="🌍 Country: ".($countryData['country'] ?? "Unknown")."\n";

    if(strpos($url,"https://")===0){
        $result.="🟢 HTTPS Enabled\n";
    } else {
        $result.="🔴 HTTPS Not Secure\n";
        $riskScore+=70;
    }

    $result.="\n📊 Risk Score: $riskScore / 100\n\n";
    $result.= $riskScore>50 ? "⚠ HIGH RISK" : "✅ LOW RISK";

    echo json_encode(["reply"=>$result]);
    exit;
}

/* =====================================================
   HELP COMMAND (UPDATED)
===================================================== */

if($command=="help"){

    $reply="Available Commands:\n\n";
    $reply.="• open google / youtube / github\n";
    $reply.="• open facebook / Instagram / amazon\n";
    $reply.="• open calculator\n";
    $reply.="• today weather <city>\n";
    $reply.="• time <city>\n";
    $reply.="• today news\n";
    $reply.="• Ip info <ip>\n";
    $reply.="• calculate 5+10\n";
    $reply.="• check phishing <url>\n";
    $reply.="• check email <email>\n";

    echo json_encode(["reply"=>$reply]);
    exit;
}

/* =====================================================
   CLEAR CHAT
===================================================== */

if($command=="clear chat"){
    echo json_encode(["reply"=>"Chat cleared."]);
    exit;
}

/* =====================================================
   DEFAULT
===================================================== */

echo json_encode(["reply"=>"Command not recognized. Type 'help'."]);
exit;
?>
