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
    
    protected static $entities       = array('RedisEntity', 'RedisList', 'RedisSet', 'RedisZSet');
    protected static $entity_classes = array(
                                            'hash'	    => 'sfRedisHashEntity',
                                            'string'	=> 'sfRedisStringEntity',
                                            'list'		=> 'sfRedisListEntity',
                                       );
    
    public static function create($connection = 'default') {
        return new sfRedisEntityManager($connection);
    }
    
    public static function getInstance($connection = 'default') {
        return self::create($connection);
    }
    
    public static function getEntitiesList() {
        return self::$entities;
    }
    
    public static function getEntityClass($type) {
        return self::$entity_classes[$type];
    }
    
    private function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function getClient() {
        return sfRedis::getClient($this->conn);
    }
    
    public function persist($obj) {
        $ref = new ReflectionAnnotatedClass($obj);
        
        $entity = null;
        foreach(self::getEntitiesList() as $e)
            if($ref->hasAnnotation($e))
                $entity = $ref->getAnnotation($e);
        
        if($entity === null)
            throw new sfRedisEntityManagerException('Attempting to persist a non-redis entity');
        
        if(!($obj instanceof sfRedisObject) && !($obj instanceof sfRedisCollection))
            throw new sfRedisEntityManagerException('RedisEntity object is not a subclass of sfRedisObject or sfRedisCollection');
            
        $class = $entity->entity;
        
        if(!class_exists($class))
            throw new sfRedisEntityManagerException('RedisEntity specifies a class "'.$class.'" that does not exist');
        
        $entity = new $class($obj);
        
        return $entity->save();
    }
    
}

class sfRedisEntityManagerException extends sfRedisException
{
}