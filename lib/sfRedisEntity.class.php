<?php

abstract class sfRedisEntity
{
    
    /**
     * @var sfRedisEntityManager
     */
    protected $em    = null;
    protected $key   = null;
    protected $value = null;
    
    public function __construct(sfRedisEntityManager $em, sfRedisObject $object = null) {
        $this->em    = $em;
        $this->value = $object;
    }
    
    public function getManager() {
        return $this->em;
    }
    
    public function getKey() {
        $key = $this->getObject()->getKey();
        
        if($key !== null)
            return $key;
            
        return get_class($this->getObject());
    }
    
    public function getObject() {
        return $this->value;
    }
    
    public function getValue() {
        return serialize($this->getObject());
    }
    
    abstract public function load($key, Predis_Client $client = null);
    abstract public function save(Predis_Client $client = null);
    abstract public function delete(Predis_Client $client = null);
    
}