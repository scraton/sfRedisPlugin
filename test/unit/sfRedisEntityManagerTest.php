<?php

/**
 * sfRedis tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(7, new lime_output_color());

require_once dirname(__FILE__).'/../fixtures/objects.php';

// persist should fail when trying to persist a non-redis entity

    $t->comment('persist should fail when trying to persist a non-redis entity');

    $em = sfRedisEntityManager::create();
    $dt = new DateTime();
    
    try {
        $em->persist($dt);
        $t->fail('->persist() should fail when trying to persist a non-redis entity');
    } catch(sfRedisEntityManagerException $e) {
        $t->pass('->persist() should fail when trying to persist a non-redis entity');
    }
    
// persist should be successful when trying to persist a redis entity

    $t->comment('persist should be successful when trying to persist a redis entity');

    $em   = sfRedisEntityManager::create();
    $user = new User();
    
    $user->nickname = 'bobuser';
    
    try {
        $t->ok($em->persist($user), '->persist() is successful when persisting a redis entity');
    } catch(sfRedisEntityManagerException $e) {
        $t->fail('->persist() is successful when persisting a redis entity');
        throw $e;
    }
    
    sfRedis::getClient()->flushdb();
    
// should be able to load an object by key

    $t->comment('should be able to load by a key');
    
    $em   = sfRedisEntityManager::create();
    $user = new User();
    
    $user->key      = 'user:bobuser';
    $user->nickname = 'bobuser'; 
    
    $em->persist($user);
    
    unset($user);
    
    $t->isa_ok($em->retrieveByKey('user:bobuser'), 'User', '->retrieveByKey() returns a found User object');
    $t->is($em->retrieveByKey('user:bobuser')->nickname, 'bobuser', '->retrieveByKey() returns the correct User object');
    
    sfRedis::getClient()->flushdb();
    
// should be able to store a StringEntity just the same as a HashEntity

    $t->comment('should be able to store a StringEntity just the same as a HashEntity');
    
    $em   = sfRedisEntityManager::create();
    $user = new UserString();
    
    $user->key      = 'user:testuser';
    $user->nickname = 'testuser';
    
    try {
        $t->ok($em->persist($user), '->persist() is successful persisting an entity as a string');
    } catch(sfRedisEntityManagerException $e) {
        $t->fail('->persist() is successful when persisting an entity as a string');
        throw $e;
    }
    
    unset($user);
    
    $t->isa_ok($em->retrieveByKey('user:testuser'), 'UserString', '->retrieveByKey() returns a found User object');
    $t->is($em->retrieveByKey('user:testuser')->nickname, 'testuser', '->retrieveByKey() returns the correct User object');
    
    