<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\timetracker\workspace;

/**
 * Description of WorkspaceList
 *
 * @author drweb
 */
class WorkspaceByName extends \losthost\DB\DBList {
    
    const SQL_QUERY = <<<END
            SELECT
                *
            FROM
                [timer_workspaces]
            WHERE 
                name = ?
            END;
    
    public function __construct($name) {
        parent::__construct([$name]);
    }
    
}
