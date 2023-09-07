<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\timetracker;

/**
 * Description of Timer
 *
 * @author drweb
 */
class Timer extends \losthost\DB\DBObject {
    
    const TABLE_NAME = 'timers';
    
    const SQL_CREATE_TABLE = <<<END
            CREATE TABLE IF NOT EXISTS %TABLE_NAME% (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                subject varchar(100),
                current_event bigint(20),
                PRIMARY KEY (id)
            ) COMMENT = 'v1.0.0';
            END;

    public function __construct($subject, $create=true) {
        parent::__construct('subject = ?', $subject, $create);
        if ($this->isNew()) {
            $this->subject = $subject;
            $this->write('New timer created');
        }
    }
    
    public function isStarted() {
        return $this->current_event !== null;
    }
    
    public function start($object, $project, $comment='', $tags=[]) {
        if ($this->isStarted()) {
            throw new \Exception('Timer is already started.', -10013);
        }
        
        $event = new TimerEvent($this);
        $event->start($object, $project, $comment, $tags);
    }
    
    public function stop($comment=null, $tags=null) {
        if (!$this->isStarted()) {
            throw new \Exception("Timer is already stopped.", -10013);
        }
        
        $event = new TimerEvent($this, $this->current_event);
        $event->stop($comment, $tags);
    }
    
    public function add($object, $project, $start_time, $end_or_duration, $comment=null, $tags=null) {
        if ($this->isStarted()) {
            throw new \Exception("Can't add event to started timer.", -10013);
        }
        
        if ($start_time === 'now' || $start_time === null) {
            $start_time = date_create_immutable();
        }
        
        if ($end_or_duration === 'now' || $end_or_duration === null) {
            $end_or_duration = date_create_immutable();
        }
        
        $event = new TimerEvent($this);
        $event->object = $object;
        $event->project = $project;
        $event->started = 0;
        
        if (is_a($start_time, 'DateTime') || is_a($start_time, 'DateTimeImmutable')) {
            $event->start_time = $start_time->format(\losthost\DB\DB::DATE_FORMAT);
        } else {
            $event->start_time = $start_time;
        }
        
        if (is_a($end_or_duration, 'DateInterval')) {
            $event->end_time = date_create_immutable($event->start_time)->add($end_or_duration)->format(\losthost\DB\DB::DATE_FORMAT);
        } elseif (is_a($end_or_duration, 'DateTime') || is_a($end_or_duration, 'DateTimeImmutable')) {
            $event->end_time = $end_or_duration->format(\losthost\DB\DB::DATE_FORMAT);
        } else { // int
            $event->end_time = date_create_immutable($event->start_time)->add(date_interval_create_from_date_string("+${end_or_duration} sec"))->format(\losthost\DB\DB::DATE_FORMAT);
        }

        $event->duration = date_create_immutable($event->end_time)->getTimestamp() - date_create_immutable($event->start_time)->getTimestamp();
        $event->comment = $comment;
        $event->write('add_tags', $tags);
    }
    
    protected function _test_timer_events() {

        $this->start('test object 2', 'test project 1', 'No comments');
        sleep(63);
        $this->stop('Some comment');
        
        $view = new \losthost\DB\DBView("SELECT * FROM [timer_events] ORDER BY id");
        
        $count = 0;
        while ($view->next()) {
            $count++;
            switch ($count) {
                case 1:
                    break;
                case 2:
                    break;
                case 3:
                    break;
                case 4:
                    $test = new \losthost\SelfTestingSuite\Test(\losthost\SelfTestingSuite\Test::EQ, 63);
                    $test->test($view->duration);
                    break;

                default:
                    throw new \Exception('Wrong number of events', -10002);
            }
        }
        
    }
    
    protected function _test_data() {
        return [
            'fetch' => '_test_skip_',
            'write' => '_test_skip_',
            'insert' => '_test_skip_',
            'update' => '_test_skip_',
            'isNew' => '_test_skip_',
            'isModified' => '_test_skip_',
            'asString' => '_test_skip_',
            'getFields' => '_test_skip_',
            'getAutoIncrement' => '_test_skip_',
            'getPrimaryKey' => '_test_skip_',
            'getLabel' => '_test_skip_',
            '__set' => '_test_skip_',
            '__get' => '_test_skip_',
            'prepare' => '_test_skip_',
            'createAlterTable' => '_test_skip_',
            'upgradeTable' => '_test_skip_',
            'initDataStructure' => '_test_skip_',
            'fetchDataStructure' => '_test_skip_',
            'initData' => '_test_skip_',
            'checkSetField' => '_test_skip_',
            'replaceVars' => '_test_skip_',
            'beforeInsert' => '_test_skip_',
            'intranInsert' => '_test_skip_',
            'afterInsert' => '_test_skip_',
            'beforeUpdate' => '_test_skip_',
            'intranUpdate' => '_test_skip_',
            'afterUpdate' => '_test_skip_',
            'beforeDelete' => '_test_skip_',
            'intranDelete' => '_test_skip_',
            'afterDelete' => '_test_skip_',
            'beforeModify' => '_test_skip_',
            'afterModify' => '_test_skip_',
            'addModifiedField' => '_test_skip_',
            'clearModifiedFeilds' => '_test_skip_',
            'eventSetActive' => '_test_skip_',
            'eventUnsetActive' => '_test_skip_',
            'isStarted' => '_test_skip_',
            'start' => [
                ['test object 1', 'test project 1', 'test comment 1', new Tag('tag 1'), null]
            ],
            'stop' => [
                [null]
            ],
            'add' => [
                ['test object 1', 'test project 2', 'now', 10, 'test comment 2', new Tag('tag 1'), null],
                ['test object 1', 'test project 3', 'now', new \DateInterval('PT10M'), 'test comment 3', new Tag('tag 1'), null]
            ],
            '_test_timer_events' => [
                [false]
            ]
        ];
    }
}
