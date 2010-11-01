<?php

/** @RedisList */
class sfRedisListCollection extends sfRedisCollection
{
    
    public function shift() {
        $shift = parent::shift();
        if($this->isPersisted())
            return $this->getField()->fromRedis( $this->getEntity()->shift() );
        else
            return $shift;
    }
    
    public function pop() {
        $pop = parent::pop();
        if($this->isPersisted())
            return $this->getField()->fromRedis( $this->getEntity()->pop() );
        else
            return $pop;
    }
    
    public function push($value) {
        parent::push($value);
        if($this->isPersisted())
            return $this->getEntity()->push( $this->getField()->toRedis($value) );
    }
    
    public function unshift($value) {
        parent::unshift($value);
        if($this->isPersisted())
            return $this->getEntity()->unshift( $this->getField()->toRedis($value) );
    }
    
    public function getIterator() {
        $data = $this->_data;
        return new ArrayIterator($data);
    }

    public function count() {
        if($this->isPersisted())
            return $this->getEntity()->count();
        else
            return parent::count();
    }
    
    public function offsetExists($offset) {
        if($this->isPersisted())
            return ($this->getEntity()->offsetGet($offset) !== null);
        else
            return parent::offsetExists($offset);
    }
    
    public function offsetGet($offset) {
        if($this->isPersisted())
            $this->_data[$offset] = $this->getField()->fromRedis( $this->getEntity()->offsetGet($offset) );
        return parent::offsetGet($offset);
    }
    
    public function offsetSet($offset, $value) {
        parent::offsetSet($offset, $value);
        
        if($this->isPersisted())
            return $this->getEntity()->offsetSet($offset, $this->getField()->toRedis( $this->_data[$offset] ));
    }
    
    public function offsetUnset($offset) {
        throw new sfRedisException('Cannot unset offset by index in a RedisList');
    }
    
}