<?php

$loader = new \Phalcon\Loader();

$loader->registerDirs(
    [
        $config->application->servicesDir,
        $config->application->repoDir,
        $config->application->viewsDir,
        $config->application->modelsDir,
        $config->application->controllersDir,
    ]
);

$loader->registerNamespaces(array(
    'App\\Controllers'     => $config->application->controllersDir,
    'App\\Repositories'    => $config->application->repoDir,
    'App\\Services'        => $config->application->servicesDir,
    'App\\Model'           => $config->application->modelsDir
));

$loader->register();

include __DIR__.'/../../vendor/autoload.php';

