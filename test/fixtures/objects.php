<?php

/** @RedisEntity */
class User extends sfRedisObject
{
    
    /** @RedisIndex */
    public $nickname;
    
    /** @RedisField */
    public $email;
    
}

/** @RedisEntity */
class BlogPost extends sfRedisObject
{
    
    /** @RedisRelation(is_a = "User") */
    public $author;
    
    /** @RedisField */
    public $content;
    
}

/** @RedisEntity */
class BlogPostCommentable extends BlogPost
{
    
    /** @RedisCollection(type = "list", has = "Comment") */
    public $comments;
    
}

/** @RedisEntity */
class BlogPostTaggable extends BlogPost
{
    
    /** @RedisCollection(type = "set") */
    public $tags;
    
}

/** @RedisEntity */
class Comment extends sfRedisObject
{
    
    /** @RedisField */
    public $author;
    
    /** @RedisField */
    public $content;
    
    /** @RedisField(type = "datetime") */
    public $posted_at;
    
}

/** @RedisSet(has = "User") */
class UserSet extends sfRedisSetCollection
{
}

class BrokenObject extends sfRedisObject
{
}