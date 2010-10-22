<?php

/**
 * sfRedisEntityManager
 *
 * @package   sfRedisPlugin
 * @author    Stephen Craton <scraton@gmail.com>
 * @license   The MIT License
 * @version   SVN: $Id$
 */
class sfRedisEntityManager
{
    
    protected static $entities = array('RedisEntity', 'RedisList', 'RedisSet', 'RedisZSet');
    protected static $entity_classes = array(
                                            'hash'	    => 'sfRedisHashEntity',
                                            'string'	=> 'sfRedisStringEntity',
                                       );
    
    public static function create($connection = 'default') {
        return new sfRedisEntityManager($connection);
    }
    
    private function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function getClient() {
        return sfRedis::getClient($this->conn);
    }
    
    public function retrieveByKey($key) {
        $type = $this->getClient()->type($key);
        
        if(!isset(self::$entity_classes[$type]))
            throw new sfRedisEntityManagerException('Unknown key type '.$type);
            
        $class = self::$entity_classes[$type];
            
        if(!class_exists($class))
            throw new sfRedisEntityManagerException('Unable to load class '.$class.' for key type '.$type);
            
        $entity = new $class($this);
        
        return $entity->load($key);
    }
    
    public function persist($obj) {
        $ref = new ReflectionAnnotatedClass($obj);
        
        $entity = null;
        foreach(self::$entities as $e)
            if($ref->hasAnnotation($e))
                $entity = $ref->getAnnotation($e);
        
        if($entity === null)
            throw new sfRedisEntityManagerException('Attempting to persist a non-redis entity');
            
        if(!$ref->isSubclassOf('sfRedisObject'))
            throw new sfRedisEntityManagerException('RedisEntity object is not a subclass of sfRedisObject');
            
        $class = $entity->class;
        
        if(!class_exists($class))
            throw new sfRedisEntityManagerException('RedisEntity specifies a class "'.$class.'" that does not exist');
        
        $entity = new $class($this, $obj);
        
        return $entity->save();
    }
    
}

class sfRedisEntityManagerException extends Exception
{
}