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
    
    protected $__timer;
    
    public function __construct(Timer $timer, int|null $id=null) {
        if ($id === null) {
            parent::__construct();
            $this->timer = $timer->id;
        } else {
            parent::__construct('id = ? AND timer = ?', [$id, $timer->id]);
        }
        $this->__timer = $timer;
    }
    
    public function start($object, $project, $comment='', $tags=[]) {
        if ($this->started) {
            throw new \Exception("Event is already started.");
        }
        
        $this->object = $object;
        $this->project = $project;
        $this->started = 1;
        $this->start_time = date_create_immutable()->format(\losthost\DB\DB::DATE_FORMAT);
        $this->comment = $comment;
        $this->write('event_start', $tags); // TODO - добавить запоминание тегов в intran_insert и запись объекта timer
    }
    
    public function stop($comment=null, $tags=null) {
        if (!$this->started) {
            throw new \Exception("Can't stop not started event.", -10013);
        }
        $start_time = date_create_immutable($this->start_time);
        $end_time = date_create_immutable();
        
        $this->started = 0;
        $this->end_time = $end_time->format(\losthost\DB\DB::DATE_FORMAT);
        $this->duration = $start_time->diff($end_time)->format('%s');
        if ($comment !== null) {
            $this->comment = $comment;
        }
        $this->write('event_stop', $tags); // TODO - добавить запоминание тегов в intran_update
    }
    
    public function addTags($tags) {
        // TODO
    }
    
    protected function intranInsert($comment, $data) {
        if ($comment == 'event_start') {
            $this->__timer->current_event = $this->id;
            $this->__timer->write();
        } elseif ($comment == 'event_stop') {
            $this->__timer->current_event = null;
            $this->__timer->write();
        }
        parent::intranInsert($comment, $data);
    }
    
    protected function intranUpdate($comment, $data) {
        if ($comment == 'event_start') {
            $this->__timer->current_event = $this->id;
            $this->__timer->write();
        } elseif ($comment == 'event_stop') {
            $this->__timer->current_event = null;
            $this->__timer->write();
        }
        parent::intranUpdate($comment, $data);
    }
}
