<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\timetracker;

/**
 * Description of TagBinder
 *
 * @author drweb
 */
class TagBinder extends \losthost\DB\DBObject {

    const TABLE_NAME = 'tag_binds';
    
    const SQL_CREATE_TABLE = <<<END
            CREATE TABLE IF NOT EXISTS %TABLE_NAME% (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                tag bigint(20) UNSIGNED NOT NULL,
                timer_event bigint(20) UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                UNIQUE INDEX tag_event (tag, timer_event)
            ) COMMENT = 'v1.0.0';  
            END;
    
    public function __construct(int|Tag $tag, int|TimerEvent $timer_event) {

        if (is_a($tag, 'losthost\timetracker\Tag')) {
            $tag = $tag->id;
        }

        if (is_a($timer_event, 'losthost\timetracker\TimerEvent')) {
            $timer_event = $timer_event->id;
        }
        
        parent::__construct('tag = ? AND timer_event = ?', [$tag, $timer_event], true);
        if ($this->isNew()) {
            $this->tag = $tag;
            $this->timer_event = $timer_event;
            $this->write('New bindign created');
        }
        
    }
}
