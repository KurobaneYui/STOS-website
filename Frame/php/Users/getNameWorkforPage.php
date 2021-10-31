<?php
if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

if (!function_exists("getNameWorkforPage")) {
    /**
     * This function is used to get name and work info for HomePage
     * @return array
     * @throws STSAException
     */
    function getNameWorkforPage(): array{ // 提供给用户中心页面右上角的个人信息简介
        $logger = new STSA_log();
        $userID = $_SESSION["userID"];
        // check authorization 属于个人级别的非保密信息
//        if(!check_authorization(['team_leader'=>true,'group_leader'=>true,'member'=>true])) {
//            throw new STSAException("无权限查看个人信息",401);
//        } 本函数暂且只会由本人调用，无需检查权限

        $session = new DatabaseConnector();
        $sql = "SELECT `部门名称`,`岗位` FROM 工作信息,部门信息 WHERE `所属组号`=`部门编号` and `学号`='{$userID}';";
        $workInfos = $session->query($sql);
        if ($workInfos===false) {
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__.":".__LINE__, "getNameWorkforPage, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误",417);
        }

//        $rows = $workInfos->num_rows;
//        $fields = array_column($workInfos->fetch_fields(),'name');
        $workInfos = $workInfos->fetch_all(MYSQLI_ASSOC);
        $logger->add_log(__FILE__.":".__LINE__, "getNameWorkforPage, get info successfully", "Log");
        return ["姓名"=>$_SESSION["userName"],"所属组与岗位"=>$workInfos];
    }
}