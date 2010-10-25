<?php

class sfRedisHashEntity extends sfRedisEntity
{
    
    /**
     * @var sfRedisObject
     */
    protected $value;
    
    public function __construct(sfRedisObject $obj) {
        $this->value = $obj;
    }
    
    public function getObject() {
        return $this->value;
    }
    
    public function getKey() {
        return $this->getObject()->getKey();
    }
    
    public function getType() {
        $type = parent::getType();
        
        if($type != sfRedisEntity::TYPE_HASH)
            return false;
        
        return $this->getClient()->hget($this->getKey(), '_obj');
    }
    
    public function get(RedisField $field) {
        $value = $this->getClient()->hget($this->getKey(), $field->name);
        
        switch($field->type) {
            case 'relation':
                $class = $field->is_a;
                $value = new $class($value);
                break;
                
            case 'string':
            default:
                break;
        }
        
        return $value;
    }
    
    public function set(RedisField $field, $value) {
        switch($field->type) {
            case 'relation':
                $this->getManager()->persist($value);
                $value = $value->getKey();
                break;
                
            case 'string':
            default:
                break;
        }
        
        return $this->getClient()->hset($this->getKey(), $field->name, $value);
    }
    
    public function save(Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        
        if(!($client instanceof Predis_CommandPipeline))
            $this->pipeline();
        
        $key    = $this->getKey();
        $data   = $this->getObject()->getData();
        
        foreach($this->getObject()->getFields() as $field) {
            $k = $field->name;
            $v = $data[$k];
            
            $this->set($field, $v);
        }
        
        $this->getClient()->hset($this->getKey(), '_obj', get_class($this->getObject()));
        
        return $this->executePipeline();
    }
    
}