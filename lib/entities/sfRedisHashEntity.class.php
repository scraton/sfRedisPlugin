<?php

class sfRedisHashEntity extends sfRedisEntity
{
    
    protected $_data = array();
    
    public function getObject() {
        return $this->value;
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
    
    public function getData() {
        return $this->_data;
    }
    
    public function setData($v) {
        $this->_data = $v;
    }
    
    public function get($field) {
        if(!isset($this->_data[$field]))
            return $this->getClient()->hget($this->getKey(), $field);
        else
            return $this->_data[$field];
    }
    
    public function set($field, $value) {
        return $this->getClient()->hset($this->getKey(), $field, $value);
    }
    
    protected function _save() {
        if(!($this->getClient() instanceof Predis_CommandPipeline))
            $this->pipeline();
        
        $data = $this->getObject()->getData();
        
        foreach($this->getObject()->getFields() as $field) {
            $value = $data[$field->name];
            
            $this->set($field->name, $field->toRedis($value));
        }
        
        $this->getClient()->hset($this->getKey(), '_obj', get_class($this->getObject()));
        
        return $this->executePipeline();
    }
    
}