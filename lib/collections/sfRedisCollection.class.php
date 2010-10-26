<?php

abstract class sfRedisCollection implements Countable, IteratorAggregate, ArrayAccess
{
    
    protected $_key;
    protected $_data = array();
    protected $_entity;
    protected $_persisted;
    
    public function __construct($key = null, RedisCollection $meta = null) {
        if($meta instanceof RedisCollection)
            $this->_meta = $meta;
        if($this->_meta === null)
            $this->loadMeta();
        
        $this->_key    = $key;
        $entity_class  = $this->_meta->entity;
        
        if(!class_exists($entity_class))
            throw new sfRedisException('Entity class `'.$entity_class.'` does not exist or was not loaded.');
        
        $this->_entity = new $entity_class($this);
        $this->_entity->associate($this);
    }
    
    private function loadMeta() {
        $ref = new ReflectionAnnotatedClass($this);
        foreach(sfRedisEntityManager::getEntitiesList() as $e)
            if($ref->hasAnnotation($e)) {
                $this->_meta = $ref->getAnnotation($e);
                break;
            }
    }
    
    public function getData() {
        return $this->_data;
    }
    
    public function getKey() {
        return $this->_key;
    }
    
    /**
     * @return sfRedisListEntity
     */
    public function getEntity() {
        return $this->_entity;
    }
    
    public function getMeta() {
        return $this->_meta;
    }
    
    public function isPersisted($set = null) {
        if($set !== null)
            $this->_persisted = $set;
        else
            return $this->_persisted;
    }
    
    public function shift() {
        return array_shift($this->_data);
    }
    
    public function pop() {
        return array_pop($this->_data);
    }
    
    public function push($value) {
        array_push($this->_data, $value);
    }
    
    public function unshift($value) {
        array_unshift($this->_data, $value);
    }
        
}