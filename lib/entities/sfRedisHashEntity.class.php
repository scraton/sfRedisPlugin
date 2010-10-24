<?php

class sfRedisHashEntity extends sfRedisEntity
{
    
    public function load($key, Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        
        $data = $client->hgetall($key);
        
        if(!class_exists($data['_obj']))
            return false;
            
        $obj = new $data['_obj'];
        
        $this->hydrate(&$obj, $data);
        
        return $obj;
    }
    
    protected function hydrate($obj, $data = array()) {
        unset($data['_obj']);
        
        foreach($obj->getFields() as $field) {
            if(!isset($data[ $field['name'] ]))
                continue;
                
            $k = $field['name'];
            $v = &$data[$k];
            
            switch($field['type']) {
                case 'relation':
                    $v_obj = $this->getManager()->retrieveByKey($v);
                    if(get_class($v_obj) == $field['is_a'])
                        $v = $v_obj;
                    else
                        $v = null;
                    break;
                
                case 'string':
                default:
                    continue;
            }
        }
        
        $obj->setData($data);
    }
    
    public function save(Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        
        $key    = $this->getKey();
        $data   = $this->getObject()->getData();
        
        foreach($data as $k => $v) {
            if($v instanceof sfRedisObject) {
                $this->getManager()->persist($v);
                $data[$k] = $v->getKey();
            }
        }
        
        $data['_obj'] = get_class($this->getObject());
        
        return $client->hmset($key, $data);
    }
    
    public function delete(Predis_Client $client = null) {
        $client = ($client) ? $client : $this->getManager()->getClient();
        $client->del($this->getKey());
    }
    
}