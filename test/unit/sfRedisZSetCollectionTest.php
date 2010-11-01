<?php

/**
 * sfRedis tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(27, new lime_output_color());

sfRedis::getClient()->flushdb();

// should handle adding data

    $t->comment('should handle adding data');
    
    $set = new sfRedisZSetCollection();
    
    $set->add(1, 'bob.saget', 2, 'joe.user');
    
    $t->is(count($set), 2, '->add() added the correct number of members in the set');
    
    unset($set);
    
// should handle removing data

    $t->comment('should handle removing data');
    
    $set = new sfRedisZSetCollection();
    
    $set->add(1, 'bob.saget', 2, 'joe.user', 3, 'sally.sue');
    $set->remove('sally.sue');
    
    $t->is(count($set), 2, '->remove() removed the correct number of members in the set');
    
    unset($set);
    
// should handle determining if stuff is in the set

    $t->comment('should handle determining if stuff is in the set');
    
    $set = new sfRedisZSetCollection();
    
    $set->add(1, 'bob.saget', 1, 'joe.user', 2, 'sally.sue');
    
    $t->is($set->isMember('bob.saget'), true, '->isMember() tells us that bob.saget is in the set');
    $t->is($set->isMember('omg.wtfbbq'), false, '->isMember() tells us that omg.wtfbbq is NOT in the set');
    
    unset($set);
    
// should handle adding and removing objects

    require_once dirname(__FILE__).'/../fixtures/objects.php';

    $t->comment('should handle adding and removing objects');
    
    $set = new UserZSet('test:set');
    
    $user1 = new User();
    $user1->nickname = 'bob.saget';
    
    $user2 = new User();
    $user2->nickname = 'joe.user';
    
    $user3 = new User();
    $user3->nickname = 'sally.sue';
    
    $set->add(100, $user1, 101, $user2);
    
    sfRedisEntityManager::create()->persist($set);
    
    unset($set);
    
    $set = new UserZSet('test:set');
    
    $t->is($set->isMember($user1), true, '->isMember() says that the user is part of the set');
    $t->is($set->isMember($user3), false, '->isMember() says that the user is NOT part of the set');
    
    $t->is(count($set), 2, '->count() returns the number of members in the set');
    
    $set->add(101, $user3);
    
    $t->is(count($set), 3, '->add() and ->count() both work after the object has been persisted');
    
    $t->ok($set->remove($user1) && !$set->isMember($user1), '->remove() removed the user from the set');
    
    sfRedis::getClient()->flushdb();
    
// should be able to be traversed

    $t->comment('should be able to be traversed');
    
    $set = new sfRedisZSetCollection('test:set');
    
    $set->add(1, 'bob.saget', 1, 'joe.user', 3, 'sally.sue');
    
    sfRedisEntityManager::create()->persist($set);
    
    unset($set);
    
    $set = new sfRedisZSetCollection('test:set');
    
    // expected scores, in order
    $scores = array(0 => 1, 1 => 1, 2 => 3);
    $j      = 0;
    
    try {
        // should iterate 3 times
        foreach($set as $score => $user) {
            $t->pass('foreach set as user');
            $t->is($score, $scores[$j], 'foreach gives us the score of the current value');
            
            $j++;
        }
    } catch(Exception $e) {
        $t->fail('foreach set as user');
    }
    
    unset($set);
    
    sfRedis::getClient()->flushdb();
    
// should be able to get the score given a member

    $t->comment('should be able to get the score given a member');
    
    $set = new sfRedisZSetCollection('test:set');
    
    $set->add(50, '1950', 40, '1940', 30, '1930', 20, '1920', 10, '1910');
    
    $t->is($set->score('1950'), 50, '->score() returns the score for a given element');
    
    sfRedisEntityManager::create()->persist($set);
    unset($set);
    
    $set = new sfRedisZSetCollection('test:set');
    
    $t->is($set->score('1950'), 50, '->score() returns the score for a given element after being persisted');
    
    unset($set);
    
    sfRedis::getClient()->flushdb();
    
// should be able to remove members by score and rank

    $t->comment('should be able to remove members by score and rank');
    
    $set = new sfRedisZSetCollection('test:set');
    
    $set->add(50, '1950', 40, '1940', 30, '1930', 20, '1920', 10, '1910');
    
    sfRedisEntityManager::create()->persist($set);
    unset($set);
    
    $set = new sfRedisZSetCollection('test:set');
    
    $set->removeByScore(30, 50);
    
    $t->is($set->getMembers(), array('1910', '1920'), '->removeByScore() removed the elements within the range of scores');
    
    $set->removeByRank(0, 0);
    
    $t->is($set->getMembers(), array('1920'), '->removeByRank() removed the elements within the range of ranks');
    
    unset($set);
    
    sfRedis::getClient()->flushdb();
    
// should be able to get data in ranges based on score

    $t->comment('should be able to get data in ranges based on score');
    
    $set = new sfRedisZSetCollection('test:set');
    
    $set->add(50, '1950', 40, '1940', 30, '1930', 20, '1920', 10, '1910');
    
    sfRedisEntityManager::create()->persist($set);
    unset($set);
    
    $set = new sfRedisZSetCollection('test:set');
    
    $t->is($set->rangeByScore(30, 50), array('1930', '1940', '1950'), '->range() returns a range of elements given the scores min to max');
    
    $t->is($set->rangeByScore(30, 50, 0, 1), array('1930'), '->range() returns only the first result when count is 1');
    $t->is($set->rangeByScore(30, 50, 1, 1), array('1940'), '->range() returns only the second result when count is 1 and offset is 1');
    
    unset($set);
    
    sfRedis::getClient()->flushdb();
    
// should be able to determine rank

    $t->comment('should be able to determine rank');
    
    $set = new sfRedisZSetCollection('test:set');
    
    $set->add(50, '1950', 40, '1940', 30, '1930', 20, '1920', 10, '1910');
    
    sfRedisEntityManager::create()->persist($set);
    unset($set);
    
    $set = new sfRedisZSetCollection('test:set');
    
    $t->is($set->rank('1950'), 4, '->rank() returns the correct rank when sorted low to high');
    $t->is($set->revRank('1950'), 0, '->revRank() returns the correct rank when sorted high to low');
    
    unset($set);
    
    sfRedis::getClient()->flushdb();
    
// should be able to get in ranges based on rank

    $t->comment('should be able to get data in ranges based on rank');
    
    $set = new sfRedisZSetCollection('test:set');
    
    $set->add(50, '1950', 40, '1940', 30, '1930', 20, '1920', 10, '1910');
    
    sfRedisEntityManager::create()->persist($set);
    unset($set);
    
    $set = new sfRedisZSetCollection('test:set');
    
    $t->is($set->rangeByRank(0, 2), array('1910', '1920', '1930'), '->rangeByRank() returns the correct members when sorted low to high');
    $t->is($set->rangeByRevRank(0, 2), array('1950', '1940', '1930'), '->rangeByRevRank() returns the correct members when sorted high to low');
    
    unset($set);
    
    sfRedis::getClient()->flushdb();
    
// should be able to increment a member's score

    $t->comment('should be able to increment a member\'s score');
    
    $set = new sfRedisZSetCollection('test:set');
    
    $set->add(50, '1950', 40, '1940', 30, '1930', 20, '1920', 10, '1910');
    
    sfRedisEntityManager::create()->persist($set);
    unset($set);
    
    $set = new sfRedisZSetCollection('test:set');
    
    $set->incrBy(5, '1950');
    
    $t->is($set->score('1950'), 55, '->incrBy() increments a member\'s score');
    
    unset($set);
    
    sfRedis::getClient()->flushdb();