<?php
error_reporting(E_ALL);

//Set timezone
date_default_timezone_set("Asia/Bangkok");

try {
    /**
     * Read the configuration
     */
    $config  = include __DIR__ . "/../app/config/config.php";
    $status  = include __DIR__ . "/../app/config/status.php";
    $message = include __DIR__ . "/../app/config/message.php";
    /**
     * Read auto-loader
     */
    include __DIR__ . "/../app/config/loader.php";
    /**
     * Read services
     */
    include __DIR__ . "/../app/config/services.php";
    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    echo $application->handle()->getContent();

}
catch (\Exception $e)
{
    echo $e->getMessage();
}
