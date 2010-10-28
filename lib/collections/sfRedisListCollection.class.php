<?php

/** @RedisList */
class sfRedisListCollection extends sfRedisCollection
{
    
    protected function _shift() {
        return $this->getEntity()->shift();
    }
    
    protected function _pop() {
        return $this->getEntity()->pop();
    }
    
    protected function _push($value) {
        return $this->getEntity()->push( $this->getField()->toRedis($value) );
    }
    
    protected function _unshift($value) {
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
        return $this->getEntity()->offsetGet($offset);
    }
    
    public function offsetSet($offset, $value) {
        return $this->getEntity()->offsetSet($offset, $value);
    }
    
    public function offsetUnset($offset) {
        throw new sfRedisException('Cannot unset offset by index in a RedisList');
    }
    
}