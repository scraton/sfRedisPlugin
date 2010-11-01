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
        $cnt = count($this->_data);
        if($this->isPersisted())
            $cnt = $this->getEntity()->count();
        return $cnt;
    }
    
    public function offsetExists($offset) {
        return ($this->getEntity()->get($offset) !== null);
    }
    
    public function offsetGet($offset) {
        return $this->getField()->fromRedis( $this->getEntity()->offsetGet($offset) );
    }
    
    public function offsetSet($offset, $value) {
        return $this->getEntity()->offsetSet($offset, $this->getField()->toRedis($value));
    }
    
    public function offsetUnset($offset) {
        throw new sfRedisException('Cannot unset offset by index in a RedisList');
    }
    
}