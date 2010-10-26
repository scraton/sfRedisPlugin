<?php

/**
 * RedisEntity
 *
 * @package   sfRedisPlugin
 * @author    Stephen Craton <scraton@gmail.com>
 * @license   The MIT License
 * @version   SVN: $Id$
 *
 * @Target("class")
 */
class RedisEntity extends Annotation
{
    public $entity = 'sfRedisHashEntity';
}

/**
 * RedisList
 *
 * @package   sfRedisPlugin
 * @author    Stephen Craton <scraton@gmail.com>
 * @license   The MIT License
 * @version   SVN: $Id$
 *
 * @Target("class")
 */
class RedisList extends RedisEntity
{
    public $entity   = 'sfRedisListEntity';
    public $type     = 'list';
    public $has_type = 'string';
    public $has;
}

/**
 * RedisSet
 *
 * @package   sfRedisPlugin
 * @author    Stephen Craton <scraton@gmail.com>
 * @license   The MIT License
 * @version   SVN: $Id$
 *
 * @Target("class")
 */
class RedisSet extends RedisEntity
{
    public $entity   = 'sfRedisSetEntity';
    public $type     = 'set';
    public $has_type = 'string';
    public $has;
}

/**
 * RedisZSet
 *
 * @package   sfRedisPlugin
 * @author    Stephen Craton <scraton@gmail.com>
 * @license   The MIT License
 * @version   SVN: $Id$
 *
 * @Target("class")
 */
class RedisZSet extends RedisEntity
{
    public $entity   = 'sfRedisZSetEntity';
    public $type     = 'zset';
    public $has_type = 'string';
    public $has;
}

/**
 * RedisField
 *
 * @package   sfRedisPlugin
 * @author    Stephen Craton <scraton@gmail.com>
 * @license   The MIT License
 * @version   SVN: $Id$
 *
 * @Target("property")
 */
class RedisField extends Annotation
{
    public $type = 'string';
    public $is_a;
}

/**
 * RedisCollection
 *
 * @package   sfRedisPlugin
 * @author    Stephen Craton <scraton@gmail.com>
 * @license   The MIT License
 * @version   SVN: $Id$
 *
 * @Target("property")
 */
class RedisCollection extends RedisField
{
    public $class    = 'sfRedisListCollection';
    public $entity   = 'sfRedisListEntity';
    public $type     = 'list';
    public $has_type = 'string';
    public $has;
}

/**
 * RedisKey
 *
 * @package   sfRedisPlugin
 * @author    Stephen Craton <scraton@gmail.com>
 * @license   The MIT License
 * @version   SVN: $Id$
 *
 * @Target("property")
 */
class RedisKey extends Annotation { }