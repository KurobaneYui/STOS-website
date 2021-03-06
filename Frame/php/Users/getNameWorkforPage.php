<?php
session_start();
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
// TODO:require log file

if (!function_exists("getNameWorkforPage")) {
    function getNameWorkforPage() { // 提供给用户中心页面右上角的个人信息简介
        $userID = $_SESSION["userID"];
        // TODO:check authorization 属于个人级别的本人保密信息
        if(!check_authorization("")) {
            throw new STSAException("无权限查看个人信息",401);
        }

        $session = new DatabaseConnector();
        $sql = "SELECT `所属组`,`岗位` FROM 工作信息,部门信息 WHERE `所属组号`=`部门编号` and `学号`='{$userID}';";
        $workInfos = $session->query($sql);
        if ($workInfos===false) {
            throw new STSAException("数据库查询错误",417);
        }

        $rows = $workInfos->num_rows;
        $fields = $workInfos->fetch_fields();
        $workInfos = $workInfos->fetch_all(MYSQLI_ASSOC);
        $workInfos_return = array();
        foreach ($workInfos as $i) {
            $workInfos_return[] = ["所属组"=>$i["所属组"],"岗位"=>$i["岗位"]];
        }
        return ["姓名"=>$_SESSION["userName"],"所属组与岗位"=>$workInfos_return];
    }
}