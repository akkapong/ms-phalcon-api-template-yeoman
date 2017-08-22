<?php
namespace App\Models;

use Phalcon\Exception;
use Phalcon\DI;

class Models 
{
    public $config;
    public $mongo;
    public $mongoService;

    public function __construct()
    {
        $this->config       = DI::getDefault()->get('config');
        $this->mongo        = DI::getDefault()->get('mongo');
        $this->mongoService = DI::getDefault()->get('mongoService');
    }

    public function getModel($name)
    {
        $className = "\\App\\Models\\{$name}";

        if (!class_exists($className)) {
            throw new Exception("Model Class {$className} doesn't exists.");
        }

        return new $className();
    }

    //method for find data
    public function find($filter)
    {
        $query = new \MongoDB\Driver\Query($filter[0], $filter);
        $cursor = $this->mongo->executeQuery($this->config->database->mongo->dbname.'.'.$this->getSource(), $query);
        
        return $cursor;
    }

    //method for assign data to model
    protected function assignDataToModel($dataObj, $filter)
    {
        foreach ($dataObj as $key => $value) {
            if ( property_exists($this, $key) ) {
                $this->{$key} = $value;
            }
        }
        $this->lastQuery = $filter;
        return $this;
    }

    //Method for fine data bt id
    public function findById($id)
    {
        $output = null;
        $id     = $this->mongoService->createMongoId($id);
        $filter = ['_id' => $id];
        $data   = $this->find([$filter])->toArray();

        if (!empty($data)) {
            $output = $data[0];
            $output = $this->assignDataToModel($output, $filter);
        }
        return $output;
    }

    //Method for count data
    public function count($filter)
    {
        // Command
        $command = new \MongoDB\Driver\Command(["count" => $this->getSource(), "query" => $filter[0]]);

        // Result
        $result = $this->mongo->executeCommand($this->config->database->mongo->dbname, $command);
        $count  = $result->toArray()[0]->n;
        return $count;
    }

    //Method for get only data from class
    public function getOnlyData()
    {
        $updates = new \StdClass;
        $datas =  get_object_vars($this);
        foreach ($datas as $key => $value) {
            // if ($key == '_handlers') {
            //     continue;
            // }
            if (is_string($value) || is_array($value)) {
                $updates->$key = $value;
            }
        }
        if (property_exists($this, '_id')) {
            $updates->_id = $this->_id;
        }
        return $updates;
    }

    //Method for save data
    public function save()
    {
        $datas  = $this->getOnlyData();
        // print_r($datas); exit;
        if (property_exists($this, 'lastQuery')) {
            //update
            $bulk         = new \MongoDB\Driver\BulkWrite;
            $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 100);

            $bulk->update(
                $this->lastQuery,
                ['$set' => $datas],
                ['multi' => true, 'upsert' => true]
            );

            $cursor = $this->mongo->executeBulkWrite($this->config->database->mongo->dbname.'.'.$this->getSource(), $bulk, $writeConcern);

            $this->_id = $this->lastQuery['_id'];

            unset($this->lastQuery);

        } else {
            //create
            $bulk      = new \MongoDB\Driver\BulkWrite;
            $_id       = $bulk->insert($datas);
            
            $cursor    = $this->mongo->executeBulkWrite($this->config->database->mongo->dbname.'.'.$this->getSource(), $bulk);
            
            $this->_id = $_id;
        }

        if (!$cursor) {
            return false;
        }

        return true;
    }

    //Method for delete data
    public function delete()
    {
        if (!property_exists($this, 'lastQuery')) {
            return false;
        }

        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->delete($this->lastQuery);

        $cursor = $this->mongo->executeBulkWrite($this->config->database->mongo->dbname.'.'.$this->getSource(), $bulk);

        $this->_id = $this->lastQuery['_id'];
        unset($this->lastQuery);

        if (!$cursor) {
            return false;
        }

        return true;
    }

    //Method for get data by aggreate
    public function aggregate($pipeline)
    {
        // Define output
        $outputs = [];
        // Command
        $command = new \MongoDB\Driver\Command([
            "aggregate" => $this->getSource(), 
            "pipeline"  => $pipeline,
            "cursor"    => new \StdClass,
        ]);

        // Result
        $cursor = $this->mongo->executeCommand($this->config->database->mongo->dbname, $command);
        
        foreach ($cursor as $document) {
            $outputs[] = $document;
        }
        return $outputs;
    }
}
