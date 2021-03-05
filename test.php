<?php
session_start();
require_once __DIR__."/ROOT_PATH.php";
require_once ROOT_PATH."/frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH."/frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/frame/php/Connector/DatabaseConnector.php";
// TODO:require log file
// TODO:require authorization file

$e = new STSAException();
var_dump($e);

//$a = array($e);
$a = $e->toArray();
var_dump($a);