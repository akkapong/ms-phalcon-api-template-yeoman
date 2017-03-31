<?php
namespace App\Library;

class MyLibrary {
    
    //Method for format output
    public function formatOutput($model)
    {
        //Define output
        $output = [];

        if (!empty($model)) {
            //add id to output
            $output       = $model->toArray();
            $output["id"] = (string)$model->_id;
            unset($output["_id"]);
        }
        
        return $output;
    }

    
    //Method for get data by filter
    public function getDataByFilter($model, $filter, $isFormatOutput=true)
    {
        //Define output
        $outputs = [];
        
        if (isset($filter['id'])) {
            $dataObj = $model->findById($filter['id']);
            if ($isFormatOutput) {
                $outputs = $this->formatOutput($dataObj);
            } else {
                $outputs = $dataObj;
            }
        } else {
            $modelDatas   =  $model->find([$filter]);
            if (!empty($modelDatas)) {
                foreach ($modelDatas as $each) {
                    if ($isFormatOutput) {
                        $outputs[] = $this->formatOutput($each);
                    } else {
                        $outputs[] = $each;
                    }
                }
            }
        }
        
        return $outputs;
    }

    //Method for create filter remove not allow filter
    public function createFilterCheckAllow($params, $allows)
    {
        //Define output
        $filters = [];
        foreach ($params as $key => $value) {
            if (in_array($ket, $allows)) {
                $filters[$key] = $value;
            }
        }
        return $filters;
    }

    //Method for create condition filter
    public function createConditionFilter($params, $options)
    {
        //Define output
        $conditions = [];

        //manage limit offset data
        $params = $this->manageLimitOffsetInParams($params);

        foreach ($params as $key => $value) {
            if (isset($options[$key])) {
                $conditions[$key] = [
                    $options[$key] => $value
                ];
            } else {
                $conditions[$key] = $value;
            }
        }
        return $conditions;
    }

    //Method for manage limit offset
    public function manageLimitOffsetInParams($params)
    {
        if (isset($params['limit'])) {
            $params['limit'] = (int)$params['limit'];
        }

        if (isset($params['offset'])) {
            $params['skip'] = (int)$params['offset'];
            //remove offset
            unset($params['offset']);
        }

        return $params;
    }

    //method for add id to data
    public function addIdTodata($dataObj, $multi=true)
    {
        //Define output
        $outputs = [];
        if (!$multi) {
            //one data
            $outputs = $this->formatOutput($dataObj);
        } else {
            //multi
            foreach ($dataObj as $each) {
                $outputs[] = $this->formatOutput($each);
            }
        }

        return $outputs;
    }
}