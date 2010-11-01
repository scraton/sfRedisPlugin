<?php

abstract class sfRedisCollection extends sfRedisAbstract implements Countable, IteratorAggregate, ArrayAccess
{
    
    protected $_field;
    
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
        
}