<?php

/** @RedisZSet */
class sfRedisZSetCollection extends sfRedisSetCollection
{
    
    protected $_scores = array();
    
    public function getScores() {
        return $this->_scores;
    }
    
    public function add() {
        $values = func_get_args();
        $values = array_chunk($values, 2);
        
        if($this->isPersisted()) {
            $this->getEntity()->pipeline();
            
            foreach($values as $value) {
                list($score, $member) = $value;
                
                $this->getEntity()->add( $score, $this->getField()->toRedis($member) );
            }
                
            return $this->getEntity()->executePipeline();
        } else {
            foreach($values as $value) {
                list($score, $member) = $value;
                
                $this->_data[]   = $member;
                $this->_scores[] = $score;
            }
        }
    }
    
    public function remove($value) {
        if($this->isPersisted())
            return parent::remove($value);
        else {
            $key = array_search($value, $this->_data);
            unset($this->_data[$key]);
            unset($this->_scores[$key]);
        }
    }
    
    public function score($member) {
        if($this->isPersisted())
            return $this->getEntity()->score($member);
        else {
            $key = array_search($member, $this->_data);
            return $this->_scores[$key];
        }
    }
    
    public function rank($member) {
        if($this->isPersisted())
            return $this->getEntity()->rank($member);
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function revRank($member) {
        if($this->isPersisted())
            return $this->getEntity()->revrank($member);
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function rangeByScore($min, $max, $offset = null, $count = null) {
        if($this->isPersisted())
            return $this->getEntity()->rangeByScore($min, $max, $offset, $count);
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function rangeByRank($min, $max) {
        if($this->isPersisted())
            return $this->getEntity()->rangeByRank($min, $max);
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function rangeByRevRank($min, $max) {
        if($this->isPersisted())
            return $this->getEntity()->rangeByRevRank($min, $max);
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function removeByScore($min, $max) {
        if($this->isPersisted())
            return $this->getEntity()->removeByScore($min, $max);
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function removeByRank($min, $max) {
        if($this->isPersisted())
            return $this->getEntity()->removeByRank($min, $max);
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function incrBy($incr, $member) {
        if($this->isPersisted())
            return $this->getEntity()->incrBy($incr, $member);
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function key() {
        if(!$this->isPersisted())
            return parent::key();
        else
            return $this->getEntity()->score( $this->getField()->toRedis( $this->current() ) );
    }
    
}