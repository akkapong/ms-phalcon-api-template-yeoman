<?php
namespace App\Models;

use Phalcon\Exception;

class Models
{
    public function getModel($name)
    {
        $className = "\\App\\Models\\{$name}";

        if (!class_exists($className)) {
            throw new Exception("Model Class {$className} doesn't exists.");
        }

        return new $className();
    }
}
