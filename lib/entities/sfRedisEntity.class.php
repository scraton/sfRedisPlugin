<?php

abstract class sfRedisEntity
{
    
    const TYPE_NONE   = 'none';
    const TYPE_STRING = 'string';
    const TYPE_LIST   = 'list';
    const TYPE_SET    = 'set';
    const TYPE_ZSET   = 'zset';
    const TYPE_HASH   = 'hash';
    
    protected $key   = null;
    protected $value = null;
    
    private $_pipe   = false;
    
    /**
     * @return sfRedisEntityManager
     */
    public function getManager() {
        return sfRedisEntityManager::getInstance();
    }
    
    public function getClient() {
        if(!$this->_pipe)
            return $this->getManager()->getClient();
        else
            return $this->_pipe;
    }
    
    public function pipeline() {
        $this->_pipe = $this->getClient()->pipeline();
    }
    
    public function executePipeline() {
        $ret = $this->_pipe->execute();
        $this->_pipe = false;
        return $ret;
    }
    
    abstract public function getKey();
    
    public function getValue() {
        return $this->value;
    }
    
    public function getType() {
        return $this->getManager()->getClient()->type($this->getKey());
    }
    
    abstract public function save(Predis_Client $client = null);
    
    public function delete(Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        $client->del($this->getKey());
    }
    
}