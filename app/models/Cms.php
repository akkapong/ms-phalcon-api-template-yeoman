<?php
namespace App\Models;

use Phalcon\Mvc\MongoCollection;

class Cms extends MongoCollection
{
    public $title;
    public $shortdesc;
    public $longdesc;
    
    public function getSource()
    {
        return 'cms';
    }
}