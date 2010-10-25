<?php

/**
 * sfRedis tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(10, new lime_output_color());

require_once dirname(__FILE__).'/../fixtures/objects.php';

sfRedis::getClient()->flushdb();

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
    
    $user->key      = 'user:bobuser';
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
    
    $user = new User('user:bobuser');
    
    $t->isa_ok($user, 'User', 'new User(key) returns a found User object');
    $t->is($user->nickname, 'bobuser', 'new User(key) returns the correct User object');
    
    unset($user);
    
    // should not be able to load a User into a Comment

    $user = new User();
    
    $user->key      = 'user:bobuser';
    $user->nickname = 'bobuser'; 
    
    $em->persist($user);
    
    $comment = new Comment();
    
    $comment->key = 'test:comment';
    $comment->content = 'This is a test.';
    
    $em->persist($comment);
    
    try {
        $user = new User('test:comment');
        $t->fail('new User(key) threw an exception since you can\'t load a Comment into a User');
    } catch(sfRedisException $e) {
        $t->pass('new User(key) threw an exception since you can\'t load a Comment into a User');
    }
    
    sfRedis::getClient()->flushdb();
    
// should be able to persist a collection of data

    $t->comment('should be able to store a collection of data');
    
    $em         = sfRedisEntityManager::create();
    $collection = new sfRedisListCollection('test:collection');
    
    $collection->push('tag 1');
    $collection->push('tag 2');
    $collection->push('tag 3');
    
    try {
        $t->ok($em->persist($collection), '->persist() can persist a collection of data');
    } catch(sfRedisException $e) {
        $t->fail('->persist() can persist a collection of data');
        throw $e;
    }
    
    unset($collection);
    
    $collection = new sfRedisListCollection('test:collection');
    
    try {
        $t->is($collection[2], 'tag 3', 'new sfRedisListCollection(key) retrieves a collection of data');
    } catch(Exception $e) {
        $t->fail('new sfRedisListCollection(key) retrieves a collection of data');
        throw $e;
    }
    exit;
    sfRedis::getClient()->flushdb();
    
// should handle relations between objects

    $t->comment('should handle relations between objects');
    
    $em   = sfRedisEntityManager::create();
    
    $user = new User();
    $post = new BlogPost();
    
    $user->key      = 'user:bobuser';
    $user->nickname = 'bobuser';
    
    $post->key     = 'post:1';
    $post->content = 'This is a test.';
    $post->author  = $user;
    
    try {
        $t->ok($em->persist($post), '->persist() will succeed in saving the post along with the related User object');
    } catch(sfRedisEntityManagerException $e) {
        $t->fail('->persist() will succeed in saving the post along with the related User object');
        throw $e;
    }
    
    unset($post, $user);
    
    $post = new BlogPost('post:1');
    
    $t->is($post->author->nickname, 'bobuser', 'new BlogPost(key) will load the correct related User object when requested');
    
    sfRedis::getClient()->flushdb();
    
// should handle relations of a one-to-many nature

    $t->comment('should handle relations of a one-to-many nature');
    
    $em   = sfRedisEntityManager::create();
    
    $user = new User();
    $post = new BlogPost();
    
    $user->nickname = 'bobuser';
    
    $post->key     = 'post:1';
    $post->content = 'This is a test.';
    $post->author  = $user;
    
    $comment = new Comment();
    $comment->author  = 'Joe User';
    $comment->content = 'Fantastic blog post!';
    
    $post->comments->push($comment);
    
    unset($comment);
    
    $comment = new Comment();
    $comment->author  = 'Sally User';
    $comment->content = 'This blog post is less than stellar.';
    
    $post->comments->push($comment);
    
    unset($comment);
    
    try {
        $t->ok($em->persist($post), '->persist() should handle one-to-many relation object persistance');
    } catch(Exception $e) {
        $t->fail('->persist() should handle one-to-many relation object persistance');
        throw $e;
    }
    
    unset($post);
    
    $post = $em->retrieveByKey('post:1');
    
    var_dump($post->comments);
    
    $t->is($post->comments[1]->author, 'Sally User', '->retrieveByKey() retrieved the comments along with the post');
    
    //sfRedis::getClient()->flushdb();
    