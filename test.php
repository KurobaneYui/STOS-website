<?php
session_start();
ini_set("display_errors","On");
error_reporting(E_ALL);
require_once __DIR__."/ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";

var_dump($_SERVER);