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
        
        if($values[0] instanceof sfRedisAbstract)
            return $this->add2($values);
        else
            return $this->add1($values);
    }
    
    private function add1($args) {
        // they are in pairs: score, member, score, member
        $values = array_chunk($args, 2);
        
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
    
    private function add2($args) {
        // the values are objects with a getScore method
        if($this->isPersisted()) {
            $this->getEntity()->pipeline();
            
            foreach($args as $member) {
                $this->getEntity()->add( $member->getScore(), $this->getField()->toRedis($member) );
            }
                
            return $this->getEntity()->executePipeline();
        } else
            foreach($args as $member) {
                $this->_data[]   = $member;
                $this->_scores[] = $member->getScore();
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
            return $this->getEntity()->score($this->getField()->toRedis($member));
        else {
            $key = array_search($member, $this->_data);
            return $this->_scores[$key];
        }
    }
    
    public function rank($member) {
        if($this->isPersisted())
            return $this->getEntity()->rank($this->getField()->toRedis($member));
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function revRank($member) {
        if($this->isPersisted())
            return $this->getEntity()->revrank($this->getField()->toRedis($member));
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function incrBy($incr, $member) {
        if($this->isPersisted())
            return $this->getEntity()->incrBy($incr, $this->getField()->toRedis($member));
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    protected function hydrateResults($members) {
        if(is_array($members))
            foreach($members as $i => $member)
                $members[$i] = $this->getField()->fromRedis($member);
        return $members;
    }
    
    public function rangeByScore($min, $max, $offset = null, $count = null) {
        if($this->isPersisted())
            return $this->hydrateResults( $this->getEntity()->rangeByScore($min, $max, $offset, $count) );
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function rangeByRank($min, $max) {
        if($this->isPersisted())
            return $this->hydrateResults( $this->getEntity()->rangeByRank($min, $max) );
        else
            throw new sfRedisException('This object `'.__CLASS__.'` must be persisted to access `'.__METHOD__.'`');
    }
    
    public function rangeByRevRank($min, $max) {
        if($this->isPersisted())
            return $this->hydrateResults( $this->getEntity()->rangeByRevRank($min, $max) );
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
    
    public function key() {
        if(!$this->isPersisted())
            return parent::key();
        else
            return $this->getEntity()->score( $this->getField()->toRedis( $this->current() ) );
    }
    
}