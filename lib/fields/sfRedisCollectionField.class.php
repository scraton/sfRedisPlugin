<?php

class sfRedisCollectionField extends sfRedisField
{
    
    public $class    = 'sfRedisListCollection';
    public $entity   = 'sfRedisListEntity';
    public $type     = 'list';
    public $has_type = 'string';
    public $has;
    
    public static function createFromAnnotation($name, RedisCollection $annotation) {
        $field           = new sfRedisCollectionField($name);
        $field->type     = $annotation->type;
        $field->class    = $annotation->class;
        $field->entity   = $annotation->entity;
        $field->has_type = $annotation->has_type;
        $field->has      = $annotation->has;
        
        return $field;
    }
    
    public function fromRedis($value) {
        $collection = new $this->class($value);
        $collection->getMeta()->has      = $this->has;
        $collection->getMeta()->has_type = $this->has_type;
        return $collection;
    }
    
    public function toRedis($value) {
        if($value instanceof sfRedisAbstract)
            return $value->toRedis();
        else
            return null;
    }
    
    public function getDefault() {
        $collection = new $this->class;
        $collection->getMeta()->has      = $this->has;
        $collection->getMeta()->has_type = $this->has_type;
        return $collection;
    }
    
//    public function process() {
//        $key = $this->owner->getKey();
//        $this->getValue()->setIndex(sprintf("%s:%s", $key, $this->name));
//        $this->getValue()->save();
//        return $this->getValue()->getIndex();
//    }
    
}