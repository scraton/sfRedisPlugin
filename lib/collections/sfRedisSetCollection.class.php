<?php

/** @RedisSet */
class sfRedisSetCollection extends sfRedisCollection
{
    
    // use to iterate through this set
    private $_iterator;
    
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
        if($this->isPersisted()) {
            $members = $this->getEntity()->getMembers();
            if(is_array($members))
                foreach($members as $i => $member) {
                    $members[$i] = $this->getField()->fromRedis($member);
                }
            return $members;
        } else
            return $this->getData();
    }
    
    private function initIterator() {
        $this->_data     = $this->getMembers();
        $this->_iterator = new ArrayIterator($this->_data);
    }
    
    public function current() {
        if(!$this->_iterator)
            $this->initIterator();
        return $this->_iterator->current();
    }
    
    public function key() {
        if(!$this->_iterator)
            $this->initIterator();
        return $this->_iterator->key();
    }
    
    public function next() {
        if(!$this->_iterator)
            $this->initIterator();
        return $this->_iterator->next();
    }
    
    public function rewind() {
        // we defo want to reset the iterator when rewinding
        $this->initIterator();
        
        return $this->_iterator->rewind();
    }
    
    public function valid() {
        if(!$this->_iterator)
            $this->initIterator();
        return $this->_iterator->valid();
    }
    
}