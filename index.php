<?php

header("Access-Control-Allow-Origin: *");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Hot Melon</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
    <link rel="manifest" href="/assets/manifest.json">
    <link rel="mask-icon" href="/assets/safari-pinned-tab.svg" color="#34d152">
    <link rel="shortcut icon" href="/assets/favicon.ico">
    <meta name="msapplication-config" content="/assets/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">


    <link href="css/tether.min.css" rel="stylesheet">
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="js/tether.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        var xhr;
        $(function(){
            $("#btn-use-melon").click(function(){
                handleSignInClick();
            });

            $("#btn-upload-file").click(function(){
                $("#hidden-input-file").click();
            });

            $("#hidden-input-file").change(function(){
                /*var name = (new Date()).getTime().toString(32);
                var file = $(this)[0].files[0]; //Files[0] = 1st file
                var reader = new FileReader();
                reader.readAsText(file, 'UTF-8');
                reader.onload = function (event) {
                    var result = event.target.result;
                    var fileName = name + ".tex"; //Should be 'picture.jpg'
                    $.post('/upload.php', { data: result, name: fileName }, function(){
                        window.location = "chat.php?name="+fileName;
                    });
                }*/




                xhr = new XMLHttpRequest();
                if (xhr.upload) {
                    xhr.open("POST", "https://hot-melon.herokuapp.com/thesis");
                    xhr.setRequestHeader("Content-Type", "application/octet-stream");
                    xhr.onreadystatechange = function () {
                        if(xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                            window.location = "chat.php?name="+xhr.responseText;
                        }
                    };
                    xhr.send($(this)[0].files[0]);
                }
            });

            $("#btn-drive-choose").click(AuthDrive);
        });



        // The Browser API key obtained from the Google Developers Console.
        var developerKey = 'AIzaSyDsJ9M5K0R7HudCsfQCHCOx4qevfwJJx3g';

        // The Client ID obtained from the Google Developers Console. Replace with your own Client ID.
        var clientId = "94230008778-s8lj3e5fjb3psgcu2icj05jud0nqhial.apps.googleusercontent.com";

        var scope = ['https://www.googleapis.com/auth/drive'];

        var pickerApiLoaded = false;
        var oauthToken;

        // Use the API Loader script to load google.picker and gapi.auth.
        function onApiLoad() {
            gapi.load('auth');
            gapi.load('picker', {'callback': onPickerApiLoad});
        }

        function AuthDrive() {
            window.gapi.auth.authorize(
                {
                    'client_id': clientId,
                    'scope': scope,
                    'immediate': false
                },
                handleAuthResult);
        }

        function onPickerApiLoad() {
            pickerApiLoaded = true;
        }

        function handleAuthResult(authResult) {
            if (authResult && !authResult.error) {
                oauthToken = authResult.access_token;
                createPicker()
            }
        }

        function createPicker() {
            if (pickerApiLoaded && oauthToken) {
                var docView = new google.picker.DocsView(google.picker.ViewId.FOLDERS);
                docView.setSelectFolderEnabled(true)
                    .setOwnedByMe(true);

                var picker = new google.picker.PickerBuilder().
                addView(docView).
                setOAuthToken(oauthToken).
                setDeveloperKey(developerKey).
                setCallback(pickerCallback).
                build();
                picker.setVisible(true);
            }
        }

        // A simple callback implementation.
        function pickerCallback(data) {
            var url = 'nothing';
            var id = 'nothing';
            if (data[google.picker.Response.ACTION] == google.picker.Action.PICKED) {
                console.log(data);
                var doc = data[google.picker.Response.DOCUMENTS][0];
                url = doc[google.picker.Document.URL];
                id = doc[google.picker.Document.ID];
            }
            var message = 'You picked: ' + url;
            console.log(message);
            console.log("The file id is " + id);
        }
    </script>
</head>

<body>
    <nav class="navbar navbar-toggleable-md navbar-light bg-faded">
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbar-global" aria-controls="navbar-global"
            aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
        <a class="navbar-brand" href="#">Hot Melon</a>
        <div class="collapse navbar-collapse" id="navbar-global">
            <?php /*<div class="navbar-nav">
                <a class="nav-item nav-link active" href="#">Home</a>
                <a class="nav-item nav-link" href="#">About</a>
            </div>*/?>
        </div>
    </nav>
    <main class="melon-main d-flex justify-content-center flex-column">
        <section class="melon-hero">
            <img src="images/hotmelon.png" alt="Hot Melon Logo">
            <button class="btn btn-primary btn-lg" id="btn-upload-file">Upload Tex</button>
            <?php /*<button class="btn btn-primary btn-lg" id="btn-drive-choose">2. Choose Drive Location</button>
            <a class="btn btn-primary btn-lg" id="btn-use-melon" href="chat.php">2. Next</a>*/?>
            <form method="POST" enctype="multipart/form-data">
                <input id="hidden-input-file" type="file" style="display: none" name="userfile">
            </form>
        </section>
    </main>

    <script type="text/javascript" src="https://apis.google.com/js/api.js?onload=onApiLoad"></script>
</body>

</html>