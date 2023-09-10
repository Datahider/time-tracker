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
        new TimerEvent($this, $object, $project, $start_time, $end_or_duration, $comment, $tags);
    }
    
    protected function _test_timer_events() {

        $this->start('test object 2', 'test project 1', 'No comments');
        echo '<wait '. DURATION_TEST. ' seconds>';
        sleep(DURATION_TEST);
        echo '.';
        $this->stop('Some comment');
        
        echo '.';
        $view = new \losthost\DB\DBView("SELECT * FROM [timer_events] ORDER BY id");
        
        $count = 0;
        while ($view->next()) {
            echo '.';
            $count++;
            switch ($count) {
                case 1:
                    break;
                case 2:
                    break;
                case 3:
                    break;
                case 4:
                    $test = new \losthost\SelfTestingSuite\Test(\losthost\SelfTestingSuite\Test::EQ, DURATION_TEST);
                    $test->test($view->duration);
                    break;

                default:
                    throw new \Exception('Wrong number of events', -10002);
            }
        }
        
    }
    
    protected function _test_data() {
        
        return array_merge(parent::_test_data(), [
            'isStarted' => '_test_skip_',
            'start' => [
                ['test object 1', 'test project 1', 'test comment 1', new Tag('tag 1'), null]
            ],
            'stop' => [
                [null]
            ],
            'add' => [
                ['test object 1', 'test project 2', 'now', 10, 'test comment 2', new Tag('tag 1'), null],
                ['test object 1', 'test project 3', 'now', new \DateInterval('PT10M'), 'test comment 3', new Tag('tag 2'), null]
            ],
            '_test_timer_events' => [
                [false]
            ]
        ]);
    }
}
