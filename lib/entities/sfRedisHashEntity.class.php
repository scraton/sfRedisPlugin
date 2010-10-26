<?php

class sfRedisHashEntity extends sfRedisEntity
{
    
    /**
     * @var sfRedisObject
     */
    protected $value;
    
    public function __construct(sfRedisObject $obj) {
        $this->value = $obj;
    }
    
    public function getObject() {
        return $this->value;
    }
    
    public function getKey() {
        return $this->getObject()->getKey();
    }
    
    public function getObjectType() {
        $type = self::getType($this->getKey());
        
        if($type != sfRedisEntity::TYPE_HASH)
            return false;
        
        return $this->getClient()->hget($this->getKey(), '_obj');
    }
    
    public function associate($obj) {
        $key = $obj->getKey();
        
        if($key !== null) {
            $type = self::getType($key);
            
            if($type == sfRedisEntity::TYPE_NONE)
                $obj->isPersisted(false);
            else if($type != sfRedisEntity::TYPE_HASH)
                throw new sfRedisException('Attempting to associate a `'.$type.'` with a `'.self::TYPE_HASH.'`');
            else {
                $class = $this->getClient()->hget($this->getKey(), '_obj');
                if(get_class($obj) != $class)
                    throw new sfRedisException('Attempting to load a `'.$class.'` into a `'.get_class($obj).'`');
                else
                    $obj->isPersisted(true);
            }
        }
    }
    
    public function get(RedisField $field) {
        $value = $this->getClient()->hget($this->getKey(), $field->name);
        return $this->load_value($value, $field->type, $field->is_a);
    }
    
    public function set(RedisField $field, $value) {
        $value = $this->save_value($value, $field->type, $field->is_a);
        return $this->getClient()->hset($this->getKey(), $field->name, $value);
    }
    
    public function save() {
        if(!($this->getClient() instanceof Predis_CommandPipeline))
            $this->pipeline();
        
        $key    = $this->getKey();
        $data   = $this->getObject()->getData();
        
        foreach($this->getObject()->getFields() as $field) {
            $k = $field->name;
            $v = $data[$k];
            
            $this->set($field, $v);
        }
        
        $this->getClient()->hset($this->getKey(), '_obj', get_class($this->getObject()));
        
        return $this->executePipeline();
    }
    
}