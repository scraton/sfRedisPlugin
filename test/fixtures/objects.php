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

/** @RedisEntity */
class BlogPost extends sfRedisObject
{
    
    /** @RedisKey */
    protected $key;
    
    /** @RedisField(type = "relation", is_a = "User") */
    protected $author;
    
    /** @RedisField */
    protected $content;
    
}

/** @RedisEntity */
class BlogPostCommentable extends BlogPost
{
    
    /** @RedisCollection(type = "list", has = "Comment") */
    protected $comments;
    
}

/** @RedisEntity */
class Comment extends sfRedisObject
{
    
    /** @RedisKey */
    protected $key;
    
    /** @RedisField */
    protected $author;
    
    /** @RedisField */
    protected $comment;
    
    /** @RedisField(type = "datetime") */
    protected $posted_at;
    
}

class BrokenObject extends sfRedisObject
{
}