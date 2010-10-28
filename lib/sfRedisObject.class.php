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
                if($annotation instanceof RedisCollection)
                    $this->_fields[$name] = sfRedisCollectionField::createFromAnnotation($name, $annotation);
                else if($annotation instanceof RedisRelation)
                    $this->_fields[$name] = sfRedisRelationField::createFromAnnotation($name, $annotation);
                else
                    $this->_fields[$name] = sfRedisField::createFromAnnotation($name, $annotation);
                    
                $this->__set($name, $this->$name);
                unset($this->$name);
            }
        }
        
        $this->ensureIndexField();
    }
    
    private function ensureIndexField() {
        if($this->_indexField === null) {
            $this->_fields['_id'] = new sfRedisField('_id');
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
        $field = $this->_fields[$fieldName];
        if($field instanceof sfRedisField) {
            if($this->isPersisted() && $this->_data[$fieldName] === null)
                $this->_data[$fieldName] = $field->fromRedis( $this->getEntity()->get($fieldName) );
            elseif($this->_data[$fieldName] === null)
                $this->_data[$fieldName] = $field->getDefault();
            return $this->_data[$fieldName];    
        } else
            return $this->$fieldName;
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
        $field = $this->_fields[$fieldName];
        
        if($field instanceof sfRedisField) {
            if($this->isPersisted())
                $this->getEntity()->set($fieldName, $field->toRedis($value));
            
            $this->_data[$fieldName] = $value;
        } else
            $this->$fieldName = $value;
    }
    
}