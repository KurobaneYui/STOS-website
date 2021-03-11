<?php
session_start();
//ini_set("display_errors","On");
//error_reporting(E_ALL);
require_once __DIR__."/ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
// TODO:require log file
// TODO:require authorization file

$root_dir = ROOT_PATH;
$python_return = array();
$python_return_code = 0;
exec("python {$root_dir}/test.py 123 ew",$python_return,$python_return_code);
var_dump($python_return);
var_dump($python_return_code);
