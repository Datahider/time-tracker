<?php

namespace losthost\timetracker;

use PHPUnit\Framework\TestCase;
use losthost\DB\DBList;

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
    
    public function testTimerGetStartedByObjectProject() {
        $timer1 = new Timer('1234567');
        $timer1->start('Заявка 1', 'Главный клиент');
        
        $timer2 = new Timer('7654321', true);
        $timer2->start('Заявка 1', 'Главный клиент');
        
        $list = Timer::getStartedByObjectProject('Заявка 1', 'Главный клиент');
        
        $this->assertEquals(2, count($list));
        
        foreach ($list as $timer) {
            $timer->stop();
            $this->assertFalse($timer->isStarted());
        }
    }

}


