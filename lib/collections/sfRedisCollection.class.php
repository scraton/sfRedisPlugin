<?php

abstract class sfRedisCollection extends sfRedisAbstract implements Countable, Iterator
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
    
    public function count() {
        return count($this->_data);
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