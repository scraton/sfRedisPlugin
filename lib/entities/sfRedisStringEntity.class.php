<?php

class sfRedisStringEntity extends sfRedisEntity
{
    
    public function load($key, Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        $data   = $client->get($key);
        
        if(!empty($data))
            return unserialize($data);
        else
            return false;
    }
    
    public function save(Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        
        $key = $this->getKey();
        $obj = $this->getObject();
        
        return $client->set($key, serialize($obj));
    }
    
    public function delete(Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        $client->del($this->getKey());
    }
    
}