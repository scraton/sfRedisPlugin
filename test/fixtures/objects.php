<?php

/** @RedisEntity */
class User extends sfRedisObject
{
        
    /** @RedisKey */
    protected $key;
    
    /** @RedisField */
    protected $nickname;
    
    /** @RedisField */
    protected $email;
    
}

/** @RedisEntity(class = "sfRedisStringEntity") */
class UserString extends sfRedisObject
{
        
    /** @RedisKey */
    protected $key;
    
    /** @RedisField */
    protected $nickname;
    
    /** @RedisField */
    protected $email;
    
}

/** @RedisEntity */
class BlogPost extends sfRedisObject
{
    
    /** @RedisCollection(has = "Comment", type = "many") */
    protected $comments;
    
    /** @RedisField(is_a = "User") */
    protected $author;
    
}

/** @RedisEntity */
class Comment extends sfRedisObject
{
    
    /** @RedisField */
    protected $author;
    
    /** @RedisField */
    protected $comment;
    
    /** @RedisField(type = "datetime") */
    protected $posted_at;
    
}

/** @RedisList */
class BlogPosts
{
    
    /** @RedisCollection(has = "BlogPost", type = "many") */
    protected $posts;
    
}