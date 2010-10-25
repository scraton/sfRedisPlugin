<?php

abstract class sfRedisEntity
{
    
    const TYPE_NONE   = 'none';
    const TYPE_STRING = 'string';
    const TYPE_LIST   = 'list';
    const TYPE_SET    = 'set';
    const TYPE_ZSET   = 'zset';
    const TYPE_HASH   = 'hash';
    
    /**
     * @var sfRedisEntityManager
     */
    protected $em    = null;
    protected $key   = null;
    protected $value = null;
    
    private $_pipe   = false;
    
    public function __construct(sfRedisEntityManager $em, sfRedisObject $object = null) {
        $this->em    = $em;
        $this->value = $object;
    }
    
    /**
     * @return sfRedisEntityManager
     */
    public function getManager() {
        return $this->em;
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
    
    public function getKey() {
        return $this->getObject()->getKey();
    }
    
    public function getObject() {
        return $this->value;
    }
    
    public function getValue() {
        return serialize($this->getObject());
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