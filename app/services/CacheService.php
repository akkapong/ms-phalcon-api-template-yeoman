<?php
namespace App\Services;

class CacheService extends \Phalcon\Mvc\Micro{
    //---- Start: Define variable ----//
    protected $delimeter = '-';

    //---- End: Define variable ----//

    //---- Start: support method ----//
    //method for sort params by key
    protected function sortParameter($params)
    {
        ksort($params);
        return $params;
    }

    //method for encode params
    protected function encodeParam($params)
    {
        return hash('md5', json_encode($params));
    }

    
    //---- End: support method ----//

    //---- Start: main method ----//
    //method for create cache key
    public function generateCacheKey($service, $method, $params)
    {
        //sort params
        $params  = $this->sortParameter($params);
        $keyText = $service.$this->delimeter.$method.$this->delimeter.$this->encodeParam($params);
        return $keyText;
    }

    //Check have cache data
    public function checkCache($key)
    {
        return $this->cache->exists($key);
    }

    //Get cache data
    public function getCache($key)
    {
        if ($this->checkCache($key)) {
            return $this->cache->get($key);
        }
        return null;
    }

    //Get cache data by prefix
    public function getCacheByPrefix($prefix)
    {
        //Define output
        $outputs = [];
        $keys = $this->cache->queryKeys($prefix);
        foreach ($keys as $key) {
            $outputs[$key] = $this->getCache($key);
        }
        return $outputs;
    }

    //Add cache data
    public function addCache($key, $data)
    {
        return $this->cache->save($key, $data);
    }

    //Delete cache data
    public function deleteCache($key)
    {
        return $this->cache->delete($key);
    }

    //Delete cache data by prefix
    public function deleteCacheByPrefix($prefix)
    {
        $keys = $this->cache->queryKeys($prefix);
        foreach ($keys as $key) {
            $this->deleteCache($key);
        }
        return true;
    }
    //---- End: main method ----//
    
}