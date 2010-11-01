<?php

abstract class sfRedisCollection extends sfRedisAbstract implements Countable, Iterator, ArrayAccess
{
    
    protected $_field;
    protected $_position = 0;
    
    protected function loadMeta() {
        parent::loadMeta();
    }
    
    /**
     * @return sfRedisField
     */
    public function getField() {
        if(!$this->_field)
            if(class_exists($this->getMeta()->has)) {
                $this->_field       = new sfRedisRelationField($this->getKey());
                $this->_field->is_a = $this->getMeta()->has;
            } else 
                $this->_field       = new sfRedisField($this->getKey());
        
        return $this->_field;
    }
    
    public function shift() {
        return array_shift($this->_data);
    }
    
    public function pop() {
        return array_pop($this->_data);
    }
    
    public function push($value) {
        array_push($this->_data, $value);
    }
    
    public function unshift($value) {
        array_unshift($this->_data, $value);
    }
    
    public function count() {
        return count($this->_data);
    }
    
    public function offsetExists($offset) {
        return isset($this->_data[$offset]);
    }
    
    public function offsetGet($offset) {
        return $this->_data[$offset];
    }
    
    public function offsetSet($offset, $value) {
        $this->_data[$offset] = $value;
    }
    
    public function offsetUnset($offset) {
        unset($this->_data[$offset]);
    }
    
    public function current() {
        return $this->offsetGet($this->_position);
    }
    
    public function key() {
        return $this->_position;
    }
    
    public function next() {
        ++$this->_position;
    }
    
    public function rewind() {
        $this->_position = 0;
    }
    
    public function valid() {
        return $this->offsetExists($this->_position);
    }
    
}