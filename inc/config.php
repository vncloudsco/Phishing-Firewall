<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/


## Database Data
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_HOST', 'localhost');
define('DB_NAME', 'phishingfirewall');


## User Data for login
$username = 'admin';
$password = 'admin';

$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
session_start();

include "functions.php";
require_once "SafeBrowsingAPI.php";
require_once "AntiBot/Antibot.php";

$ab = new Antibot();
$ab->check();