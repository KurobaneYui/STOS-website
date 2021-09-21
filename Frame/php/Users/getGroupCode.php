<?php
session_start();
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

if(!function_exists("getGroupCode")) {
    /**
     * @param string $groupName
     * @return int
     * @throws STSAException
     */
    function getGroupCode(string $groupName): int{
        $session = new DatabaseConnector();
        $logger = new STSA_log();
        // 非关键信息无需权限检查
        $sql="SELECT 部门编号 FROM 部门信息 WHERE 部门名称='{$groupName}';";
        $result = $session->query($sql);
        if ($result === false) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "getGroupCode, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        if ($result->num_rows<1) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "getGroupCode, 提供的输入错误, 找不到{$groupName}对应的编号", "Error");
            throw new STSAException("提供的输入错误", 400);
        }
        return $result->fetch_all(MYSQLI_ASSOC)[0]["部门编号"];
    }

    /**
     * @return array
     * @throws STSAException
     */
    function getGroupCodeForTeamOrGroupLeader(): array{
        // 环境准备
        $session = new DatabaseConnector();
        $logger = new STSA_log();
        // 权限检查
        if (!check_authorization()) {
            $logger->add_log(__FILE__.":".__LINE__, "getGroupCodeForTeamOrGroupLeader, 可能由于登录过期等原因无法查看个人任职岗位, 权限错误", "Log");
            throw new STSAException("可能由于登录过期等原因无法查看个人任职岗位",401);
        }
        // 搜索组号
        $personID = $_SESSION['userID'];
        $sql = "SELECT 所属组号 FROM 工作信息 WHERE 学号='{$personID}' and 岗位 in ('组长','队长');";
        $groupCodeResult = $session->query($sql);
        if ($groupCodeResult===false) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "getGroupCodeForTeamOrGroupLeader, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        return array_column($groupCodeResult->fetch_all(MYSQLI_ASSOC),"所属组号");
    }
}