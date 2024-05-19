<?php

namespace losthost\timetracker;

use losthost\DB\DBObject;
use losthost\DB\DB;

class Tag extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
        'name' => 'VARCHAR(256) NOT NULL',
        'PRIMARY KEY' => 'id'
    ];
    
    public static function tableName() {
        return DB::$prefix. 'tags';
    }
    
    public function __construct($name_or_id, $create=true) {
        if (\is_int($name_or_id)) {
            if ($create) {
                throw new \Exception('Avaiting name to create a tag. Number given.');
            }
            parent::__construct (['id' => $name_or_id], false);
        } else {
            parent::__construct(['name' => $name_or_id], $create);
            if ($this->isNew()) {
                $this->write('New tag created'); 
            }
        }
    }
    
}
