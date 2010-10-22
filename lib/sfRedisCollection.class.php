<?php

abstract class sfRedisCollection implements Countable, IteratorAggregate, Serializable
{
    
    protected $data = array();
    
    public function __construct() {
    }
    
    public function getIterator() {
        $data = $this->data;
        return new ArrayIterator($data);
    }
    
    public function add($v, $key = null) {
        if($key !== null)
            $this->data[$key] = $v;
        else
            array_push($this->data, $v);
    }
    
    public function merge(Redis_Collection $rc) {
        $this->data = array_merge($this->data, $rc->getData());
        return $this->data;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function save() {
        $client = sfRedis::getClient();
        $pipe   = $client->pipeline();
        foreach($this->data as $o)
            $o->save($pipe);
        $pipe->execute();
    }
    
    public function count() {
        return count($this->data);
    }

    public function serialize() {
        // TODO Auto-generated method stub
    }

    public function unserialize($serialized) {
        // TODO Auto-generated method stub
    }

    
}