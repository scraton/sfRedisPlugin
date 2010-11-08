<?php

class sfRedisDateTimeField extends sfRedisField
{
    
    const TZ_REDIS = 'UTC';
    
    public $name  = null;
    public $type  = 'datetime';
    
    public $format;
    
    public static function createFromAnnotation($name, RedisField $annotation) {
        $field         = new sfRedisDateTimeField($name);
        $field->type   = $annotation->type;
        
        switch($field->type) {
            case 'date':      $field->format = 'Y-m-d H:i:s'; break;
            case 'datetime':  $field->format = 'Y-m-d H:i:s'; break;
            case 'timestamp': $field->format = 'U'; break;
        }
            
        return $field;
    }
    
    public function fromRedis($value) {
        $dt = new DateTime($value, new DateTimeZone(self::TZ_REDIS));
        $dt->setTimezone( new DateTimeZone(date_default_timezone_get()) );
        
        return $dt->format($this->format);
    }
    
    public function toRedis($value) {
        $dt = new DateTime($value, new DateTimeZone(date_default_timezone_get()));
        $dt->setTimezone( new DateTimeZone(self::TZ_REDIS) );
        
        return $dt->format($this->format);
    }
    
}