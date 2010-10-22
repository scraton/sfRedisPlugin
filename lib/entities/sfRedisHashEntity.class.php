<?php

class sfRedisHashEntity extends sfRedisEntity
{
    
    public function load($key, Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        
        $data = $client->hgetall($key);
        
        if(!class_exists($data['_obj']))
            return false;
            
        $obj = new $data['_obj'];
        
        unset($data['_obj']);
        
        $obj->setData($data);
        
        return $obj;
    }
    
    public function save(Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        
        $key  = $this->getKey();
        $data = $this->getObject()->getData();
        
        $data['_obj'] = get_class($this->getObject());
        
        return $client->hmset($key, $data);
    }
    
    public function delete(Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        $client->del($this->getKey());
    }
    
}