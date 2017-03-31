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
}