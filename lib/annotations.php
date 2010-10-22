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
    public $class = 'sfRedisHashEntity';
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
    public $class = 'sfRedisListEntity';
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
    public $class = 'sfRedisSetEntity';
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
    public $class = 'sfRedisZSetEntity';
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
class RedisCollection extends Annotation
{
    public $has;
    public $type;
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