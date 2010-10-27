<?php

abstract class sfRedisObject extends sfRedisAbstract
{
    
    private $_fields     = array();
    private $_indexField = null;
    
    protected function loadMeta() {
        parent::loadMeta();
        
        $ref = new ReflectionAnnotatedClass($this);
        foreach($ref->getProperties() as $property) {
            // property needs to be public to be able to be persisted in Redis :)
            if(!$property->isPublic())
                continue;
            
            $name       = $property->getName();
            $annotation = self::findAnnotationForField($property);
            
            if($annotation instanceof RedisIndex)
                $this->_indexField  = $name;
            
            if($annotation instanceof RedisField) {
                $this->_fields[$name] = sfRedisField::createFromAnnotation($this, $name, $annotation);
                $this->__set($name, $this->$name);
                unset($this->$name);
            }
        }
        
        $this->ensureIndexField();
    }
    
    private function ensureIndexField() {
        if($this->_indexField === null) {
            $this->_fields['_id'] = new sfRedisField($this, '_id');
            $this->_indexField    = '_id';
        }
    }
    
    private static function findAnnotationForField($property) {
        $annotations = array('RedisIndex', 'RedisField', 'RedisRelation', 'RedisCollection');
        foreach($annotations as $a)
            if($property->hasAnnotation($a))
                return $property->getAnnotation($a);
    }
    
    public function getFields() {
        return $this->_fields;
    }
    
    public function get($field) {
        return $this->_get($field);
    }
    
    public function __get($field) {
        return $this->get($field);
    }
    
    protected function _get($fieldName) {
        $field = $this->_fields[ $fieldName ];
        if(!($field instanceof sfRedisField))
            if(property_exists($this, $fieldName))
                return $this->$fieldName;
            else
                return null;
        else
            return $field->getValue();
    }
    
    public function set($field, $value) {
        if($field == $this->_indexField)
            $this->setIndex($value);
        
        $this->_set($field, $value);
    }
    
    public function __set($field, $value) {
        $this->set($field, $value);
    }
    
    protected function _set($fieldName, $value) {
        $field = $this->_fields[ $fieldName ];
        if(!($field instanceof sfRedisField))
            $this->$fieldName = $value;
        else
            $field->setValue($value);
    }
    
}