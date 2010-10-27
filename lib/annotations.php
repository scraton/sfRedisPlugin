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
    public $entity = 'sfRedisListEntity';
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
    public $entity = 'sfRedisSetEntity';
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
    public $entity = 'sfRedisZSetEntity';
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
 * RedisIndex
 *
 * @package   sfRedisPlugin
 * @author    Stephen Craton <scraton@gmail.com>
 * @license   The MIT License
 * @version   SVN: $Id$
 *
 * @Target("property")
 */
class RedisIndex extends RedisField
{
}

class RedisRelation extends RedisField
{
    public $type = 'object';
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