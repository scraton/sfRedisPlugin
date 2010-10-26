<?php

/** @RedisList */
class sfRedisListCollection extends sfRedisCollection
{
    
    public function __toString() {
        throw new Exception('oops');
    }
    
    public function shift() {
        $val = parent::shift();
        if($this->isPersisted())
            $val = $this->getEntity()->shift();
        return $val;
    }
    
    public function pop() {
        $val = parent::pop();
        if($this->isPersisted())
            $val = $this->getEntity()->pop();
        return $val;
    }
    
    public function push($value) {
        parent::push($value);
        return $this->getEntity()->push($value);
    }
    
    public function unshift($value) {
        parent::unshift($value);
        return $this->getEntity()->unshift($value);
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
        return $this->getEntity()->offsetGet($offset);
    }
    
    public function offsetSet($offset, $value) {
        return $this->getEntity()->offsetSet($offset, $value);
    }
    
    public function offsetUnset($offset) {
        throw new sfRedisException('Cannot unset offset by index in a RedisList');
    }
    
}