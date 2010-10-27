<?php

class sfRedisField
{
    
    public $owner = null;
    public $name  = null;
    public $type  = 'string';
    
    protected $value;
    
    public static function createFromAnnotation(sfRedisObject $owner, $name, RedisField $annotation) {
        if($annotation instanceof RedisCollection)
            return sfRedisCollectionField::createFromAnnotation($owner, $name, $annotation);
        elseif($annotation instanceof RedisRelation)
            return sfRedisRelationField::createFromAnnotation($owner, $name, $annotation);
        
        $field       = new sfRedisField($owner, $name);
        $field->type = $annotation->type;
        
        return $field;
    }
    
    public function __construct(sfRedisAbstract $owner, $name) {
        $this->owner = $owner;
        $this->name  = $name;
    }
    
    public function process() {
        return $this->value;
    }
    
    public function unprocess($value) {
        return $value;
    }
    
    public function getValue() {
        if($this->owner->isPersisted())
            return $this->unprocess( $this->owner->getEntity()->get($this) );
        else
            return $this->value;
    }
    
    public function setValue($v) {
        $this->value = $v;
        if($this->owner->isPersisted())
            $this->owner->getEntity()->set($this, $v);
    }
    
}