<?php

class sfRedisField
{
    
    public $name  = null;
    public $type  = 'string';
    
    public $is_index = false;
    public $is_score = false;
    
    public static function createFromAnnotation($name, RedisField $annotation) {
        if($annotation instanceof RedisCollection)
            return sfRedisCollectionField::createFromAnnotation($name, $annotation);
        elseif($annotation instanceof RedisRelation)
            return sfRedisRelationField::createFromAnnotation($name, $annotation);
            
        if(in_array($annotation->type, array('date', 'datetime', 'timestamp')))
            return sfRedisDateTimeField::createFromAnnotation($name, $annotation);
        
        $field       = new sfRedisField($name);
        $field->type = $annotation->type;
        
        if($annotation instanceof RedisIndex)
            $field->is_index = true;
        if($annotation instanceof RedisScore)
            $field->is_score = true;
        
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