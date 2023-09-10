<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\timetracker;

/**
 * Description of TimerEvent
 *
 * @author drweb
 */
class TimerEvent extends \losthost\DB\DBObject {
    
    const TABLE_NAME = 'timer_events';
    
    const SQL_CREATE_TABLE = <<<END
            CREATE TABLE IF NOT EXISTS %TABLE_NAME% (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                timer bigint(20) NOT NULL,
                project varchar(100) NOT NULL,
                object varchar(100) NOT NULL,
                comment varchar(250) NOT NULL DEFAULT '',
                started tinyint(1) NOT NULL DEFAULT 0,
                start_time DATETIME NOT NULL,
                end_time DATETIME,
                duration BIGINT(20),
                PRIMARY KEY (id)
            ) COMMENT = 'v1.0.0';
            END;
    
    const SQL_SELECT_TAGS = <<<END
            SELECT tag 
            FROM [tag_binds]
            WHERE timer_event = ?
            END;
    
    protected $__timer;
    protected $__tags = [];


    public function __construct(Timer $timer, ...$args) {
        
        $this->__timer = $timer;
        $arg_count = count($args);
        
        switch ($arg_count) {
            case 0:
                parent::__construct();
                $this->timer = $timer->id;
                break;
            case 1:
                parent::__construct('id = ? AND timer = ?', [$args[0], $timer->id]);
                break;
            case 4:
                parent::__construct();
                $this->timer = $timer->id;
                $this->fillEvent($args[0], $args[1], $args[2], $args[3]);
                $this->write();
                break;
            case 5:
                parent::__construct();
                $this->timer = $timer->id;
                $this->fillEvent($args[0], $args[1], $args[2], $args[3], $args[4]);
                $this->write();
                break;
            case 6:
                parent::__construct();
                $this->timer = $timer->id;
                $this->fillEvent($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
                $this->write();
                break;
            default:
                $args_passed = 1 + $arg_count;
                throw new \Exception("TimerEvent constructor awaits 1, 2, 5, 6 or 7 arguments. $args_passed passed.");
        }
    }
    
    public function hasTag(Tag $tag) {
        foreach ($this->tags as $existing_tag) {
            if ($existing_tag->id == $tag->id) {
                return true;
            }
        }
        return false;
    }
    
    public function addTag(Tag $tag) {
        if (!$this->hasTag($tag)) {
            $this->__tags[] = $tag;
        }
    }
    
    public function removeTag(Tag $tag) {
        if ($this->hasTag($tag)) {
            foreach ($this->__tags as $key => $existing_tag) {
                if ($existing_tag->id == $tag->id) {
                    unset($this->__tags[$key]);
                    return;
                }
            }
        }
    }


    public function fetch($where = null, $params = []): bool {
        $fetch_result = parent::fetch($where, $params);
        
        if ($this->isNew()) {
            $this->__tags = [];
        } else {
            $tag_view = new \losthost\DB\DBView(self::SQL_SELECT_TAGS, $this->id);
            while ($tag_view->next()) {
                $this->__tags[] = new Tag($tag_view->tag, false);
            }
        }
        return $fetch_result;
    }
    
    public function __set($name, $value) {
        switch ($name) {
            case 'start_time':
                $this->setStartTime($value);
                break;
            case 'end_time':
                $this->setEndTime($value);
                break;
            case 'duration':
                $this->setDuration($value);
                break;
            case 'tags':
                if (!is_array($value)) {
                    $value = [$value];
                }
                $this->__tags = $value;
                break;
            default:
                parent::__set($name, $value);
        }
    }
    
    public function __get($name) {
        switch ($name) {
            case 'start_time':
                return $this->getStartTime();
            case 'end_time':
                return $this->getEndTime();
            case 'tags': 
                return $this->__tags;
            default:
                return parent::__get($name);
        }
    }
    
    protected function fillEvent($object, $project, $start_time, $end_or_duration, $comment=null, $tags=null) {
        if ($this->__timer->isStarted()) {
            throw new \Exception("Can't add event to a started timer.", -10013);
        }
        
        $this->object = $object;
        $this->project = $project;
        $this->started = 0;
        
        $this->start_time = $start_time;
        $this->end_time = $end_or_duration;
        
        $this->comment = $comment;
        $this->tags = $tags;
    }


    public function start($object, $project, $comment=null, $tags=null) {
        if ($this->started) {
            throw new \Exception("Event is already started.");
        }
        if ($this->end_time) {
            throw new \Exception("Event is already finished (stopped).");
        }
        
        $this->object = $object;
        $this->project = $project;
        $this->started = 1;
        $this->start_time = 'now';
        if ($comment !== null) {
            $this->comment = $comment;
        }
        if ($tags !== null) {
            $this->tags = $tags;
        }
        $this->write();
    }
    
    public function stop($comment=null, $tags=null) {
        if (!$this->started) {
            throw new \Exception("Can't stop not started event.", -10013);
        }

        $this->started = 0;
        $this->end_time = 'now';
        if ($comment !== null) {
            $this->comment = $comment;
        }
        if ($tags !== null) {
            $this->tags = $tags;
        }
        $this->write();
    }
    
    protected function intranInsert($comment, $data) {
        if ($this->started) {
            $this->__timer->current_event = $this->id;
            $this->__timer->write();
        } else {
            $this->__timer->current_event = null;
            $this->__timer->write();
        }
        
        foreach ($this->__tags as $tag) {
            if (!is_a($tag, 'losthost\timetracker\Tag')) {
                throw new \Exception('Awaiting tags to be Tag or array of Tags');
            }
            new TagBinder($tag, $this);
        }
        parent::intranInsert($comment, $data);
    }
    
    protected function intranUpdate($comment, $data) {
        if ($this->started) {
            $this->__timer->current_event = $this->id;
            $this->__timer->write();
        } else {
            $this->__timer->current_event = null;
            $this->__timer->write();
        }
        $this->updateTagsInDB();
        parent::intranUpdate($comment, $data);
    }
    
    protected function updateTagsInDB() {
        $tag_view = new \losthost\DB\DBView(self::SQL_SELECT_TAGS, $this->id);
        $ids_now_in_db = [];
        $ids_now_in_object = [];
        
        while ($tag_view->next()) {
            $ids_now_in_db[] = $tag_view->tag;
        }
        foreach ($this->__tags as $tag) {
            $ids_now_in_object[] = $tag->id;
        }
        
        $to_insert = array_diff($ids_now_in_object, $ids_now_in_db);
        $to_remove = array_diff($ids_now_in_db, $ids_now_in_object);
        
        foreach ($to_insert as $tag_id) {
            new TagBinder($tag_id, $this);
        }
        
        foreach ($to_remove as $tag_id) {
            $binding = new TagBinder($tag_id, $this);
            $binding->delete();
        }
    }
    
    protected function getStartTime() {
        if (isset($this->__data['start_time'])) {
            return date_create_immutable($this->__data['start_time']);
        }
        return null;
    }
    
    protected function getEndTime() {
        if (isset($this->__data['end_time'])) {
            return date_create_immutable($this->__data['end_time']);
        }
        return null;
    }
    
    protected function setStartTime(null|string|\DateTime|\DateTimeImmutable $time) {
        if ($time === null) {
            $time = date_create_immutable();
        } elseif (is_string($time)) {
            $time = date_create_immutable($time);
        }         
        
        $this->__data['start_time'] = $time->format(\losthost\DB\DB::DATE_FORMAT);
        $this->updateDuration();
    }
    
    protected function setEndTime(null|string|int|\DateInterval|\DateTime|\DateTimeImmutable $time) {
        if ($time === null) {
            $time = date_create_immutable();
        } elseif (is_string($time)) {
            $time = date_create_immutable($time);
        } elseif (is_int($time) || is_a($time, '\DateInterval')) {
            $time = $this->endTimeByInterval($time);
        }
        
        $this->__data['end_time'] = $time->format(\losthost\DB\DB::DATE_FORMAT);
        $this->updateDuration();
    }
    
    protected function setDuration(int|\DateInterval $duration) {
        $this->checkStartTimeIsSet();
        $this->setEndTime($duration);
    }

    protected function endTimeByInterval(int|\DateInterval $interval) {
        $this->checkStartTimeIsSet();
        if (is_int($interval)) {
            $interval = date_interval_create_from_date_string("+$interval sec");
        }
        return date_create_immutable($this->__data['start_time'])->add($interval);
    }
    
    protected function checkStartTimeIsSet() {
        if (!isset($this->__data['start_time'])) {
            throw new \Exception("start_time is not yet set.", -10013);
        }
    }

    protected function updateDuration() {
        if (isset($this->__data['start_time']) && isset($this->__data['end_time'])) {
            $this->__data['duration'] = $this->end_time->getTimestamp() - $this->start_time->getTimestamp();
        }
    }
    
}
