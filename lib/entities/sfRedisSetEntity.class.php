<?php

class sfRedisSetEntity extends sfRedisEntity
{
    
    public function getSet() {
        return $this->getValue();
    }
    
    public function add($member) {
        return $this->getClient()->sadd($this->getKey(), $member);
    }
    
    public function remove($member) {
        return $this->getClient()->srem($this->getKey(), $member);
    }
    
    public function pop() {
        return $this->getClient()->spop($this->getKey());
    }
    
    public function count() {
        return $this->getClient()->scard($this->getKey());
    }
    
    public function isMember($member) {
        return $this->getClient()->sismember($this->getKey(), $member);
    }
    
    public function getMembers() {
        return $this->getClient()->smembers($this->getKey());
    }

    protected function _save() {
        if(!($this->getClient() instanceof Predis_CommandPipeline))
            $this->pipeline();
        
        $key   = $this->getKey();
        $data  = $this->getSet()->getData();
        $meta  = $this->getSet()->getMeta();
        $field = $this->getSet()->getField();
        
        foreach($data as $v) {
            $this->add( $field->toRedis($v) );
        }
        
        return $this->executePipeline();
    }

	public function associate($obj) {
	    $key = $obj->getKey();
        
        if($key !== null) {
            $type = self::getType($key);
            
            if($type == self::TYPE_NONE)
                $obj->isPersisted(false);
            else if($type != self::TYPE_SET)
                throw new sfRedisException('Attempting to associate a `'.$type.'` with a `'.self::TYPE_SET.'`');
            else 
                $obj->isPersisted(true);
        }
    }
    
}