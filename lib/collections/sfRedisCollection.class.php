<?php

abstract class sfRedisCollection implements Countable, IteratorAggregate, Serializable, ArrayAccess
{
    
    protected $key;
    protected $data = array();
    
    public function __construct($key = null) {
        $this->key = $key;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getKey() {
        return $this->key;
    }
    
    public function shift() {
        return array_shift($this->data);
    }
    
    public function pop() {
        return array_pop($this->data);
    }
    
    public function push($value) {
        array_push($this->data, $value);
    }
    
    public function unshift($value) {
        array_unshift($this->data, $value);
    }
    
    public function getIterator() {
        $data = $this->data;
        return new ArrayIterator($data);
    }
    
    public function count() {
        return count($this->data);
    }

    public function serialize() {
        return serialize($this->data);
    }

    public function unserialize($serialized) {
        $this->data = unserialize($serialized);
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value) {
        return $this->data[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    
}