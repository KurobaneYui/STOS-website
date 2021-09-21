<?php
session_start();
//ini_set("display_errors","On");
//error_reporting(E_ALL);
require_once __DIR__."/ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
<<<<<<< HEAD
// TODO:require log file
=======
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";
>>>>>>> website-v2

require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
var_dump($agent = $_SERVER['HTTP_USER_AGENT']);
$root_dir = ROOT_PATH;
$python_return = array();
$python_return_code = 0;
exec("python {$root_dir}/test.py 123 ew",$python_return,$python_return_code);
var_dump($python_return);
var_dump($python_return_code);
