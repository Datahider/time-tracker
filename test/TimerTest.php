<?php

namespace losthost\timetracker;

use PHPUnit\Framework\TestCase;

class TimerTest extends TestCase {
    
    public function testTimerCreation() : void {
        
        $timer = new Timer('1234567', true);
        
        $same_timer = new Timer('1234567', 'false');
        
        $this->assertEquals($same_timer, $timer);
    }
    
    public function testTimerStartAndStop() : void {
        
        $timer = new Timer('1234567');
        
        if ($timer->isStarted()) {
            $timer->stop();
        }
        
        $timer->start('Заявка 1', 'Главный клиент', 'Запуск тестового таймера', [new Tag('test')]);
        sleep(3);
        $timer->stop('Остановка таймера');
    }

}


