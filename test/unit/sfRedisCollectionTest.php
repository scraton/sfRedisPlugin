<?php

/**
 * sfRedis tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(7, new lime_output_color());

// should be able to push and unshift data to the collection

    $t->comment('should be able to push and unshift data to the collection');
    
    $collection = new sfRedisCollection();
    
    $collection->push('tag 1');
    $data = $collection->getData();
    
    $t->is($data[0], 'tag 1', '->push() can push data into the collection');
    
    $collection->unshift('tag 2');
    $data = $collection->getData();
    
    $t->is($data[0], 'tag 2', '->unshift() can push data into the collection');
    
    unset($collection);
    
// should be able to pop and shift data on/off the array

    $t->comment('should be able to push and unshift data to the collection');
    
    $collection = new sfRedisCollection();
    
    $collection->push('tag 1');
    $collection->push('tag 2');
    $collection->push('tag 3');
    $collection->push('tag 4');
    $collection->push('tag 5');
    
    $t->is($collection->pop(), 'tag 5', '->pop() can pop data off the end of the array');
    $t->is($collection->shift(), 'tag 1', '->shift() can shift data off the beginning of the array');
    
    unset($collection);
    
// should be able to iterate over the array of data

    $t->comment('should be able to iterate over the array of data');
    
    $collection = new sfRedisCollection();
    
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
    
// should be able to count the data in the array

    $t->comment('should be able to count the data in the array');
    
    $collection = new sfRedisCollection();
    
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