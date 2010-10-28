<?php

/**
 * sfRedis tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(13, new lime_output_color());

require_once dirname(__FILE__).'/../fixtures/objects.php';

sfRedis::getClient()->flushdb();

// persist should fail when trying to persist a non-redis entity

    $t->comment('persist should fail when trying to persist a non-redis entity');

    $em = sfRedisEntityManager::create();
    $dt = new DateTime();
    
    try {
        $em->persist($dt);
        $t->fail('->persist() should fail when trying to persist a non-redis entity');
    } catch(sfRedisException $e) {
        $t->pass('->persist() should fail when trying to persist a non-redis entity');
    }
    
    // this one extends the sfRedisObject but lacks a doc-comment
    
    try {
        $broken = new BrokenObject();
        $t->fail('threw an exception when trying to play with a non-redis entity');
    } catch(sfRedisException $e) {
        $t->pass('threw an exception when trying to play with a non-redis entity');
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
    
    $user->nickname = 'bobuser'; 
    
    $em->persist($user);
    
    unset($user);
    
    $user = new User('bobuser');
    
    $t->isa_ok($user, 'User', 'new User(key) returns a found User object');
    $t->is($user->nickname, 'bobuser', 'new User(key) returns the correct User object');
    
    unset($user);
    
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
    
    sfRedis::getClient()->flushdb();
    
// should handle relations between objects

    $t->comment('should handle relations between objects');
    
    $em   = sfRedisEntityManager::create();
    
    $user = new User();
    $post = new BlogPost('post:1');
    
    $user->nickname = 'bobuser';
    
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
    
    unset($post);
    sfRedis::getClient()->flushdb();
    
    // but I can also just directly accesss the User object without having to create it myself
    
    $post = new BlogPost('post:2');
    
    $post->content = 'Woot. A test.';
    $post->author->nickname = 'bobuser';
    
    $em->persist($post);
    
    unset($post);
    
    $post = new BlogPost('post:2');
    
    $t->is($post->author->nickname, 'bobuser', 'new BlogPost(key) will load the correct related User object when requested');
    
    sfRedis::getClient()->flushdb();
    
// should handle relations of a one-to-many nature

    $t->comment('should handle relations of a one-to-many nature');
    
    $em   = sfRedisEntityManager::create();
    
    $user = new User('user:bobuser');
    $post = new BlogPostCommentable('post:1');
    
    $user->nickname = 'bobuser';
    
    $post->content = 'This is a test.';
    $post->author  = $user;
    
    $comment = new Comment('comment:1');
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
    
    $post = new BlogPostCommentable('post:1');
    
    $t->is(count($post->comments), 2, 'new BlogPostCommentable(key) retrieved the comments along with the post');
    
    // can even add to the list of comments after persisted
    
    $comment = new Comment();
    $comment->author  = 'Sally User';
    $comment->content = 'This comment sucks.';
    
    try {
        $post->comments->push($comment);
        $t->pass('->push() works on an associated collection even after being persisted');
    } catch(Exception $e) {
        $t->fail('->push() works on an associated collection even after being persisted');
    }
    
    //sfRedis::getClient()->flushdb();
    