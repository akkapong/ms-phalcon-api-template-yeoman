<?php
namespace App\Models;

use Phalcon\DI;
use Phalcon\Db;
use Phalcon\Db\Adapter\Pdo\Oracle as PdoOracle;

class BaseModel
{
    //====== Start: Define parameter ======//
    public $connection;
    //====== End: Define parameter ======//

    public function __construct() {
        //Get config
        $config = DI::getDefault()->get('config')->database->toArray();
        //Connect to oracle
        $this->connection = new PdoOracle($config);
    }

    //====== Start: Support Method ======//
    //Method for convert value by type
    private function convertValueByType($value, $type)
    {
        $output = "";
        switch ($type) {
            case "timestamp":
                // $date = $date = date("Y-m-d H:i:s.B", strtotime($value));
                $output = "TO_TIMESTAMP('".$value."', 'YYYY-MM-DD HH24:MI:SS.FF')";
                break;
            case "incress":
            case "int":
            case "float":
                $output = $value;
                break;
            case "string":
            default:
                $output = "'".$value."'";
        }

        return $output;
    }

    //Method for convert key by type
    private function convertKeyByType($value, $type)
    {
        $output = "";
        switch ($type) {
            case "timestamp":
                // $date = $date = date("Y-m-d H:i:s.B", strtotime($value));
                $output = "TO_TIMESTAMP(".$value.", 'YYYY-MM-DD HH24:MI:SS.FF')";
                break;
            case "incress":
            case "int":
            case "float":
            case "string":
            default:
                $output = $value;
        }

        return $output;
    }

    //Method for convert value before send to pdo
    private function manageConvertValue($params)
    {
        //Define output
        $outputs = [];

        foreach ($params as $key => $value) {
            //Check array
            if (is_array($value)) {
                $index = 1;
                foreach ($value as $each) {
                    $outputs[$key.$index] = $each;
                    $index++;
                }
            } else {
                $outputs[$key] = $value;
            }
        }

        return $outputs;
    }

    //Method for generate condition
    private function generateCondition($key, $condition="=", $type="")
    {
        $output = "";

        if (empty($type)) {
            //this field is not support
            return $output;
        }

        switch (strtolower($condition)) {
            case "between":
                $key1 = $this->convertKeyByType(":".strtolower($key)."1", $type);
                $key2 = $this->convertKeyByType(":".strtolower($key)."2", $type);
                $output .= " ".$key." ". strtoupper($condition). " ".$key1. " AND ".$key2;
                break;
            default:
                $output .= " ".$key." ".$condition." :".$this->convertKeyByType(strtolower($key), $type);
                break;
        }

        return $output;
    }

    //Method for generate condition add value
    private function generateConditionValue($key, $value, $condition="=", $type="")
    {
        $output = "";

        if (empty($type)) {
            //this field is not support
            return $output;
        }

        switch (strtolower($condition)) {
            case "between":
                $val1 = $this->convertValueByType($value[0], $type);
                $val2 = $this->convertValueByType($value[1], $type);
                $output .= " ".$key." ". strtoupper($condition). " ".$val1. " AND ".$val2;
                break;
            default:
                $output .= " ".$key." ".$condition." ".$this->convertValueByType($value, $type);
                break;
        }

        return $output;
    }

    //Method for generate where
    private function generateWhere($params=[], $conditions=[], $types=[], $mode="key")
    {
        $where = "";

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (!empty($where)) {
                    $where .= " AND";
                }
                //Get have condition?
                $condition = (isset($conditions[$key]))?$conditions[$key]:"=";
                //Get type
                $type      = (isset($types[$key]))?$types[$key]:"";
                //add condition to where
                $key       = strtoupper($key);
                if ($mode == "key") {
                    $where    .= $this->generateCondition($key, $condition, $type);
                } else {
                    $where    .= $this->generateConditionValue($key, $value, $condition, $type);
                }
            }

            $where = " WHERE".$where;
        }

        return $where;

    }

    //Method for generate insert all field from types
    private function generateInsertFromType($type, $params)
    {
        $insert = "";

        foreach ($type as $key => $value) {
            if (isset($params[$key])) {
                if (!empty($insert)) {
                    $insert .= ", ";
                }
                $insert .= strtoupper($key);
            }
            
        }

        return "(".$insert.")";
    }

    //Method for generate insert value field from types
    private function generateInsertValue($types, $params)
    {
        $output = "";

        foreach ($types as $key => $type) {
            //get value from parma
            $value = (isset($params[$key]))?$params[$key]:"";
            $value = $this->convertValueByType($value, $type);

            //Add to output
            if (!empty($output)) {
                $output .= ", ";
            }
            $output .= $value;
        }

        return "(".$output.")";
    }

    //Method gor generate update value
    private function generateUpdateValue($params, $types)
    {
        $output = "";
        foreach ($params as $key => $value) {
            //Add to output
            if (!empty($output)) {
                $output .= ", ";
            }

            //get type
            $type    = (isset($types[$key]))?$types[$key]:"";
            $output .= strtoupper($key)." = ".$this->convertValueByType($value, $type);
        }

        return $output;
    }
    //====== End: Support Method ======//

    //====== Start: Main Method ======//
    //Method for start transaction
    protected function startTransaction()
    {
        $this->connection->begin();
    }

    //Method for commit transaction
    protected function commitTransaction()
    {
        $this->connection->commit();
    }

    //Method for rollback transaction
    protected function rollbackTransaction()
    {
         $this->connection->rollback();
    }

    //Method for generate select query
    protected function generateSelectQuery($params=[], $conditions=[], $types=[], $table, $select="")
    {
        $query = "SELECT ";

        //Select
        if (!empty($select)) {
            $query .= $select;
        } else {
            $query .= "*";
        }
        //From
        $query .= " FROM ".$table;
        //Where
        $query .= $this->generateWhere($params, $conditions, $types);

        //TODO : order by limit offet here
        return $query;
    }

    //Method for generate insert query
    protected function generateInsertQuery($params=[], $types, $table)
    {
        $query = "INSERT INTO ".$table;
        //get insert
        $insert = $this->generateInsertFromType($types, $params);
        $query  .= " ".$insert;
        //get values
        $values = $this->generateInsertValue($types, $params);
        $query  .= " VALUES ".$values;

        return $query;
    }

    //Method for generate update query
    protected function generateUpdateQuery($updates=[], $wheres=[], $conditions=[], $types, $table)
    {
        $query  = "UPDATE ".$table." SET";
        //get update
        $update = $this->generateUpdateValue($updates, $types);
        $query  .= " ".$update;
        //get where
        $where  = $this->generateWhere($wheres, $conditions, $types, 'value');
        $query  .= $where;

        return $query;
    }

    //Method for generate delete query
    protected function generateDeleteQuery($wheres=[], $conditions=[], $types, $table)
    {
        $query  = "DELETE FROM ".$table;
        //get where
        $where  = $this->generateWhere($wheres, $conditions, $types, 'value');
        $query  .= $where;

        return $query;
    }

    //Method for get last insert
    protected function getLastInsertId($seqTable)
    {
        $sql = "SELECT ".$seqTable."_SEQ".".NEXTVAL FROM dual";

        $data = $this->connection->fetchOne($sql);

        if (!empty($data)) {
            return (int)$data["NEXTVAL"] - 1;
        }

        return 0;
    }

    //Method for get data form database
    protected function getData($params, $condtions, $types, $table, $select="")
    {
        //Get query
        $query = $this->generateSelectQuery($params, $condtions, $types, $table, $select);  
        //convert value by type
        $params = $this->manageConvertValue($params);

        // $connection = $this->connectOracle();
        $output = $this->connection->fetchAll($query, Db::FETCH_ASSOC, $params);
        return $output;
    }

    //Method for insert data to database
    protected function insertData($params=[], $types=[], $table)
    {
        //get query
        $query = $this->generateInsertQuery($params, $types, $table);
        
        return $this->connection->execute($query);
    }

    //Method for update data to database
    protected function updateData($updates=[], $wheres=[], $conditions=[], $types, $table)
    {
        //get query
        $query = $this->generateUpdateQuery($updates, $wheres, $conditions, $types, $table);

        return $this->connection->execute($query);
    }

    //Method for delete data to database
    protected function deleteData($wheres=[], $conditions=[], $types, $table)
    {
        //get query
        $query = $this->generateDeleteQuery($wheres, $conditions, $types, $table);

        return $this->connection->execute($query);
    }

    //Method for get data form database
    protected function rawQuery($query='', $params=[])
    {
        $output = $this->connection->fetchAll($query, Db::FETCH_ASSOC, $params);

        return $output;
    }
    //====== End: Main Method ======//
}