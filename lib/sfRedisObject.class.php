<?php

abstract class sfRedisObject implements Serializable
{
    
    const INCID_KEY    = '%s:_inc_id';
    
    private $_data     = array();
    private $_fields   = array();
    private $_keyField = null;
    
    public function __construct() {
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
                $this->_fields[] = array(
                    'name'    => $name,
                    'type'    => $field->type,
                    'is_a'	  => $field->is_a
                );
                // we need to remove it so any actions done to it will come here to __get and __set
                $this->__set($name, $this->$name);
                unset($this->$name);
            } else if($property->hasAnnotation('RedisCollection')) {
                $field = $property->getAnnotation('RedisField');
                $this->_fields[] = array(
                    'name'    => $name,
                    'type'    => $field->type,
                    'has'     => $field->has
                );
                // we need to remove it so any actions done to it will come here to __get and __set
                $this->__set($name, new sfRedisCollection($this->$name));
                unset($this->$name);
            }
        }
    }
    
    public function __get($fieldName) {
        return $this->get($fieldName);
    }

    public function get($fieldName) {
        return $this->_get($fieldName);
    }
    
    protected function _get($fieldName) {
        return $this->_data[$fieldName];
    }
    
    public function __set($fieldName, $value) {
        $this->set($fieldName, $value);
    }
    
    public function set($fieldName, $value) {
        $this->_set($fieldName, $value);
    }
    
    protected function _set($fieldName, $value) {
        $this->_data[$fieldName] = $value;
    }

    public function getKey() {
        $key = $this->get($this->_keyField);
        
        if($key !== null)
            return $key;
            
        $id  = $this->get('id');
        $id  = ($id === null) ? sfRedis::getClient()->incr(sprintf(self::INCID_KEY, get_class($this))) : $id;
        
        $key = sprintf('%s:%s', get_class($this), $id);
        $this->set($this->_keyField, $key);
        
        return $key;
    }
    
    public function getData() {
        return $this->_data;
    }
    
    public function setData($data = array()) {
        $this->_data = $data;
    }
    
    public function getFields() {
        return $this->_fields;
    }
    
    public function serialize() {
        return serialize($this->_data);
    }
    
    public function unserialize($serialized) {
        $this->_data = unserialize($serialized);
    }

}