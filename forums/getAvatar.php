<?php
    header('Content-Type: image/png');

    $rsn = urlencode($_GET['rsn']);
    $fileFormat = ".png";
    $avatarFolder = "avatars/";


    function getImage($rsn) {
        $avatarURL = "http://services.runescape.com/m=avatar-rs/" . $rsn . "/chat.png";
        $ch = curl_init($avatarURL);
        $fp = fopen('avatars/' . urldecode($rsn) . '.png', 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    function createImage($rsn) {
        $imgPng = imageCreateFromPng("avatars/" . urldecode($rsn) . ".png");
        imageAlphaBlending($imgPng, true);
        imageSaveAlpha($imgPng, true);

        return $imgPng;
    }

    if(file_exists($avatarFolder . urldecode($rsn) . $fileFormat)) {
        if(time() - filemtime($avatarFolder . urldecode($rsn) . $fileFormat) > (24*3600) * 14) {
            //Check for new version every two weeks. Can be overriden in UCP.
            getImage($rsn);
        } else {

        }
    } else {
        getImage($rsn);
    }

    imagepng(createImage($rsn));


