<?php
namespace App\Library;

class MyLibrary {

    //Method for add lang to key params
    public function addLangToKeyParams($params, $keys, $lang='en')
    {
        foreach ($keys as $key) {
            if (isset($params[$key])) {
                $params[$key.".".$lang] = $params[$key];
                //remove old
                unset($params[$key]);
            }
        }
        return $params;
    }
    
    //Method for add language to allow filter
    public function addLangToAllowFilter($allowFilters, $keys, $lang='en')
    {
        foreach ($keys as $key) {
            if (in_array($key, $allowFilters)) {
                //get index
                $index = array_search($key, $allowFilters);
                //remove old fron index
                unset($allowFilters[$index]);
                //add new one
                $allowFilters[] = $key.".".$lang;
                
            }
        }
        return $allowFilters;
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

    
}