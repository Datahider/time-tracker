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
    
    public function __construct($name_or_id, $create=true) {
        if (\is_int($name_or_id)) {
            if ($create) {
                throw new \Exception('Avaiting name to create a tag. Number given.');
            }
            parent::__construct ('id = ?', $name_or_id, false);
        } else {
            parent::__construct('name = ?', $name_or_id, $create);
            if ($this->isNew()) {
                $this->name = $name_or_id;
                $this->write('New tag created');
            }
        }
    }
    
}
