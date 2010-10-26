<?php

class sfRedisListEntity extends sfRedisEntity
{
    
    public function __construct(sfRedisCollection $collection = null) {
        $this->value = $collection;
    }
    
    public function getCollection() {
        return $this->getValue();
    }
    
    public function getKey() {
        return $this->getCollection()->getKey();
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
        $value = $this->getClient()->lindex($this->getKey(), $index);
        return $this->load_value($value, $this->getCollection()->getMeta()->has_type, $this->getCollection()->getMeta()->has);
    }
    
    public function pop() {
        $value = $this->getClient()->rpop($this->getKey());
        return $this->load_value($value);
    }
    
    public function shift() {
        $value = $this->getClient()->lpop($this->getKey());
        return $this->load_value($value);
    }
    
    public function push($v) {
        $meta = $this->getCollection()->getMeta();
        $v = $this->save_value($v, $meta->has_type, $meta->has);
        return $this->getClient()->rpush($this->getKey(), $v);
    }
    
    public function unshift($v) {
        $meta = $this->getCollection()->getMeta();
        $v = $this->save_value($v, $meta->has_type, $meta->has);
        return $this->getClient()->lpush($this->getKey(), $v);
    }
    
    public function save(Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        
        if(!($client instanceof Predis_CommandPipeline))
            $this->pipeline();
        
        $key  = $this->getKey();
        $data = $this->getCollection()->getData();
        $meta = $this->getCollection()->getMeta();
        
        foreach($data as $v) {
            $this->push($v);
        }
        
        return $this->executePipeline();
    }
    
}