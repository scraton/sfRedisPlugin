<?php

class sfRedisCollection implements Countable, IteratorAggregate, Serializable
{
    
    protected $data = array();
    
    public function __construct($data = array()) {
        if(is_array($data))
            $this->data = $data;
    }
    
    public function getData() {
        return $this->data;
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
    
}