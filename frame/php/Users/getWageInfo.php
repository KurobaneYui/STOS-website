<?php
session_start();
require_once __DIR__."/../../../ROOT_PATH.php";
require_once ROOT_PATH."/frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH."/frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/frame/php/Connector/DatabaseConnector.php";
// TODO:require log file
// TODO:require authorization file

if (!function_exists("getWageInfo")) {
    function getWageInfo() { // 提供给队长用于财务报表
        $userID = $_SESSION["userID"];
        // TODO:check authorization 属于队长级别的他人保密信息

        $session = new DatabaseConnector();
        $sql = "SELECT * FROM 成员工资信息 ORDER BY `岗位`, `部门名称` DESC;";
        $WageInfos = $session->query($sql);
        if ($WageInfos===false) {
            throw ;// TODO:Throw Errors 417 数据库查询错误
        }

        $rows = $WageInfos->num_rows;
        $fields = $WageInfos->fetch_fields();
        $WageInfos = $WageInfos->fetch_all(MYSQLI_ASSOC);
        return ["人数" => $rows, "列数" => count($fields), "数据表" => $WageInfos];
    }
}
