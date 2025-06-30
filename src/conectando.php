<?php

session_start();

$mac = $_SESSION["id"];
$ap = $_SESSION["ap"];
$name = $_POST['name'];
$email = $_POST['email'];

require __DIR__ . '/../vendor/autoload.php';

// Carrega variáveis do .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$duration = 30; //Duration of authorization in minutes
$site_id = $_ENV['SITE_ID'];
$controlleruser     = $_ENV['CONTROLLER_USER'];
$controllerpassword = $_ENV['CONTROLLER_PASSWORD'];
$controllerurl      = $_ENV['CONTROLLER_URL'];
$controllerversion  = $_ENV['CONTROLLER_VERSION'];
$debug = false;

$unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $controllerurl, $site_id, $controllerversion);
$set_debug_mode   = $unifi_connection->set_debug($debug);
$loginresults     = $unifi_connection->login();

$auth_result = $unifi_connection->authorize_guest($mac, $duration, $up = null, $down = null, $MBytes = null, $ap);

//User will be authorized at this point; their name and email address can be saved to some database now
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>WiFi Portal</title>
        <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta http-equiv="refresh" content="5;url=https://www.google.com/" />
    </head>
    <body>
            <p>You're online! <br>
            Thanks for visiting us!</p>
    </body>
</html>