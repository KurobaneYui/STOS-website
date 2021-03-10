<?php
session_start();
ini_set("display_errors","On");
error_reporting(E_ALL);
require_once __DIR__."/ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
// TODO:require log file
// TODO:require authorization file

$e = new STSAException();
var_dump($e);

//$a = array($e);
$a = $e->toArray();
var_dump($a);