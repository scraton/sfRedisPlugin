<?php

class sfRedisListEntity extends sfRedisEntity
{
    
    public function __construct(sfRedisCollection $collection = null) {
        $this->value = $collection;
    }
    
    public function getCollection() {
        return $this->getValue();
    }
    
    public function getKey() {
        return $this->getCollection()->getKey();
    }
    
    public function save(Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        
        $ret = true;
        
        $this->delete($client);
        
        foreach($this->value as $v)
            $ret = $ret && $client->rpush($this->getKey(), $v);
            
        return $ret;
    }
    
}