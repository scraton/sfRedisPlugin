<?php

class sfRedisZSetEntity extends sfRedisEntity
{
    
    public function getSet() {
        return $this->getValue();
    }
    
    public function add($score, $member) {
        return $this->getClient()->zadd($this->getKey(), $score, $member);
    }
    
    public function remove($member) {
        return $this->getClient()->zrem($this->getKey(), $member);
    }
    
    public function score($member) {
        return $this->getClient()->zscore($this->getKey(), $member);
    }
    
    public function rank($member) {
        return $this->getClient()->zrank($this->getKey(), $member);
    }
    
    public function revrank($member) {
        return $this->getClient()->zrevrank($this->getKey(), $member);
    }
    
    public function count($min = null, $max = null) {
        if($min === null || $max === null)
            return $this->getClient()->zcard($this->getKey());
        else
            return $this->getClient()->zcount($this->getKey(), $min, $max);
    }
    
    public function isMember($member) {
        return ($this->getClient()->zrank($this->getKey(), $member) !== null);
    }
    
    public function getMembers() {
        $max = $this->getManager()->getClient()->zcard($this->getKey()) - 1;
        return $this->getClient()->zrange($this->getKey(), 0, $max);
    }
    
    public function rangeByScore($min, $max, $offset = null, $count = null) {
        if($offset !== null || $count != null)
            $pager = array('limit' => array('offset' => $offset, 'count' => $count));
        else
            $pager = array();
        
        return $this->getClient()->zrangebyscore($this->getKey(), $min, $max, $pager);
    }
    
    public function rangeByRank($min, $max) {
        return $this->getClient()->zrange($this->getKey(), $min, $max);
    }
    
    public function rangeByRevRank($min, $max) {
        return $this->getClient()->zrevrange($this->getKey(), $min, $max);
    }
    
    public function removeByScore($min, $max) {
        return $this->getClient()->zremrangebyscore($this->getKey(), $min, $max);
    }
    
    public function removeByRank($min, $max) {
        return $this->getClient()->zremrangebyrank($this->getKey(), $min, $max);
    }
    
    public function incrBy($incr, $member) {
        return $this->getClient()->zincrby($this->getKey(), $incr, $member);
    }

    protected function _save() {
        if(!($this->getClient() instanceof Predis_CommandPipeline))
            $this->pipeline();
        
        $key   = $this->getKey();
        $data  = $this->getSet()->getData();
        $scores= $this->getSet()->getScores();
        $meta  = $this->getSet()->getMeta();
        $field = $this->getSet()->getField();
        
        foreach($data as $j => $v) {
            $score = $scores[$j];
            $this->add( $score, $field->toRedis($v) );
        }
        
        return $this->executePipeline();
    }

	public function associate($obj) {
	    $key = $obj->getKey();
        
        if($key !== null) {
            $type = self::getType($key);
            
            if($type == self::TYPE_NONE)
                $obj->isPersisted(false);
            else if($type != self::TYPE_ZSET)
                throw new sfRedisException('Attempting to associate a `'.$type.'` with a `'.self::TYPE_ZSET.'`');
            else 
                $obj->isPersisted(true);
        }
    }
    
}