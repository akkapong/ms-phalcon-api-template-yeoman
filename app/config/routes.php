<?php

use Phalcon\Mvc\Router\Group as RouterGroup;

$router->removeExtraSlashes(true);

$router->setDefaults(array(
    'namespace'  => 'App\Controllers',
    'controller' => 'error',
    'action'     => 'page404'
));

//==========Route for api==========
$api = new RouterGroup(array(
    'namespace' => 'App\Controllers'
));

$api->addGet('/test', [
    'controller' => 'test',
    'action'     => 'getTest',
]);

$router->mount($api);

return $router;
