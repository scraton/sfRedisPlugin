<?php

/** @RedisSet */
class sfRedisSetCollection extends sfRedisCollection
{
    
    public function add() {
        $values = func_get_args();
        
        if($this->isPersisted()) {
            $this->getEntity()->pipeline();
            
            foreach($values as $value)
                $this->getEntity()->add( $this->getField()->toRedis($value) );
                
            return $this->getEntity()->executePipeline();
        } else
            return ($this->_data = array_merge($this->_data, $values));
    }
    
    public function isMember($value) {
        if($this->isPersisted())
            return $this->getEntity()->isMember( $this->getField()->toRedis($value) );
        else
            return in_array($value, $this->_data);
    }
    
    public function remove($value) {
        if($this->isPersisted())
            return $this->getEntity()->remove( $this->getField()->toRedis($value) );
        else {
            $key = array_search($value, $this->_data);
            unset($this->_data[$key]);
        }
    }
    
    public function count() {
        if($this->isPersisted())
            return $this->getEntity()->count();
        else
            return parent::count();
    }
    
    public function getMembers() {
        return $this->getData();
    }
    
}