<?php

/**
 * sfRedis tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(18, new lime_output_color());

sfRedis::getClient()->flushdb();

// should be able to push and unshift data to the collection

    $t->comment('should be able to push and unshift data to the collection');
    
    $collection = new sfRedisListCollection();
    
    $collection->push('tag 1');
    
    $t->is($collection[0], 'tag 1', '->push() can push data into the collection');
    
    $collection->unshift('tag 2');
    
    $t->is($collection[0], 'tag 2', '->unshift() can push data into the collection');
    
    unset($collection);
    sfRedis::getClient()->flushdb();
    
// should be able to pop and shift data on/off the array

    $t->comment('should be able to push and unshift data to the collection');
    
    $collection = new sfRedisListCollection();
    
    $collection->push('tag 1');
    $collection->push('tag 2');
    $collection->push('tag 3');
    $collection->push('tag 4');
    $collection->push('tag 5');
    
    $t->is($collection->pop(), 'tag 5', '->pop() can pop data off the end of the array');
    $t->is($collection->shift(), 'tag 1', '->shift() can shift data off the beginning of the array');
    
    unset($collection);
    sfRedis::getClient()->flushdb();
    
// should be able to iterate over the array of data

    $t->comment('should be able to iterate over the array of data');
    
    $collection = new sfRedisListCollection();
    
    $collection->push('tag 1');
    $collection->push('tag 2');
    $collection->push('tag 3');
    $collection->push('tag 4');
    $collection->push('tag 5');
    
    try {
        foreach($collection as $tag) {
        }
        $t->pass('can iterate over the array of data');
    } catch(Exception $e) {
        $t->fail('can iterate over the array of data');
    }
    
    unset($collection);
    sfRedis::getClient()->flushdb();
    
// should be able to count the data in the array

    $t->comment('should be able to count the data in the array');
    
    $collection = new sfRedisListCollection();
    
    $collection->push('tag 1');
    $collection->push('tag 2');
    $collection->push('tag 3');
    $collection->push('tag 4');
    $collection->push('tag 5');
    
    $t->is($collection->count(), 5, '->count() returns the correct number in the array');
    
    try {
        $t->is(count($collection), 5, 'can count the data in the array with PHP count');
    } catch(Exception $e) {
        $t->fail('can count the data in the array with PHP count');
    }
    
    sfRedis::getClient()->flushdb();
    
// should behave the same, even when persisted

    $t->comment('should behave the same, even when persisted');
    
    $collection = new sfRedisListCollection('test:collection');
    
    $collection->push('tag 1');
    $collection->push('tag 2');
    $collection->push('tag 3');
    $collection->push('tag 4');
    $collection->push('tag 5');
    
    sfRedisEntityManager::create()->persist($collection);
    
    unset($collection);
    
    $collection = new sfRedisListCollection('test:collection');
    
    $t->is(count($collection), 5, '->count() returns the correct number in the array');
    
    try {
        foreach($collection as $tag) {
            // should pass 5 times
            $t->pass('can do a foreach on the collection');
        }
    } catch(Exception $e) {
        $t->fail('can do a foreach on the collection');
    }
    
    $t->is($collection[0], 'tag 1', '->offsetGet() works by returning the correct element at the given index');
    
    $t->is($collection->pop(), 'tag 5', '->pop() returns the element off the end of the array');
    $t->is($collection[4], null, '->pop() removed that element off the list entirely');
    
    $t->is($collection->shift(), 'tag 1', '->shift() returns the element off the beginning of the array');
    $t->is($collection[0], 'tag 2', '->shift() removed that element off the list entirely');
    
    sfRedis::getClient()->flushdb();
