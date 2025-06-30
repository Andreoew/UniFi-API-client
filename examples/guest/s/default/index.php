<?php
require __DIR__ . '/../../../../src/conectando.php';
session_start();

require __DIR__ . '/../../../../vendor/autoload.php';

// Carrega variáveis do .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../');
$dotenv->load();

// Validação das variáveis de ambiente
$required_envs = [
    'CONTROLLER_USER',
    'CONTROLLER_PASSWORD',
    'CONTROLLER_URL',
    'SITE_ID',
    'CONTROLLER_VERSION'
];
foreach ($required_envs as $env) {
    if (empty($_ENV[$env])) {
        die("Erro: variável de ambiente '$env' não definida no .env.");
    }
}

// Validação dos dados de sessão e POST
if (empty($_SESSION["id"]) || empty($_SESSION["ap"])) {
    die("Erro: sessão inválida. Dados do dispositivo não encontrados.");
}
if (empty($_POST['name']) || empty($_POST['email'])) {
    die("Erro: nome e e-mail são obrigatórios.");
}

$mac  = $_SESSION["id"];
$ap   = $_SESSION["ap"];
$name = $_POST['name'];
$email = $_POST['email'];

$duration          = 30; // minutos
't$site_id           = $_ENV['SITE_ID'];
$controlleruser    = $_ENV['CONTROLLER_USER'];
$controllerpassword= $_ENV['CONTROLLER_PASSWORD'];
$controllerurl     = $_ENV['CONTROLLER_URL'];
$controllerversion = $_ENV['CONTROLLER_VERSION'];
$debug             = false;

try {
    $unifi_connection = new UniFi_API\Client(
        $controlleruser,
        $controllerpassword,
        $controllerurl,
        $site_id,
        $controllerversion
    );
    $unifi_connection->set_debug($debug);
    $loginresults = $unifi_connection->login();

    if (!$loginresults) {
        throw new Exception("Falha ao autenticar no UniFi Controller.");
    }

    $auth_result = $unifi_connection->authorize_guest($mac, $duration, null, null, null, $ap);

    if (!$auth_result) {
        throw new Exception("Falha ao autorizar o dispositivo na rede.");
    }

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}

// Aqui você pode salvar $name e $email em um banco de dados, se desejar.
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>WiFi Portal</title>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="refresh" content="5;url=https://www.google.com/" />
</head>
<body>
    <p>Você está online!<br>
    Obrigado por nos visitar!</p>
</body>
</html> 