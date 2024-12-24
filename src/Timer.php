<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\timetracker;

use losthost\DB\DB;
use losthost\DB\DBObject;
use losthost\DB\DBList;
use losthost\DB\DBView;

class Timer extends DBObject {
    
    const LIST_STOPPED = 0;
    const LIST_STARTED = 1;
    const LIST_BOTH = 2;
    
    const METADATA = [
        'id' => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
        'subject' => 'VARCHAR(100)',
        'current_event' => 'BIGINT(20)',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX SUBJECT' => 'subject'
    ];
    
    public static function tableName() {
        return DB::$prefix. 'timers';
    }


    public function __construct($subject, $create=true) {
        parent::__construct(['subject' => $subject], $create);
        if ($this->isNew()) {
            $this->write('New timer created');
        }
    }
    
    public function isStarted() {
        return $this->current_event !== null;
    }
    
    public function start($object, $project, $comment='', $tags=[]) {
        DB::beginTransaction();
        try {
            
            $lock = new DBView('SELECT current_event FROM [timers] WHERE id = ? AND current_event IS NULL FOR UPDATE', [$this->id]);
            if (!$lock->next()) {
                throw new \Exception('Timer is already started.', -10013);
            }

            $event = new TimerEvent($this);
            $event->start($object, $project, $comment, $tags);
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
        DB::commit();
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
    
    static public function getStartedByObjectProject(string $object, string $project) : array {
        
        $view = new DBView(<<<FIN
            SELECT 
                t.subject AS subject 
            FROM 
                [timers] AS t
                LEFT JOIN [timer_events] AS e ON e.timer = t.id 
            WHERE
                e.object = ?
                AND e.project = ?
                AND e.started = 1
            FIN, [$object, $project]);
        
        $result = [];

        while ($view->next()) {
            $result[] = new Timer($view->subject);
        }

        return $result;
        
    }
}
