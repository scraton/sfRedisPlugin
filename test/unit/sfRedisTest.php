<?php

/**
 * sfRedis tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(20, new lime_output_color());

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
    $comment->author  = 'Bob Schmucks';
    $comment->content = 'This comment is the greatest.';
    
    $post->comments->push($comment);
    
    unset($post, $comment);
    
    $post = new BlogPostCommentable('post:1');
    
    $t->is($post->comments[2]->content, 'This comment is the greatest.', '->push() works on an already persisted object');
    
    sfRedis::getClient()->flushdb();
    
// should be able to handle objects with RedisSets as fields

    $t->comment('should be able to handle objects with RedisSets as fields');
    
    $user = new User('user:bobuser');
    $post = new BlogPostTaggable('post:1');
    
    $user->nickname = 'bobuser';
    
    $post->content = 'This is a test.';
    $post->author  = $user;
    
    $post->tags->add('omgwtfbbq', 'test tag', 'rox my sox');
    
    try {
        $t->ok($em->persist($post), '->persist() should handle redis sets as fields');
    } catch(Exception $e) {
        $t->fail('->persist() should handle redis sets as fields');
        throw $e;
    }
    
    unset($post);
    
    $post = new BlogPostTaggable('post:1');
    
    $t->is(count($post->tags), 3, '->tags has the correct number of tags');
    $t->is($post->tags->isMember('omgwtfbbq'), true, '->tags has the correct omgwtfbbq tag as expected');
    
    $t->is(sort($post->tags->getMembers()), array('omgwtfbbq', 'test tag', 'rox my sox'), '->getMembers() returns an array of tags');
    
    sfRedis::getClient()->flushdb();
    
// should be able to handle objects with RedisZSets as fields

    $t->comment('should be able to handle objects with RedisSets as fields');
    
    $user = new User('user:bobuser');
    $post = new BlogPostWithMovies('post:1');
    
    $user->nickname = 'bobuser';
    
    $post->content = 'This is a test.';
    $post->author  = $user;
    
    $rocky = new Movie();
    $rocky->title = 'Rocky';
    $rocky->year  = 1976;
    
    $post->movies->add($rocky);
    
    $rocky2 = new Movie();
    $rocky2->title = 'Rocky II';
    $rocky2->year  = 1979;
    
    $post->movies->add($rocky2);
    
    $rocky3 = new Movie();
    $rocky3->title = 'Rocky III';
    $rocky3->year  = 1982;
    
    $post->movies->add($rocky3);
    
    try {
        $t->ok($em->persist($post), '->persist() should handle redis zsets as fields');
    } catch(Exception $e) {
        $t->fail('->persist() should handle redis sets as fields');
        throw $e;
    }
    
    unset($post);
    
    $post = new BlogPostWithMovies('post:1');
    
    $t->is(count($post->movies), 3, '->movies has the correct number of movies');
    
    $movies = $post->movies->rangeByScore(1970, 1980);
    
    $t->is($movies[0]->title, 'Rocky', '->rangeByScore() returns the correct first movie sorted by score in the range');
    
    sfRedis::getClient()->flushdb();
    