<?php

abstract class sfRedisObject extends sfRedisAbstract
{
    
    private $_fields     = array();
    private $_indexField = null;
    private $_scoreField = null;
    
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
            if($annotation instanceof RedisScore)
                $this->_scoreField  = $name;
            
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
        $annotations = array('RedisIndex', 'RedisScore', 'RedisField', 'RedisRelation', 'RedisCollection');
        foreach($annotations as $a)
            if($property->hasAnnotation($a))
                return $property->getAnnotation($a);
    }
    
    public function getFields() {
        return $this->_fields;
    }
    
    public function get($field) {
        $accessor = 'get' . sfInflector::classify($field);
        
        if(method_exists($this, $accessor)) {
            return $this->$accessor($field);
        }
        
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
            throw new sfRedisException_UnknownPropertyException();
    }
    
    public function set($field, $value) {
        if($field == $this->_indexField)
            $this->setIndex($value);
        if($field == $this->_scoreField)
            $this->setScore($value);
            
        $accessor = 'set' . sfInflector::classify($field);
        
        if(method_exists($this, $accessor)) {
            return $this->$accessor($value, $field);
        }
        
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
            throw new sfRedisException_UnknownPropertyException();
    }
    
    public function hasField($field) {
        return isset($this->_fields[$field]);
    }
    
    /**
     * Provides getter and setter methods.
     *
     * @param  string $method    The method name
     * @param  array  $arguments The method arguments
     *
     * @return mixed The returned value of the called method
     */
    public function __call($method, $arguments) {
        if(in_array($verb = substr($method, 0, 3), array('set', 'get'))) {
            $name = substr($method, 3);
            
            if($this->hasField($name)) {
                $entityNameLower = strtolower($name);
                if($this->hasField($entityNameLower)) {
                    $entityName = $entityNameLower;
                } else {
                    $entityName = $name;
                }
            } else {
                $underScored = sfInflector::underscore($name);
                if($this->hasField($underScored)) {
                    $entityName = $underScored;
                } else if($this->hasField(strtolower($name))) {
                    $entityName = strtolower($name);
                } else {
                    $camelCase = sfInflector::camelize($name);
                    $camelCase = strtolower($camelCase[0]).substr($camelCase, 1, strlen($camelCase));
                    if($this->hasField($camelCase)) {
                        $entityName = $camelCase;
                    } else {
                        $entityName = $underScored;
                    }
                }
            }
            
            return call_user_func_array(
                array($this, $verb),
                array_merge(array($entityName), $arguments)
            );
        }
        
        throw new sfRedisException(sprintf('Unknown method %s::%s', get_class($this), $method));
    }
    
}