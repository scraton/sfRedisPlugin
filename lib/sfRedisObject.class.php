<?php

abstract class sfRedisObject
{
    
    const INCID_KEY     = '%s:_inc_id';
    
    private $_data      = array();
    private $_fields    = array();
    private $_meta      = null;
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
    
    public function __construct($key = null) {
        if(empty($this->_fields))
            $this->loadFields();
        if(empty($this->_meta))
            $this->loadMeta();
            
        $this->_key    = $key;
        $entity_class  = $this->_meta->entity;
        
        if(!class_exists($entity_class))
            throw new sfRedisException('Entity class `'.$entity_class.'` does not exist or was not loaded.');
        
        $this->_entity = new $entity_class($this);
        $this->_entity->associate($this);
    }
    
    private function loadFields() {
        $ref = new ReflectionAnnotatedClass($this);
        foreach($ref->getProperties() as $property) {
            $name       = $property->getName();
            $annotation = self::findAnnotationForField($property);
            
            $annotation->name = $name;
            
            if($annotation instanceof RedisKey) {
                $this->_keyField = $name;
                $this->__set($name, $this->$name);
            } else if($annotation instanceof RedisCollection) {
                $this->_fields[$name] = $annotation;
                $key = $this->getKey();
                
                $collection_class = $annotation->class;
                
                if(!class_exists($collection_class))
                    throw new sfRedisException('No such collection class `'.$collection_class.'`');
                    
                $annotation->has_type = (class_exists($annotation->has)) ? 'object' : $annotation->has_type;
                
                $collection = new $collection_class("{$key}:{$name}", $annotation);
                
                $this->__set($name, $collection);
            } else if($annotation instanceof RedisField) {
                $this->_fields[$name] = $annotation;
                $this->__set($name, $this->$name);
            }
            
            unset($this->$name);
        }
    }
    
    private static function findAnnotationForField($property) {
        $annotations = array('RedisKey', 'RedisField', 'RedisCollection');
        foreach($annotations as $a)
            if($property->hasAnnotation($a))
                return $property->getAnnotation($a);
    }
    
    private function loadMeta() {
        $ref = new ReflectionAnnotatedClass($this);
        foreach(sfRedisEntityManager::getEntitiesList() as $e)
            if($ref->hasAnnotation($e)) {
                $this->_meta = $ref->getAnnotation($e);
                return;
            }
        // we couldn't find an entity type
        throw new sfRedisException('`'.get_class($this).'` is not a RedisEntity');
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
    
    protected function _set($fieldName, $value) {
        $field = $this->_fields[ $fieldName ];
        
        $this->_data[$fieldName] = $value;
        
        if($field !== null && $this->isPersisted())
            $this->getEntity()->set($field, $value);
    }
    
    public function isPersisted($set = null) {
        if($set !== null)
            $this->_persisted = $set;
        else
            return ($this->_persisted);
    }

    public function getKey() {
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