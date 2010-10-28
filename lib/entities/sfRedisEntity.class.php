<?php

abstract class sfRedisEntity
{
    
    const INCID_KEY   = '%s:_inc_id';
    
    const TYPE_NONE   = 'none';
    const TYPE_STRING = 'string';
    const TYPE_LIST   = 'list';
    const TYPE_SET    = 'set';
    const TYPE_ZSET   = 'zset';
    const TYPE_HASH   = 'hash';
    
    protected $value = null;
    
    private $_pipe   = false;
    
    public static function getType($key) {
        return sfRedisEntityManager::getInstance()->getClient()->type($key);
    }
    
    public function __construct(sfRedisAbstract $obj) {
        $this->value = $obj;
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
    
    public function getKey() {
        if(!method_exists($this->value, 'getKey'))
            throw new sfRedisException('Cannot get key for object `'.get_class($this->value).'`');
        
        return $this->value->getKey();
    }
    
    public function getValue() {
        return $this->value;
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
                $value = $value->getIndex();
                break;
                
            case 'list':
            case 'set':
            case 'zset':
                if(!$value->isPersisted())
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
    
    protected function incrId() {
        return $this->getManager()->getClient()->incr(sprintf(self::INCID_KEY, get_class($this->value)));
    }
    
    public function save() {
        if($this->getKey() === null)
            throw new sfRedisException('Attempting to save `'.get_class($this->getObject()).'` with null key');
            
        $this->getValue()->prePersist();
            
        $ret = $this->_save();
        
        $this->getValue()->postPersist();
        
        return $ret;
    }
    
    abstract protected function _save();
    abstract public function associate($obj);
    
    public function delete() {
        $client = ($client) ? $client : $this->getManager()->getClient();
        $client->del($this->getKey());
    }
    
}