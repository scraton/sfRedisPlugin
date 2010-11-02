<?php

/** @RedisEntity */
class User extends sfRedisObject
{
    
    /** @RedisIndex */
    public $nickname;
    
    /** @RedisField */
    public $email;
    
    /** @RedisField */
    public $age;
    
    public function setEmail($email) {
        return $this->_set('email', $email.' -- modified');
    }
    
    public function getAge() {
        return ($this->_get('age') * 2);
    }
    
}

/** @RedisEntity */
class BlogPost extends sfRedisObject
{
    
    /** @RedisRelation(is_a = "User") */
    public $author;
    
    /** @RedisField */
    public $content;
    
    /** @RedisField(type = "datetime") */
    public $created_at;
    
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
class BlogPostWithMovies extends BlogPost
{
    // sue me, I couldn't think of anything clever to add to a blog post that needs to be sorted
    
    /** @RedisCollection(type = "zset", has = "Movie") */
    public $movies;
    
}

/** @RedisEntity */
class Movie extends sfRedisObject
{
    
    /** @RedisIndex */
    public $title;
    
    /** @RedisScore */
    public $year;
    
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

/** @RedisZSet(has = "User") */
class UserZSet extends sfRedisZSetCollection
{
}

class BrokenObject extends sfRedisObject
{
}