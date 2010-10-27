<?php

class sfRedisCollectionField extends sfRedisField
{
    
    public $class    = 'sfRedisListCollection';
    public $entity   = 'sfRedisListEntity';
    public $type     = 'list';
    public $has_type = 'string';
    public $has;
    
    public static function createFromAnnotation(sfRedisObject $owner, $name, RedisCollection $annotation) {
        $field           = new sfRedisCollectionField($owner, $name);
        $field->type     = $annotation->type;
        $field->class    = $annotation->class;
        $field->entity   = $annotation->entity;
        $field->has_type = $annotation->has_type;
        $field->has      = $annotation->has;
        
        return $field;
    }
    
    public function process() {
        $key = $this->owner->getKey();
        $this->getValue()->setIndex(sprintf("%s:%s", $key, $this->name));
        $this->getValue()->save();
        return $this->getValue()->getIndex();
    }
    
    public function unprocess($value) {
        // $value is an index for the object
        if(class_exists($this->class)) {
            $collection = new $this->class($value);
            
            $collection->getMeta()->has = $this->has;
            
            return $collection;
        } else
            return null;
    }
    
    public function getValue() {
        $value = parent::getValue();
        
        if(!($value instanceof $this->class)) {
            $value = new $this->class;
            
            $value->getMeta()->has      = $this->has;
            $value->getMeta()->has_type = $this->has_type;
            
            $this->setValue( $value );
        }
        
        return $value;
    }
    
}