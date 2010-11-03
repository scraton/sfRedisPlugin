<?php

class sfRedisRelationField extends sfRedisField
{
    
    public $is_a  = null;
    
    public static function createFromAnnotation($name, RedisField $annotation) {
        $field       = new sfRedisRelationField($name);
        $field->type = $annotation->type;
        $field->is_a = $annotation->is_a;
        
        return $field;
    }
    
    public function fromRedis($value) {
        return new $this->is_a($value);
    }
    
    public function toRedis($value) {
        if($value instanceof sfRedisAbstract)
            return $value->toRedis();
        else
            return null;
    }
    
    public function getDefault() {
        return new $this->is_a;
    }
    
}