<?php

/**
 * sfRedis tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(12, new lime_output_color());

sfRedis::getClient()->flushdb();

// should handle adding data

    $t->comment('should handle adding data');
    
    $set = new sfRedisSetCollection();
    
    $set->add('bob.saget', 'joe.user');
    
    $t->is(count($set), 2, '->add() added the correct number of members in the set');
    
    unset($set);
    
// should handle removing data

    $t->comment('should handle removing data');
    
    $set = new sfRedisSetCollection();
    
    $set->add('bob.saget', 'joe.user', 'sally.sue');
    $set->remove('sally.sue');
    
    $t->is(count($set), 2, '->remove() removed the correct number of members in the set');
    
    unset($set);
    
// should handle determining if stuff is in the set

    $t->comment('should handle determining if stuff is in the set');
    
    $set = new sfRedisSetCollection();
    
    $set->add('bob.saget', 'joe.user', 'sally.sue');
    
    $t->is($set->isMember('bob.saget'), true, '->isMember() tells us that bob.saget is in the set');
    $t->is($set->isMember('omg.wtfbbq'), false, '->isMember() tells us that omg.wtfbbq is NOT in the set');
    
    unset($set);
    
// should handle adding and removing objects

    require_once dirname(__FILE__).'/../fixtures/objects.php';

    $t->comment('should handle adding and removing objects');
    
    $set = new UserSet('test:set');
    
    $user1 = new User();
    $user1->nickname = 'bob.saget';
    
    $user2 = new User();
    $user2->nickname = 'joe.user';
    
    $user3 = new User();
    $user3->nickname = 'sally.sue';
    
    $set->add($user1, $user2);
    
    sfRedisEntityManager::create()->persist($set);
    
    unset($set);
    
    $set = new UserSet('test:set');
    
    $t->is($set->isMember($user1), true, '->isMember() says that the user is part of the set');
    $t->is($set->isMember($user3), false, '->isMember() says that the user is NOT part of the set');
    
    $t->is(count($set), 2, '->count() returns the number of members in the set');
    
    $set->add($user3);
    
    $t->is(count($set), 3, '->add() and ->count() both work after the object has been persisted');
    
    $t->ok($set->remove($user1) && !$set->isMember($user1), '->remove() removed the user from the set');
    
    sfRedis::getClient()->flushdb();
    
// should be able to be traversed

    $t->comment('should handle adding and removing objects');
    
    $set = new sfRedisSetCollection('test:set');
    
    $set->add('bob.saget', 'joe.user', 'sally.sue');
    
    sfRedisEntityManager::create()->persist($set);
    
    unset($set);
    
    $set = new sfRedisSetCollection('test:set');
    
    try {
        // should iterate 3 times
        foreach($set as $user) {
            $t->pass('foreach set as user');
        }
    } catch(Exception $e) {
        $t->fail('foreach set as user');
    }
    
    unset($set);
    
    sfRedis::getClient()->flushdb();