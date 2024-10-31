<?php

use losthost\DB\DB;
use losthost\timetracker\TagBinder;

require_once '../vendor/autoload.php';

$db_host = 'localhost';
$db_user = 'test';
$db_name = 'test';
$db_prefix = 'ttimer_';

require 'db_pass.php';

DB::connect($db_host, $db_user, $db_pass, $db_name, $db_prefix);
DB::dropAllTables(true, true);

