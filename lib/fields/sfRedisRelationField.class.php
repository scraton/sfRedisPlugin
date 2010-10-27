<?php

class sfRedisRelationField extends sfRedisField
{
    
    public $is_a  = null;
    
    public static function createFromAnnotation(sfRedisObject $owner, $name, RedisRelation $annotation) {
        $field       = new sfRedisRelationField($owner, $name);
        $field->type = $annotation->type;
        $field->is_a = $annotation->is_a;
        
        return $field;
    }
    
    public function getValue() {
        $value = parent::getValue();
        
        if(!($value instanceof $this->is_a)) {
            $value = new $this->is_a;
            $this->setValue( $value );
        }
        
        return $value;
    }
    
    public function process() {
        $this->value->save();
        return $this->value->getIndex();
    }
    
    public function unprocess($value) {
        // $value is an index for the object
        if(class_exists($this->is_a))
            return new $this->is_a($value);
        else
            return null;
    }
    
}