<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\timetracker;

use losthost\DB\DBObject;
use losthost\DB\DB;

class TagBinder extends DBObject {

    const METADATA = [
        'id' => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
        'tag' => 'BIGINT(20) UNSIGNED NOT NULL',
        'timer_event' => 'BIGINT(20) UNSIGNED NOT NULL',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX tag_event' => ['tag', 'timer_event']
    ];
    
    public static function tableName() {
        return DB::$prefix. 'tag_binds';
    }
    
    public function __construct(int|Tag $tag, int|TimerEvent $timer_event) {

        if (is_a($tag, 'losthost\timetracker\Tag')) {
            $tag = $tag->id;
        }

        if (is_a($timer_event, 'losthost\timetracker\TimerEvent')) {
            $timer_event = $timer_event->id;
        }
        
        parent::__construct(['tag' => $tag, 'timer_event' => $timer_event], true);
        if ($this->isNew()) {
            $this->write('New binding created');
        }
        
    }
}
