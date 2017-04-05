<?php
use Phalcon\DI;
use Phalcon\DI\FactoryDefault;

date_default_timezone_set("Asia/Bangkok");

ini_set('display_errors',1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__);
define('PATH_CONTROLLER', ROOT_PATH . '/../app/controllers/');
define('PATH_REPO', ROOT_PATH . '/../app/repositories/');
define('PATH_MODEL', ROOT_PATH . '/../app/models/');
define('PATH_LIBRARY', ROOT_PATH . '/../app/library/');
define('PATH_SERVICES', ROOT_PATH . '/../app/services/');

set_include_path(
    ROOT_PATH . PATH_SEPARATOR . get_include_path()
);

// required for phalcon/incubator
include __DIR__ . "/../vendor/autoload.php";

// use the application autoloader to autoload the classes
// autoload the dependencies found in composer
$loader = new \Phalcon\Loader();

$loader->registerDirs(array(
    ROOT_PATH
));

$loader->registerNamespaces(array(
    'App\\Controllers'  => PATH_CONTROLLER,
    'App\\Repositories' => PATH_REPO,
    'App\\Library'      => PATH_LIBRARY,
    'App\\Services'     => PATH_SERVICES,
    'App\\Models'       => PATH_MODEL
));

$loader->register();

$di = new FactoryDefault();
DI::reset();

// Add any needed services to the DI here
/**
 * Registering a util
 */
$di->set('util',function(){
    $util = new Util();
    return $util;
});

DI::setDefault($di);