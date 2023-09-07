<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\timetracker;

/**
 * Description of Tag
 *
 * @author drweb
 */
class Tag extends \losthost\DB\DBObject {
    
    const TABLE_NAME = 'tags';
    
    const SQL_CREATE_TABLE = <<<END
            CREATE TABLE IF NOT EXISTS %TABLE_NAME% (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name varchar(256) NOT NULL,
                PRIMARY KEY (id)
            ) COMMENT = 'v1.0.0';  
            END;
    
    const SQL_UPGRADE_FROM_1_0_0 = <<<END
            ALTER TABLE %TABLE_NAME%
            COMMENT = 'v1.0.1'
            END;
    
    public function __construct($name, $create=true) {
        parent::__construct('name = ?', $name, $create);
        if ($this->isNew()) {
            $this->name = $name;
            $this->write('New tag created');
        }
    }
}
