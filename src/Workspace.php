<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\timetracker;

/**
 * Description of Workspace
 *
 * @author drweb
 */
class Workspace extends \losthost\DB\DBObject {
 
    protected $__secret;
    
    const TABLE_NAME = 'timer_workspaces';
    
    const SQL_CREATE_TABLE = <<<END
            CREATE TABLE IF NOT EXISTS %TABLE_NAME% (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name varchar(50) NOT NULL,
                secret varchar(100) NOT NULL,
                PRIMARY KEY (id)
            ) COMMENT = 'v1.0.0';
            END;
    
    const SECRET_SET = '_0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIGKLMNOPQRSTUVWXYZ';
    const SECRET_LEN = '50';
    
    public function __construct(string $token=null) {
        
        if ($token) {
            $where = 'id = ? AND secret = ?';
            $params = explode(':', $token, 2);
            $params[1] = md5($params[1]);
            parent::__construct($where, $params);
        } else {
            parent::__construct();
            $this->__secret = $this->genSecret();
        }
        
    }
    
    protected function genSecret($len=self::SECRET_LEN) {
        $result = '';
        
        for ($i=0; $i<$len; $i++) {
            $result .= substr(self::SECRET_SET, random_int(0, strlen(self::SECRET_SET)-1), 1);
        }
        
        return $result;
    }
    
    protected function _test_data() {
        return [
            'genSecret' => [
                [new \losthost\SelfTestingSuite\Test(\losthost\SelfTestingSuite\Test::PCRE, "/^[_0-9a-zA-Z]{50}$/")],
                [10, new \losthost\SelfTestingSuite\Test(\losthost\SelfTestingSuite\Test::PCRE, "/^[_0-9a-zA-Z]{10}$/")],
            ]
        ];
    }
}
