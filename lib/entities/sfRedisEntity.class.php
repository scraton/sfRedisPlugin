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
    
    public static function getType($key) {
        return sfRedisEntityManager::getInstance()->getClient()->type($key);
    }
    
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
    
    
    public function getValue() {
        return $this->value;
    }
    
    public function delete() {
        $client = ($client) ? $client : $this->getManager()->getClient();
        $client->del($this->getKey());
    }
    
    protected function load_value($value, $type = 'string', $is_a = null) {
        switch($type) {
            case 'relation':
            case 'object':
                if(class_exists($is_a))
                    $value = new $is_a($value);
                else
                    throw new sfRedisException('Attempting to load non-existent class `'.$is_a.'`');
                break;
                
            case 'string':
            default:
                break;
        }
        
        return $value;
    }
    
    protected function save_value($value, $type = 'string', $is_a = null) {
        switch($type) {
            case 'relation':
            case 'object':
                $this->getManager()->persist($value);
                $value = $value->getKey();
                break;
                
            case 'list':
            case 'set':
            case 'zset':
                $this->getManager()->persist($value);
                $value = $value->getKey();
                break;
                
            case 'datetime':
                // TODO: timezone conversion
                break;
                
            case 'string':
            default:
                break;
        }
        
        if(is_object($value))
            throw new sfRedisException('Value was not serialized in `'.get_class($this).'`');
        
        return $value;
    }
    
    abstract public function getKey();
    abstract public function save();
    abstract public function associate($obj);
    
}