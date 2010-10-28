<?php

class sfRedisField
{
    
    public $name  = null;
    public $type  = 'string';
    
    public static function createFromAnnotation($name, RedisField $annotation) {
        if($annotation instanceof RedisCollection)
            return sfRedisCollectionField::createFromAnnotation($name, $annotation);
        elseif($annotation instanceof RedisRelation)
            return sfRedisRelationField::createFromAnnotation($name, $annotation);
        
        $field       = new sfRedisField($name);
        $field->type = $annotation->type;
        
        return $field;
    }
    
    public function __construct($name) {
        $this->name  = $name;
    }
    
    public function fromRedis($value) {
        return $value;
    }
    
    public function toRedis($value) {
        return $value;
    }
    
    public function getDefault() {
        return null;
    }
    
}