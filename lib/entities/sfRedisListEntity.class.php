<?php

class sfRedisListEntity extends sfRedisEntity
{
    
    public function getCollection() {
        return $this->getValue();
    }
    
    public function associate($obj) {
        $key = $obj->getKey();
        
        if($key !== null) {
            $type = self::getType($key);
            
            if($type == self::TYPE_NONE)
                $obj->isPersisted(false);
            else if($type != self::TYPE_LIST)
                throw new sfRedisException('Attempting to associate a `'.$type.'` with a `'.self::TYPE_LIST.'`');
            else 
                $obj->isPersisted(true);
        }
    }
    
    public function count() {
        return $this->getClient()->llen($this->getKey());
    }
    
    public function offsetGet($index) {
        return $this->getClient()->lindex($this->getKey(), $index);
    }
    
    public function pop() {
        return $this->getClient()->rpop($this->getKey());
    }
    
    public function shift() {
        return $this->getClient()->lpop($this->getKey());
    }
    
    public function push($v) {
        return $this->getClient()->rpush($this->getKey(), $v);
    }
    
    public function unshift($v) {
        return $this->getClient()->lpush($this->getKey(), $v);
    }
    
    protected function _save() {
        if(!($this->getClient() instanceof Predis_CommandPipeline))
            $this->pipeline();
        
        $key   = $this->getKey();
        $data  = $this->getCollection()->getData();
        $meta  = $this->getCollection()->getMeta();
        $field = $this->getCollection()->getField();
        
        foreach($data as $v) {
            $this->push( $field->toRedis($v) );
        }
        
        return $this->executePipeline();
    }
    
}