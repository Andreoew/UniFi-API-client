<?php
session_start();

require __DIR__ . '/../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

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

// Se for GET, exibe o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Pega id e ap da query string e salva na sessão
    if (isset($_GET['id']) && isset($_GET['ap'])) {
        $_SESSION["id"] = $_GET["id"];
        $_SESSION["ap"] = $_GET["ap"];
    }
    ?>
    <!doctype html>
    <html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <title>WiFi Portal</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .login-container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                padding: 40px;
                width: 100%;
                max-width: 400px;
                text-align: center;
            }
            
            .logo {
                width: 120px;
                height: 120px;
                margin: 0 auto 30px;
                border-radius: 50%;
                background: #f8f9fa;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }
            
            .logo img {
                width: 80px;
                height: 80px;
                object-fit: contain;
            }
            
            h1 {
                color: #333;
                margin-bottom: 10px;
                font-size: 24px;
                font-weight: 600;
            }
            
            .subtitle {
                color: #666;
                margin-bottom: 30px;
                font-size: 16px;
                line-height: 1.5;
            }
            
            .form-group {
                margin-bottom: 20px;
                text-align: left;
            }
            
            label {
                display: block;
                margin-bottom: 8px;
                color: #333;
                font-weight: 500;
                font-size: 14px;
            }
            
            input[type="text"],
            input[type="email"] {
                width: 100%;
                padding: 15px;
                border: 2px solid #e1e5e9;
                border-radius: 10px;
                font-size: 16px;
                transition: all 0.3s ease;
                background: #f8f9fa;
            }
            
            input[type="text"]:focus,
            input[type="email"]:focus {
                outline: none;
                border-color: #667eea;
                background: white;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            
            .submit-btn {
                width: 100%;
                padding: 15px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 10px;
            }
            
            .submit-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            }
            
            .submit-btn:active {
                transform: translateY(0);
            }
            
            .footer {
                margin-top: 30px;
                color: #666;
                font-size: 12px;
            }
            
            @media (max-width: 480px) {
                .login-container {
                    padding: 30px 20px;
                    margin: 10px;
                }
                
                .logo {
                    width: 100px;
                    height: 100px;
                }
                
                .logo img {
                    width: 60px;
                    height: 60px;
                }
                
                h1 {
                    font-size: 20px;
                }
                
                .subtitle {
                    font-size: 14px;
                }
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="logo">
                <img src="/logo.png" alt="Logo" onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'font-size: 40px; color: #667eea;\'>📶</span>';">
            </div>
            
            <h1>Bem-vindo!</h1>
            <p class="subtitle">Conecte-se à nossa rede Wi-Fi gratuita</p>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="name">Nome completo</label>
                    <input type="text" id="name" name="name" placeholder="Digite seu nome completo" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                </div>
                
                <button type="submit" class="submit-btn">
                    Conectar à Internet
                </button>
            </form>
            
            <div class="footer">
                <p>Conectando você ao mundo digital</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// POST: processa autenticação
if (empty($_SESSION["id"]) || empty($_SESSION["ap"])) {
    die("Erro: dados do dispositivo não encontrados.");
}
if (empty($_POST['name']) || empty($_POST['email'])) {
    die("Erro: nome e e-mail são obrigatórios.");
}

$mac  = $_SESSION["id"];
$ap   = $_SESSION["ap"];
$name = $_POST['name'];
$email = $_POST['email'];

$duration          = 30; // minutos
$site_id           = $_ENV['SITE_ID'];
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
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>WiFi Portal - Conectado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="5;url=https://www.google.com/" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            border-radius: 50%;
            background: #4CAF50;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
        }
        
        h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 24px;
            font-weight: 600;
        }
        
        .message {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        
        .redirect {
            color: #999;
            font-size: 14px;
        }
        
        @media (max-width: 480px) {
            .success-container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            h1 {
                font-size: 20px;
            }
            
            .message {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            ✓
        </div>
        
        <h1>Conectado com sucesso!</h1>
        <p class="message">Você está online e pode navegar livremente pela internet.</p>
        <p class="redirect">Redirecionando em 5 segundos...</p>
    </div>
</body>
</html> 