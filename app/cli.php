<?php
use Phalcon\Loader;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Di\FactoryDefault\Cli as CliDI;
// Using the CLI factory default services container
$di = new CliDI();
//=== Start: Register class===
// Load the configuration file (if any)
$configFile = __DIR__ . "/config/config.php";
if (is_readable($configFile)) {
    $config = include $configFile;
    $di->set("config", $config);
}
//register repositories
$di->set('repository', function() {
    return new \App\Repositories\Repositories();
});
//=== End: Register class===


//=== Start: Load and set namespace ===
/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new Loader();
$loader->registerDirs(
    [
        __DIR__ . "/tasks",
        __DIR__ . "/repositories",
    ]
);
$loader->registerNamespaces(array(
    'App\\Repositories' => __DIR__ . '/repositories/',
    'App\\Tasks'        => __DIR__ . '/tasks/',
));
$loader->register();
//=== End: Load and set namespace ===
//Load vendor
include __DIR__.'/../vendor/autoload.php';
// Create a console application
$console = new ConsoleApp();
$console->setDI($di);
//=== Start: manage console arguments ===
/**
 * Process the console arguments
 */
$arguments = [];
foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments["task"] = $arg;
    } elseif ($k === 2) {
        $arguments["action"] = $arg;
    } elseif ($k >= 3) {
        $arguments["params"][] = $arg;
    }
}
try {
    // Handle incoming arguments
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
exit(255);
}
//=== End: manage console arguments ===