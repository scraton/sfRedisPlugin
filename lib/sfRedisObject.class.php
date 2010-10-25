<?php

abstract class sfRedisObject
{
    
    const INCID_KEY     = '%s:_inc_id';
    
    private $_data      = array();
    private $_fields    = array();
    private $_keyField  = null;
    
    private $_key       = null;
    
    private $_persisted = false;
    
    /**
     * @var sfRedisEntityManager
     */
    private $_em;
    
    /**
     * @var sfRedisEntity
     */
    private $_entity;
    
    public function __construct($key = null, sfRedisEntityManager $em = null) {
        if(empty($this->_fields))
            $this->loadFields();
            
        $this->_key    = $key;
        
        $this->_em     = ($em !== null) ? $em : sfRedisEntityManager::create();
        $this->_entity = new sfRedisHashEntity($this->_em, $this);
        
        if($this->_key !== null) {
            $type = $this->getEntity()->getType();
            
            if($type == get_class($this))
                $this->_persisted = true;
            else if(!$type)
                $this->_persisted = false;
            else 
                throw new sfRedisException('Attempting to load invalid type `'.$type.'` into sfRedisObject `'.get_class($this).'`');
        }
    }
    
    private function loadFields() {
        $ref = new ReflectionAnnotatedClass($this);
        foreach($ref->getProperties() as $property) {
            $name = $property->getName();
            
            if($property->hasAnnotation('RedisKey')) {
                $this->_keyField = $name;                
                // we need to remove it so any actions done to it will come here to __get and __set
                $this->__set($name, $this->$name);
                unset($this->$name);
            } else if($property->hasAnnotation('RedisField')) {
                $field = $property->getAnnotation('RedisField');
                $field->name = $name;
                
                $this->_fields[ $name ] = $field;
                // we need to remove it so any actions done to it will come here to __get and __set
                $this->__set($name, $this->$name);
                unset($this->$name);
            } else if($property->hasAnnotation('RedisCollection')) {
                $field = $property->getAnnotation('RedisCollection');
                $field->name = $name;
                
                $this->_fields[ $name ] = $field;
                // we need to remove it so any actions done to it will come here to __get and __set
                $key = $this->getKey();
                $collection = sfRedisCollection::createForField($field, "{$key}:{$name}");
                $this->__set($name, $collection);
                unset($this->$name);
            }
        }
    }
    
    public function get($field) {
        return $this->_get($field);
    }
    
    public function __get($field) {
        return $this->get($field);
    }
    
    protected function _get($fieldName) {
        $field = $this->_fields[ $fieldName ];
        
        if($field === null)
            return (isset($this->_data[$fieldName])) ? $this->_data[$fieldName] : null;
        else if(!isset($this->_data[$fieldName]) && $this->isPersisted())
            $this->_data[$fieldName] = $this->getEntity()->get($field);
        else if(!isset($this->_data[$fieldName]))
            $this->_data[$fieldName] = null;
        
        return $this->_data[$fieldName];
    }
    
    public function set($field, $value) {
        if($field == $this->_keyField)
            return ($this->_key = $value);
        
        $this->_set($field, $value);
    }
    
    public function __set($field, $value) {
        $this->set($field, $value);
    }
    
    protected function _set($field, $value) {
        $this->_data[$field] = $value;
        
        if($this->isPersisted())
            $this->getEntity()->set($field, $value);
    }
    
    public function isPersisted() {
        return ($this->_persisted);
    }

    public function getKey() {
        if($this->_key !== null)
            return $this->_key;
            
//        $id  = $this->get('id');
//        $id  = ($id === null) ? sfRedis::getClient()->incr(sprintf(self::INCID_KEY, get_class($this))) : $id;
        
        $this->_key = sprintf('%s:%s', get_class($this), $id);
        
        return $this->_key;
    }
    
    public function getData() {
        return $this->_data;
    }
    
    public function getFields() {
        return $this->_fields;
    }
    
    /**
     * @return sfRedisHashEntity
     */
    public function getEntity() {
        return $this->_entity;
    }
    
}