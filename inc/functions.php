<?php


function getIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

function addLog($status) {
    global $pdo;
    $getsettings = $pdo->query("SELECT * FROM settings");
    $settings = $getsettings->fetch();

    $ip = getIp();
    $userAgent = $_SERVER["HTTP_USER_AGENT"];
    $path = $_SERVER["REQUEST_URI"];

    if($status == 1 && $settings["smartban"] == 1) {
        $banIp = $pdo->prepare("INSERT INTO bannedips (ip) VALUES (:ip)");
        $banIp->bindParam("ip", $ip);
        $banIp->execute();
    }

    $addLog = $pdo->prepare("INSERT INTO logs (user_agent, ip, date, path, status) VALUES (:ua, :ip, NOW(), :p, :s)");
    $addLog->bindParam("ua", $userAgent);
    $addLog->bindParam("ip", $ip);
    $addLog->bindParam("p", $path);
    $addLog->bindParam("s", $status);
    $addLog->execute();

}