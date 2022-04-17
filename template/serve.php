<?php

$query = "";
if (!empty($_GET)) {
    
    $query = "?".$_SERVER["QUERY_STRING"];
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
        }
        #iframe {
            padding: 0;
            width: 100%;
            height: 100%;
            position: fixed;
            display: flex;
            justify-content: center;
            top: 0;
        }
        #iframe>iframe {
            width: 100%;
            height: 100%;
        }
        @keyframes loader {
            0% { width:30px; height:30px; }
            50% { width:70px; height:70px; opacity:0; }
        }
        #loader {
            opacity: 0.5;
            animation: loader 1s ease-out infinite;
            align-self:center;
            width: 30px;
            height: 30px;
            background-color: red;
            border-radius: 100%;
            display: inline-block;
            position: fixed;
        }
    </style>

</head>
<body>

    <div id="iframe">
        <div id="loader"></div>
        <iframe frameborder="0"></iframe>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var baseurl = "<?= $this->watch["baseurl"] ?>";
            var loader = document.querySelector("#loader");
            var iframe = document.querySelector("#iframe>iframe");
            var watch = new EventSource(baseurl+"?watch");
            var size = "<?= $this->watch["size"] ?>";

            setTimeout( function() {
                loader.style.display = "none";
            }, 1500);

            iframe.setAttribute("src", baseurl+"/<?=$this->config["serve"] ?><?=$query?>");
            
            watch.addEventListener("watch", function (event) {
                if (size != event.data) {
                    window.location.assign("");
                    console.log("page reloaded successfully");
                }
            });
        });
    </script>
</body>
</html>