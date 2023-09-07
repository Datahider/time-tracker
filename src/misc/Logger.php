<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\timetracker\misc;
use losthost\DB\DBEvent;

/**
 * Description of Logger
 *
 * @author drweb
 */
class Logger extends \losthost\DB\DBTracker {
    
    public function track(DBEvent $event) {
        
        switch ($event->type) {
            case DBEvent::AFTER_INSERT:
                error_log("Добавлен новый объект ". $event->object->asString());
                break;
            case DBEvent::AFTER_UPDATE:
                error_log("Объект изменен ". $event->object->asString());
                break;
        }
    }
}
