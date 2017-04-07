<?php
namespace App\Repositories;

use Phalcon\Exception;

class Repositories extends \Phalcon\Mvc\Micro {

    public function getRepository($name)
    {
        $className = "\\App\\Repositories\\{$name}";

        if (!class_exists($className)) {
            throw new Exception("Model Class {$className} doesn't exists.");
        }

        return new $className();
    }
}