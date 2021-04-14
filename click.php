<?php
include "inc/config.php";

if(!isset($_GET["id"])) {
    addLog(1);
    http_response_code(404);
    exit("<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>");
}

$getid = $pdo->prepare("SELECT id FROM links WHERE uniq_id = :id");
$getid->bindParam("id", $_GET["id"]);
$getid->execute();
if($getid->rowCount() == 0) {
    addLog(1);
    http_response_code(404);
    exit("<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>");
}
$id = $getid->fetch()["id"];

// Only select 10 domains for performance
$getdomains = $pdo->prepare("SELECT * FROM domains WHERE link_id = :id AND status = 0 LIMIT 10");
$getdomains->bindParam("id", $id);
$getdomains->execute();
$domains = array();

if($getdomains->rowCount() == 0) {
    http_response_code(404);
    exit("<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>");
}

foreach ($getdomains->fetchAll() as $domain) {
    $domains[] = $domain["link"];
}

$get_settings = $pdo->query("SELECT * FROM settings");
$key = $get_settings->fetch()["safebrowsing_key"];

$sf = new SafeBrowsingAPI($key);
$result = $sf->checkUrls($domains);

if(!isset($result["matches"])) {
   header("Location: ".$domains[0]);
   exit();
}

foreach ($result["matches"] as $match) {
    $url = $match["threat"]["url"];
    $update = $pdo->prepare("UPDATE domains SET status = 1 WHERE link = :link");
    $update->bindParam("link", $url);
    $update->execute();
    if (($key = array_search($url, $domains)) !== false) {
        unset($domains[$key]);
    }
}

//Redirect to first unflagged url
if(empty($domains)) {
    http_response_code(404);
    exit("<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>");
}

header("Location: ".$domains[0]);