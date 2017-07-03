<?php
namespace App\Services;

class MongoService {

    private $idDelimeter = ',';
    private $orderDelimeter = ',';
    private $orderDelimeterVal = ':';
    
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

    //Method for convert param to search like format
    protected function convertValueForSearchLike($value)
    {
        //get index of '%''
        $index = strpos($value, "%");
        if ($index === FALSE) {
            // no '%'
            return [false, $value];
        }

        //check first
        if ($index !== 0) {
            // not found '%' at first : add start with syntax '^'
            $value = '^'.$value;

        } else {
            //remove '%'
            $value = substr($value, 1);
            
        } 

        //check last
        if (substr($value, -1) == '%') {
            //found '%' at last : remove it
            $value = substr_replace($value, "", -1);
        } else {
            // not found '%' at last : add end with syntax '$'
            $value .= '$';
        }

        return [true, $value];
    }  

    //Method for manage fileter value support search like
    public function manageFilterValue($key, $value, $conditions)
    {
        //check search like
        $res = $this->convertValueForSearchLike($value);
        
        if ($res[0]) {
            //search like
            $conditions[$key] = [
                '$regex' => $res[1]
            ];
        } else {

            if ($value == 'null') {
                $conditions[$key] = null;
            } else {
                $conditions[$key] = $value;
            }
        }

        return $conditions;
                
    }

    //Method for create condition filter
    public function createConditionFilter($params, $allowFilter, $options=[])
    {
        //Define output
        $conditions = [];

        foreach ($params as $key => $value) {
            //check allow filter
            if (in_array($key, $allowFilter)) {
               if (isset($options[$key])) {
                    $conditions[$key] = [
                        $options[$key] => $value
                    ];
                } else {
                    $conditions = $this->manageFilterValue($key, $value, $conditions);
                }
            }
            
        }
        
        return $conditions;
    }

    //Method for manage limit offset
    public function manageLimitOffsetInParams($params, $filters)
    {
        if (isset($params['limit'])) {
            $filters['limit'] = (int)$params['limit'];
        }

        if (isset($params['offset'])) {
            $filters['skip'] = (int)$params['offset'];
        }

        return $filters;
    }

    //Method for convert type order
    protected function convertOrderType($val)
    {
        if ($val == 'desc') {
            return -1;
        } 
        return 1;
    }

    //Method for manage order
    public function manageOrderInParams($params, $filters, $allowFilter)
    {
        if (isset($params['order_by']) && !empty($params['order_by'])) {
            //spit order
            $orders = explode($this->orderDelimeter, $params['order_by']);

            $filters['sort'] = [];
            foreach ($orders as $order) {
                $vals = explode($this->orderDelimeterVal, $order);
                if (count($vals) == 2) {
                    if (in_array($vals[0], $allowFilter)){
                        $filters['sort'][$vals[0]] = $this->convertOrderType($vals[1]);
                    }
                }
            }
            if (empty($filters['sort'])) {
                unset($filters['sort']);
            }
        }

        return $filters;
    }

    //Method for get ids from datas
    public function getAllIdFromDatas($dataObj, $format='string')
    {
        //Define outputs
        $ids = [];
        if (!empty($dataObj)) {
            foreach ($dataObj as $each) {
                $ids[] = (string)$each->_id;
            }

            //check format
            if ($format == 'string') {
                $ids = implode($this->idDelimeter, $ids);

            }
        } else {

            if ($format == 'string'){
                $ids = "";
            }
            
        }
        
        return $ids;
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

    //Method for manage sort data bt id list
    public function manageSortDataByIdList($datas, $id)
    {
        //Define outputs
        $outputs = [];

        //get id
        $ids = explode($this->idDelimeter, $id);

        foreach ($ids as $id) {
            foreach ($datas as $data) {
                if ($data['id'] == $id) {
                    $outputs[] = $data;
                    break;
                }
            }
        }
        return $outputs;
    }

    //Method for get category detail
    public function getDetailDataById($model, $id, $allowFilter)
    {
        //get id
        $ids     = explode($this->idDelimeter, $id);
        //create option
        $options = ['id' => '$in'];
        //create filter
        $filter  = [
            'id' => $ids
        ];

        //Create conditions
        $conditions = $this->createConditionFilter($filter, $allowFilter, $options);
        
        //get data
        $datas      = $model->find($conditions);

        return $datas;
    }

    //Method for manage between filter
    public function manageBetweenFilter(&$params, &$options)
    {
        $betweenKeys = [];
        if (isset($params['between_key'])) {
            $betweenKeys = explode(",", $params['between_key']);
        }

        if (empty($betweenKeys)) {
            return $params;
        }

        foreach ($betweenKeys as $key) {
            if (isset($params[$key.'_start']) && isset($params[$key.'_end'])) {
                $params[$key] = [$params[$key.'_start'], $params[$key.'_end']];

                //add option
                $options[$key] = ['$gte', '$lte'];
                //remove old key
                unset($params[$key.'_start']);
                unset($params[$key.'_end']);
            }
        }

        return $params;
        
    }

    
}