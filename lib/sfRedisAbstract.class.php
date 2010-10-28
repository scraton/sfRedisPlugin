<?php

abstract class sfRedisAbstract
{
    
    const INCID_KEY     = '%s:_inc_id';
    
    protected $_key       = null;
    protected $_index     = null;
    protected $_meta      = null;
    protected $_data      = array();
    protected $_persisted = false;
    protected $_entity    = null;
    
    public function __construct($index = null) {
        $this->_key = get_class($this);
        
        if($this->_meta === null)
            $this->loadMeta();
        
        $this->setIndex($index);
        
        $entity_class = $this->_meta->entity;
        
        if(!class_exists($entity_class))
            throw new sfRedisException('Entity class `'.$entity_class.'` does not exist or was not loaded.');
        
        $this->_entity = new $entity_class($this);
        $this->_entity->associate($this);
    }
    
    protected function loadMeta() {
        $ref = new ReflectionAnnotatedClass($this);
        foreach(sfRedisEntityManager::getEntitiesList() as $e)
            if($ref->hasAnnotation($e)) {
                $this->setMeta( $ref->getAnnotation($e) );
                return;
            }
        // we couldn't find an entity type
        throw new sfRedisException('`'.get_class($this).'` is not a RedisEntity');
    }
    
    public function getKey() {
        return sprintf('%s:%s', $this->_key, $this->getIndex());
    }
    
    public function getIndex() {
        return $this->_index;
    }
    
    public function setIndex($index) {
        $this->_index = $index;
    }
    
    public function getData() {
        return $this->_data;
    }
    
    /**
     * @return sfRedisEntity
     */
    public function getEntity() {
        return $this->_entity;
    }
    
    public function getMeta() {
        return $this->_meta;
    }
    
    public function setMeta($meta) {
        $this->_meta = $meta;
    }
    
    public function isPersisted($set = null) {
        if($set !== null)
            $this->_persisted = $set;
        else
            return ($this->_persisted);
    }
    
    public function toRedis() {
        if($this->isPersisted())
            return $this->getIndex();
        else {
            $this->getEntity()->getManager()->persist($this);
            return $this->getIndex();
        }
    }
    
}