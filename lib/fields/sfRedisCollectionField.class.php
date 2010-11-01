<?php

class sfRedisCollectionField extends sfRedisField
{
    
    const CLASS_LIST = 'sfRedisListCollection';
    const CLASS_SET  = 'sfRedisSetCollection';
    const CLASS_ZSET = 'sfRedisZSetCollection';
    
    public $class;
    public $entity;
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
        
        if($field->class === null)
            if($field->type == 'list')
                $field->class  = self::CLASS_LIST;
            else if($field->type == 'set')
                $field->class = self::CLASS_SET;
            else if($field->type == 'zset')
                $field->class = self::CLASS_ZSET;
        
        $field->entity = sfRedisEntityManager::getEntityClass($field->type);
        
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
    
}