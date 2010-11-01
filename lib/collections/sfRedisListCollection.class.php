<?php

/** @RedisList */
class sfRedisListCollection extends sfRedisCollection
{
    
    public function shift() {
        if($this->isPersisted())
            return $this->getField()->fromRedis( $this->getEntity()->shift() );
        else
            return parent::shift();
    }
    
    public function pop() {
        if($this->isPersisted())
            return $this->getField()->fromRedis( $this->getEntity()->pop() );
        else
            return parent::pop();
    }
    
    public function push($value) {
        if($this->isPersisted())
            return $this->getEntity()->push( $this->getField()->toRedis($value) );
        else
            parent::push($value);
    }
    
    public function unshift($value) {
        if($this->isPersisted())
            return $this->getEntity()->unshift( $this->getField()->toRedis($value) );
        else
            parent::unshift($value);
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
            return $this->getField()->fromRedis( $this->getEntity()->offsetGet($offset) );
        else
            return parent::offsetGet($offset);
    }
    
    public function offsetSet($offset, $value) {
        if($this->isPersisted())
            return $this->getEntity()->offsetSet($offset, $this->getField()->toRedis( $this->_data[$offset] ));
        else
            parent::offsetSet($offset, $value);
    }
    
    public function offsetUnset($offset) {
        throw new sfRedisException('Cannot unset offset by index in a RedisList');
    }
    
}