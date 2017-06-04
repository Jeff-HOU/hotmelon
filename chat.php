<?php

$gapi_referral = "chat.php";
require_once "gapi.php";

$people = new Google_Service_People($client);

$names = $people->people->get("people/me", array(
    'requestMask.includeField'=>'person.names'
));

$photos = $people->people->get("people/me", array(
    'requestMask.includeField'=>'person.photos'
));

if (isset($_GET["name"])) {
    if(shell_exec("python3 /json2markdown.py https://hotmelon-655ec.firebaseio.com/.json ${$_GET["name"]} md.md
")) {
        $str = shell_exec("md2gslides -n md.md");
    }
}

//$names->names[0]->givenName;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Hot Melon</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="css/tether.min.css" rel="stylesheet">
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="js/tether.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.0.2/socket.io.js"></script>
    <script src="https://apis.google.com/js/api.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/annyang/2.6.0/annyang.min.js"></script>
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <script src="https://www.gstatic.com/firebasejs/4.1.1/firebase.js"></script>
    <script>
        $(function () {
            gapi.load("client:auth2", initClient);
        });

        function initClient() {
            gapi.client.init({
                apiKey: "AIzaSyDsJ9M5K0R7HudCsfQCHCOx4qevfwJJx3g",
                discoveryDocs: ["https://www.googleapis.com/discovery/v1/apis/people/v1/rest"],
                clientId: "94230008778-s8lj3e5fjb3psgcu2icj05jud0nqhial.apps.googleusercontent.com",
                scope: 'profile'
            }).then(function () {
                gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);
                if (!gapi.auth2.getAuthInstance().isSignedIn.get()) {
                    window.location = "/";
                    return true;
                } else {
                    setupStage();
                }
            });
        }

        function updateSigninStatus(isSignedIn) {
            if (!isSignedIn) {
                window.location = "/";
                return true;
            }
            setupStage();
        }

        function setupStage() {
            gapi.client.people.people.get({
                "resourceName": "people/me",
                "requestMask.includeField": "person.photos"
            }).then(function (response) {
                $("#user-avatar").attr("src", response.result.photos[0].url);
            }, function (reason) {
                console.log('Error: ' + reason.result.error.message);
            });
        }
    </script>
    <?php
    if (isset($str)) {
        echo "<script>var link = $str</script>";
    }
    ?>

</head>

<body>
<main class="fill-height d-flex justify-content-center">
    <div id="slides" style="position:relative">
        <div id="slide">
            <p id="slides-content">

            </p>
        </div>

        <div style="width:100%;position:absolute;text-align:center;bottom:50px;left:0;">
            <div class="btn-group" role="group" aria-label="Basic example">
                <button type="button" class="btn btn-secondary" onclick="prev()">prev</button>
                <button type="button" class="btn btn-secondary" onclick="next()">next</button>
            </div>
        </div>


    </div>
    <div id="chat">
        <div class="avatars">
            <img class="avatar float-left" src="/images/hotmelon120.png">
            <img id="user-avatar" class="avatar float-right">
        </div>
        <div class="log">
            <p class="msg msg-bot">hi, here is your slides:) What would u like to change?</p>
        </div>
        <div class="melon-input">
            <div class="input-group">
                    <span class="input-group-btn">
                        <button class="btn btn-secondary" onclick="speak()" type="button">speak</button>
                    </span>
                <input id="input-chat-message" class="form-control" type="text" placeholder="Type here..." style="float:right">
            </div>
        </div>
    </div>
</main>
<script>
    // Initialize Firebase
    var config = {
        apiKey: "AIzaSyD5S0YpJskTLBkh0pROTkxChFnpxukdKD4",
        authDomain: "hotmelon-655ec.firebaseapp.com",
        databaseURL: "https://hotmelon-655ec.firebaseio.com",
        projectId: "hotmelon-655ec",
        storageBucket: "hotmelon-655ec.appspot.com",
        messagingSenderId: "1033058926443"
    };
    firebase.initializeApp(config);
    var playersRef = firebase.database().ref("style/");

    var socket = io.connect('https://hotmelon.herokuapp.com');
    //var socket = io.connect('http://localhost:3000');
    socket.emit('start', { text: "hi" });
    responsiveVoice.speak("hi, here is your slides:) What would u like to change?");
    var $log;
    $(function () {
        $log = $("#chat").find("> .log");
        $('#input-chat-message').on("keyup", function (e) {
            if (e.which === 13) {
                var val = $(this).val();
                if (val.trim() !== "") {
                    makeNewChatBubble("you", val);
                    socket.emit('msg', { text: val });
                    $(this).val('');
                }
            }
        });
        var json = {};
        var slides = document.getElementById("slides-content");
        socket.on('reply', function (data) {
            if (!data.complete) {
                switch (data.intent) {
                    case "color":
                        json.color = data.param.color.replace(" ", "");
                        slides.style.color = data.param.color.replace(" ", "");
                        break;
                    case "background-color":
                        json['background-color'] = data.param.color.replace(" ", "");
                        document.getElementById("slide").style.backgroundColor = data.param.color.replace(" ", "");
                        break;
                    case "font-size":
                        json['font-size'] = data.param['unit-length'].amount + "px";
                        slides.style.fontSize = data.param['unit-length'].amount + "px";
                        break;
                }
            }
            makeNewChatBubble("bot", data.text);
            if (data.intent === "end") {
                playersRef.set({
                    backgroundColor: json['background-color'] || "white",
                    color: json.color || "black"
                });
            }
        });
    });

    function hi(text) {
        makeNewChatBubble("you", text);
        socket.emit('msg', { text: text });
    }

    function makeNewChatBubble(entityName, msg) {
        if (entityName == "bot") {
            responsiveVoice.speak(msg);
        }
        $(`<div class="clearfix"><p class="msg msg-${entityName}">${msg}</p></div>`).appendTo($log);
        $log.stop().animate({ scrollTop: $log[0].scrollHeight }, 500, 'swing');
    }

    if (annyang) {
        // Display the user input
        annyang.addCallback('result', function (userSaid) {
            // Catch the highest possible input from user
            var userInput = userSaid[0];
            console.log(userInput);
            makeNewChatBubble("you", userInput);
            socket.emit('msg', { text: userInput });
            // here you can do anything to that "userInput"
        });

        // Start listening
        function speak() {
            annyang.start();
        };
    }
    var index = 2;
    var ppt_content = {};
    var fb_url = "https://hotmelon-655ec.firebaseio.com/ppt.json";
    var ppt_len = 0;
    $.get(fb_url, function (data) {
        console.log(data);
        ppt_content = data;
        ppt_len = Object.keys(ppt_content).length;
        $('#slide').css("background-color", ppt_content[1]["bg"]);
        $('#slide').css("color", ppt_content[1]["color"]);
        update_ppt();
    });
    function prev() {
        if (index > 2) index--;
        update_ppt();
    }
    function next() {
        if (index < ppt_len) index++;
        update_ppt();
    }
    function update_ppt() {
        var slides = $('#slides-content');
        slides.empty();
        var content = "";
        for (key in ppt_content[index]) {
            var data = ppt_content[index][key];
            switch (key.substr(1)) {
                case "figure":
                    content = '<img id="figure1" src="' + data + '">';
                    break;
                case "keyword":
                    content = "<ul>";
                    var keywords = data.substr(1, data.length - 1).split(',');
                    for (var i = 0; i < keywords.length; i++) {
                        content += "<li>" + keywords[i] + "</li>";
                    }
                    content += "</ul>";
                    break;
                default:
                    content = data;
                    break;
            }
            slides.append('<div id="' + key.substr(1) + '">' + content + '</div>');
        }
    }
</script>
</body>

</html>