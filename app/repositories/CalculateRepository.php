<?php
namespace App\Repositories;

Class CalculateRepository extends \Phalcon\Mvc\Micro {

    public function sum($num1, $num2)
    {
       return $num1 + $num2; 
    }
}