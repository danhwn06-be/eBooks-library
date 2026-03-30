<?php
define('DS', DIRECTORY_SEPARATOR);

// Autoload by Composer
require_once '..' . DS . 'vendor' . DS . 'autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// config/config.php
require_once '..' . DS . 'config' . DS . 'config.php';

// Autoload custom
spl_autoload_register(function ($className) {
    if (strpos($className, '\\') !== false) {
        return;     // avoid confilcts with composer
    }

    if (preg_match('/Controller$/', $className) && $className !== 'Controller') {
        $file = APP_ROOT . DS . 'app' . DS . 'controllers' . DS . $className . '.php';
    }

    // call core function to run MVC custom
    elseif (in_array($className, ['App', 'Controller', 'Database', 'Model'])) {
        $file = APP_ROOT . DS . 'app' . DS . 'core' . DS . $className . '.php';
    }
    else {
        $file = APP_ROOT . DS . 'app' . DS . 'models' . DS . $className . '.php';
    }
    if (file_exists($file)) {
        require_once $file;
    }
});

$app = new App();