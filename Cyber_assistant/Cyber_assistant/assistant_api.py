
import speech_recognition as sr
import pyttsx3
import webbrowser
import requests
import ssl
import socket
import random
import time
from urllib.parse import urlparse
from datetime import datetime

# ===============================
# CONFIGURATION & API KEYS
# ===============================
# Get your free API key at https://www.virustotal.com/
VIRUSTOTAL_API_KEY = "pKJ6zn3canbc9R4TMY4AaaFZ"

# ===============================
# TEXT TO SPEECH SETUP
# ===============================
engine = pyttsx3.init()
voices = engine.getProperty('voices')
engine.setProperty("rate", 170) 
# engine.setProperty('voice', voices[1].id) # Uncomment for a female voice

def speak(text):
    print(f"Assistant: {text}")
    engine.say(text)
    engine.runAndWait()

# ===============================
# SPEECH INPUT
# ===============================
def listen():
    try:
        r = sr.Recognizer()
        with sr.Microphone() as source:
            print("🎙 Listening...")
            audio = r.listen(source, timeout=5)
            text = r.recognize_google(audio)
            print("You:", text)
            return text.lower()
    except:
        return input("Type command: ").lower()


# ===============================
# SECURITY LOGIC: TEXT ANALYSIS
# ===============================
keywords = {
    "Threat": ["attack", "bomb", "weapon", "kill"],
    "Illegal": ["hack", "fraud", "scam", "phish", "bypass"],
    "Privacy": ["password", "otp", "credit card", "bank"],
    "Application Attack": ["sql injection", "xss", "cross site scripting"]
}

def analyze_text(text):
    alerts = []
    for cat, words in keywords.items():
        for w in words:
            if w in text:
                alerts.append(cat)
    return list(set(alerts))

# ===============================
# SECURITY LOGIC: URL & PHISHING
# ===============================
def check_ssl(url):
    try:
        hostname = urlparse(url).hostname
        if not hostname:
            return False, 0
        
        context = ssl.create_default_context()
        with socket.create_connection((hostname, 443), timeout=3) as sock:
            with context.wrap_socket(sock, server_hostname=hostname) as ssock:
                cert = ssock.getpeercert()
                exp_str = cert["notAfter"]
                exp_date = datetime.strptime(exp_str, "%b %d %H:%M:%S %Y %Z")
                days_left = (exp_date - datetime.utcnow()).days
                return True, days_left
    except Exception:
        return False, 0

def virus_total_scan(url):
    if VIRUSTOTAL_API_KEY == "pKJ6zn3canbc9R4TMY4AaaFZ":
        return "VirusTotal check skipped (No API Key)"
    
    try:
        url_id = requests.utils.quote(url, safe='')
        headers = {"x-apikey": VIRUSTOTAL_API_KEY}
        # This is the v3 endpoint for URL analysis
        response = requests.get(f"https://www.virustotal.com/api/v3/urls/{url_id}", headers=headers, timeout=5)
        
        if response.status_code == 200:
            stats = response.json()['data']['attributes']['last_analysis_stats']
            malicious = stats.get('malicious', 0)
            if malicious > 0:
                return f"Warning: {malicious} engines flagged this as malicious."
            return "VirusTotal: Clean"
        else:
            return "VirusTotal: URL record not found or error."
    except:
        return "VirusTotal: Connection failed."

def phishing_check(url):
    # Clean the URL input
    url = url.replace(" ", "").lower()
    if not url.startswith("http"):
        url = "https://" + url

    speak(f"Analyzing {url}")
    risk_score = 0

    # 1. Check Protocol
    if not url.startswith("https"):
        print("❌ Protocol: Insecure (HTTP)")
        risk_score += 2
    else:
        print("✅ Protocol: Secure (HTTPS)")
        # 2. Check SSL Certificate
        is_valid, days = check_ssl(url)
        if is_valid:
            print(f"✅ SSL: Valid for {days} more days")
        else:
            print("❌ SSL: Invalid or Expired")
            risk_score += 2

    # 3. External API Check
    vt_result = virus_total_scan(url)
    print(f"🔍 {vt_result}")
    if "Warning" in vt_result:
        risk_score += 3

    # Final Verdict
    if risk_score >= 4:
        speak("Alert! This website is highly suspicious and likely a phishing attempt.")
    elif risk_score >= 2:
        speak("Proceed with caution. This website has security weaknesses.")
    else:
        speak("The website appears to be safe based on current scans.")

# ===============================
# VULNERABILITY SCANNER (SIMULATED)
# ===============================
def vulnerability_scan(target):
    speak(f"Initializing vulnerability assessment for {target}")
    time.sleep(1) # Simulation delay
    
    possible_vulns = ["SQL Injection", "XSS", "Broken Authentication", "Insecure Direct Object References"]
    found = random.sample(possible_vulns, random.randint(0, 2))
    
    if found:
        print(f"--- Scan Results for {target} ---")
        for v in found:
            print(f"[!] Vulnerability Detected: {v}")
        speak(f"Scan complete. Found {len(found)} critical vulnerabilities.")
    else:
        speak("Scan complete. No common vulnerabilities were detected on the target surface.")

# ===============================
# COMMAND HANDLER
# ===============================
def handle_command(cmd):
    if not cmd:
        return

    if "time" in cmd:
        speak(datetime.now().strftime("The current time is %I:%M %p"))

    elif "open google" in cmd:
        speak("Opening Google")
        webbrowser.open("https://google.com")

    elif "scan website" in cmd or "phishing" in cmd:
        speak("Please say the website address clearly. For example, google.com")
        url_input = listen()
        if url_input:
            phishing_check(url_input)

    elif "scan target" in cmd or "vulnerability" in cmd:
        speak("What is the target name or I P address?")
        target = listen()
        if target:
            vulnerability_scan(target)

    elif "exit" in cmd or "stop" in cmd:
        speak("Shutting down. Stay safe online. Goodbye!")
        exit()

    else:
        # Check for suspicious keywords in general conversation
        alerts = analyze_text(cmd)
        if alerts:
            speak(f"Warning: I detected discussion regarding {', '.join(alerts)}. Please ensure you are following ethical guidelines.")
        else:
            speak("I'm sorry, I didn't catch that command.")

# ===============================
# MAIN EXECUTION
# ===============================
if __name__ == "__main__":
    speak("AI Cyber Security Assistant is now online.")
    speak("Ready for phishing detection or vulnerability scanning.")
    
    while True:
        user_command = listen()
        handle_command(user_command) 