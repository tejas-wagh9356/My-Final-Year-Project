async function startScan(){

    const url = document.getElementById("scanUrl").value.trim();
    const resultBox = document.getElementById("scanResult");

    if(!url){
        resultBox.innerHTML = "<span style='color:red;'>⚠ Please enter a URL</span>";
        return;
    }

    resultBox.innerHTML = "🔄 Scanning... Please wait...";

    try{
        const response = await fetch("vulnerability_scan.php", {
            method: "POST",
            headers: {"Content-Type":"application/x-www-form-urlencoded"},
            body: "url=" + encodeURIComponent(url)
        });

        const data = await response.json();
        resultBox.innerHTML = data.result;

    }catch(error){
        resultBox.innerHTML = "<span style='color:red;'>❌ Scan Failed</span>";
        console.error(error);
    }
}
const chat = document.getElementById("chat");
const cmdInput = document.getElementById("cmd");
const micBtn = document.getElementById("mic");

function add(message, type){

    if(!chat) return;

    const div = document.createElement("div");
    div.className = type;

    div.innerText = message;

    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;
}

async function sendCmd(text=null){

    const command = (text ?? cmdInput.value).trim();
    if(!command) return;

    add(command, "user");
    cmdInput.value = "";

    try{

        let response, data;

        if(command.toLowerCase().startsWith("check phishing")){

            const url = command.substring(14).trim(); 

            if(!url){
                add("⚠ Please provide a URL.\nExample: check phishing google.com", "bot");
                return;
            }

            add("🔄 Performing Deep Phishing Scan...\n", "bot");

            response = await fetch("phishing_api.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "url=" + encodeURIComponent(url)
            });

            data = await response.json();

            add(data.reply, "bot");
            return;
        }

        response = await fetch("assistant_api.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "command=" + encodeURIComponent(command)
        });

        data = await response.json();

        add(data.reply, "bot");

        if(data.url && data.url !== ""){
            window.location.href = data.url;
        }

    }catch(error){

        add("❌ Error executing command", "bot");
        console.error(error);
    }
}

if(cmdInput){
    cmdInput.addEventListener("keydown", function(e){
        if(e.key === "Enter"){
            sendCmd();
        }
    });
}

if ('webkitSpeechRecognition' in window && micBtn) {

    const recognition = new webkitSpeechRecognition();
    recognition.lang = "en-IN";
    recognition.continuous = false;
    recognition.interimResults = false;

    micBtn.addEventListener("click", function(){
        micBtn.classList.add("listening");
        recognition.start();
    });

    recognition.onresult = function(event){
        micBtn.classList.remove("listening");
        const speechText = event.results[0][0].transcript;
        sendCmd(speechText);
    };

    recognition.onerror = function(){
        micBtn.classList.remove("listening");
    };
}
function openHelp(){
    const helpBox = document.getElementById("helpBox");
    if(helpBox){
        helpBox.style.display = "block";
    }
}
function closeHelp(){
    const helpBox = document.getElementById("helpBox");
    if(helpBox){
        helpBox.style.display = "none";
    }
}
const logoBtn = document.getElementById("logoBtn");
if(logoBtn){
    logoBtn.addEventListener("click", openHelp);
}
