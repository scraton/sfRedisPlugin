<?php

/**
 * sfRedis tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(11, new lime_output_color());

require_once dirname(__FILE__).'/../fixtures/objects.php';

sfRedis::getClient()->flushdb();

// should be able to getFieldsByName

    $t->comment('should be able to getFieldsByName');
    
    $user = new User('user:bobuser');
    
    $user->setEmail('bobuser@gmail.com');
    $t->is($user->email, 'bobuser@gmail.com -- modified', 'can use setter methods for fields that exist');
    
    $t->is($user->getEmail(), 'bobuser@gmail.com -- modified', 'can use getter methods even if it isn\'t defined');
        
    $user->email = 'bobuser2@gmail.com';
    $t->is($user->email, 'bobuser2@gmail.com -- modified', 'directly setting the attribute will use the setter method');
    
    $user->age = 12;
    
    $t->is($user->getAge(), 24, 'can use getter methods for fields that exist');
    $t->is($user->age, 24, 'directly accessing the attribute will use the getter method');
    
    unset($user);
    
    sfRedis::getClient()->flushdb();
    
// should not be able to access or set fields that don't exist

    $t->comment('should not be able to access or set fields that don\'t exist');
    
    $user = new User('user:bobuser');
    
    try {
        $user->unknown = 'kaboom!';
        $t->fail('cannot set non-existent field using fields');
    } catch(sfRedisException_UnknownPropertyException $ex) {
        $t->pass('cannot set non-existent field using fields');
    }
    
    try {
        $user->setUnknown('kaboom!');
        $t->fail('cannot set non-existent field using methods');
    } catch(sfRedisException_UnknownPropertyException $ex) {
        $t->pass('cannot set non-existent field using methods');
    }
    
    try {
        var_dump($user->unknown);
        $t->fail('cannot get non-existent field using fields');
    } catch(sfRedisException_UnknownPropertyException $ex) {
        $t->pass('cannot get non-existent field using fields');
    }
    
    try {
        var_dump($user->getUnknown());
        $t->fail('cannot get non-existent field using methods');
    } catch(sfRedisException_UnknownPropertyException $ex) {
        $t->pass('cannot get non-existent field using methods');
    }
    
    sfRedis::getClient()->flushdb();
    
// should do timezone conversion automagically

    date_default_timezone_set('America/New_York');

    $t->comment('should do timezone conversion automagically');
    
    $post = new BlogPost('test:post');
    
    $post->created_at = '2010-11-02 12:00:00';
    
    sfRedisEntityManager::create()->persist($post);
    
    unset($post);
    
    $created_at = sfRedis::getClient()->hget('BlogPost:test:post', 'created_at');
    
    $t->is($created_at, '2010-11-02 16:00:00', 'timezone as saved to redis as UTC');
    
    $post = new BlogPost('test:post');
    
    $t->is($post->created_at, '2010-11-02 12:00:00', 'timezone was retrieved in local timezone');
    
    sfRedis::getClient()->flushdb();