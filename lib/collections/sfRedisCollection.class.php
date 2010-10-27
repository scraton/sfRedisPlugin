<?php

abstract class sfRedisCollection extends sfRedisAbstract implements Countable, IteratorAggregate, ArrayAccess
{
    
    public function shift() {
        $ret = array_shift($this->_data);
        
        if($this->isPersisted())
            return $this->_shift();
        
        return $ret;
    }
    
    abstract protected function _shift();
    
    public function pop() {
        $ret = array_pop($this->_data);
        
        if($this->isPersisted())
            return $this->_pop();
        
        return $ret;
    }
    
    abstract protected function _pop();
    
    public function push($value) {
        array_push($this->_data, $value);
        
        if($this->isPersisted())
            $this->_push($value);
    }
    
    abstract protected function _push($value);
    
    public function unshift($value) {
        array_unshift($this->_data, $value);
        
        if($this->isPersisted())
            $this->_unshift($value);
    }
    
    abstract protected function _unshift($value);
        
}