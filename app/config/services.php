<?php
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Router;
use Phalcon\Http\Request;
use Phalcon\Http\Response;

use Phalcon\Mvc\Collection\Manager;
use Phalcon\Db\Adapter\MongoDB\Client;

// Create a DI
$di = new FactoryDefault();

//Registering a router
$di->set('router', function ()
{
    $router = new Router();
    require 'routes.php';
    return $router;
});

// Setup the view component
$di->set(
    "view",
    function () use ($config) {
        $view = new View();
        $view->setViewsDir($config->application->viewsDir);
        return $view;
    }
);

$di->set('dispatcher', function(){
    // Create/Get an EventManager
    $eventsManager = new Phalcon\Events\Manager();

    // Attach a listener
    $eventsManager->attach("dispatch", function ($event, $dispatcher, $exception) {
        // The controller exists but the action not
        if ($event->getType() == 'beforeNotFoundAction') {
            $dispatcher->forward(array(
                'namespace' => 'App\Controllers',
                'controller' => 'error',
                'action' => 'page404'
            ));
            return false;
        }
        // Alternative way, controller or action doesn't exist
        if ($event->getType() == 'beforeException') {
            switch ($exception->getCode()) {
                case Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward(array(
                        'namespace' => 'App\Controllers',
                        'controller' => 'error',
                        'action' => 'page404'
                    ));
                    return false;
            }
        }
    });

    $dispatcher = new Phalcon\Mvc\Dispatcher();

    // Bind the EventsManager to the dispatcher
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});

// Initialise the mongo DB connection.
$di->setShared('mongo', function () {
    /** @var \Phalcon\DiInterface $this */
    $config = $this->getShared('config');

    if (!$config->database->mongo->username || !$config->database->mongo->password) {
        $dsn = 'mongodb://' . $config->database->mongo->host.":". $config->database->mongo->port;
    } else {
        $dsn = sprintf(
            'mongodb://%s:%s@%s:%s/%s',
            $config->database->mongo->username,
            $config->database->mongo->password,
            $config->database->mongo->host,
            $config->database->mongo->port,
            $config->database->mongo->dbname
        );
    }

    $mongo = new Client($dsn);

    return $mongo->selectDatabase($config->database->mongo->dbname);
});

$di->set('collectionManager', function () {
    return new Manager();
}, true);

// Register a "repository" service in the container
$di->set('repository', function () {
    $repository =  new App\Repositories\Repositories();
    return $repository;
});

// Register a "model" service in the container
$di->set('model', function () {
    $model =  new App\Models\Models();
    return $model;
});

// Register a "myLibrary" service in the container
$di->set('myLibrary', function () {
    $myLib =  new App\Library\MyLibrary();
    return $myLib;
});

// Register a "mongoService" service in the container
$di->set('mongoService', function () {
    $myLib =  new App\Services\MongoService();
    return $myLib;
});

// Register a "response" service in the container
$di->set('response', function () {
    $response = new Response();
    return $response;
});

// Register a "request" service in the container
$di->set('request', function () {
    $request = new Request();
    return $request;
});

//add config and message
$di->set('config', $config, true);
$di->set('status', $status, true);
$di->set('message', $message, true);