<?php

/** @RedisList */
class sfRedisListCollection extends sfRedisCollection implements ArrayAccess
{
    
    public function shift() {
        if($this->isPersisted())
            return $this->getField()->fromRedis( $this->getEntity()->shift() );
        else
            return array_shift($this->_data);
    }
    
    public function pop() {
        if($this->isPersisted())
            return $this->getField()->fromRedis( $this->getEntity()->pop() );
        else
            return array_pop($this->_data);
    }
    
    public function push($value) {
        if($this->isPersisted())
            return $this->getEntity()->push( $this->getField()->toRedis($value) );
        else
            array_push($this->_data, $value);
    }
    
    public function unshift($value) {
        if($this->isPersisted())
            return $this->getEntity()->unshift( $this->getField()->toRedis($value) );
        else
            array_unshift($this->_data, $value);
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
            return isset($this->_data[$offset]);
    }
    
    public function offsetGet($offset) {
        if($this->isPersisted())
            return $this->getField()->fromRedis( $this->getEntity()->offsetGet($offset) );
        else
            return $this->_data[$offset];
    }
    
    public function offsetSet($offset, $value) {
        if($this->isPersisted())
            return $this->getEntity()->offsetSet($offset, $this->getField()->toRedis( $this->_data[$offset] ));
        else
            $this->_data[$offset] = $value;
    }
    
    public function offsetUnset($offset) {
        throw new sfRedisException('Cannot unset offset by index in a RedisList');
    }
    
}