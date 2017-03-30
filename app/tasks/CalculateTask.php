<?php
use Phalcon\DI;
use Phalcon\DI\InjectionAwareInterface;
use Phalcon\Cli\Task;
use Phalcon\DiInterface;
use App\Repositories\CalculateRepository;

class CalculateTask extends Task implements InjectionAwareInterface
{
    public $di;

    //Method for create calcuateReposritory class
    private function createCalRepo()
    {
        return new CalculateRepository();
    }

    //Method for di
    public function setDI(DiInterface $di)
    {
        $this->di = $di;
    }
    
    public function getDI()
    {
        return $this->di;
    }

    private function showResult($n1, $n2, $result)
    {
        echo $n1." + ".$n2." = ".$result."\n";
    }

    /* 
    * Method for sum
    */
    public function sumAction(array $params)
    {
        //create calulate repository class
        $calRepo = $this->createCalRepo();

        //call sum in calulate repository
        $result = $calRepo->sum($params[0], $params[1]);

        //print result
        $this->showResult($params[0], $params[1], $result);

        return true;
    }
}