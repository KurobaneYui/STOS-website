<?php
if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

if (!function_exists("getWageInfo")) {
    /**
     * This function used to get team wage info
     * @return array
     * @throws STSAException
     */
    function getWageInfo(): array{ // 提供给队长用于财务报表
        $logger = new STSA_log();
        $userID = $_SESSION["userID"];
        // check authorization 属于队长级别的他人保密信息
        if(!check_authorization(['team_leader'=>true,'group_leader'=>false,'member'=>false])) {
            $logger->add_log(__FILE__.":".__LINE__, "getWageInfo, 查看队员工资信息失败, 权限错误", "Log");
            throw new STSAException("无权限查看队员工资信息",401);
        }

        $session = new DatabaseConnector();
        $sql = "SELECT * FROM 成员工资信息 ORDER BY `岗位`, `部门名称` DESC;";
        $WageInfos = $session->query($sql);
        if ($WageInfos===false) {
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__.":".__LINE__, "getWageInfo, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误",417);
        }

        $rows = $WageInfos->num_rows;
        $fields = $WageInfos->fetch_fields();
        $WageInfos = $WageInfos->fetch_all(MYSQLI_ASSOC);
        $logger->add_log(__FILE__.":".__LINE__, "getWageInfo, getWageInfo successfully", "Log");
        return ["人数" => $rows, "列数" => count($fields), "数据表" => $WageInfos];
    }
}
