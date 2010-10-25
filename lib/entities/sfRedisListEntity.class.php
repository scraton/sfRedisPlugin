<?php

class sfRedisListEntity extends sfRedisEntity
{
    
    public function __construct(sfRedisEntityManager $em, sfRedisCollection $collection = null) {
        $this->em    = $em;
        $this->value = $collection;
    }
    
    public function load($key, Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        return new sfRedisListCollection($key);
    }
    
    public function save(Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        
        $ret = true;
        
        $this->delete($client);
        
        foreach($this->value as $v)
            $ret = $ret && $client->rpush($this->getKey(), $this->serialize($v));
            
        return $ret;
    }
    
    protected function serialize($v) {
        if($v instanceof sfRedisObject) {
            $this->getManager()->persist($v);
            return $v->getKey();
        }
        
        return $v;
    }
    
}