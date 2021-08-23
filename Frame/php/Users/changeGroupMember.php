<?php
session_start();
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/Tools/TranslateBetweenChineseAndEnglish.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

if(!function_exists("addGroupMember")) {
    /**
     * @param array $personID
     * @param int $groupCode
     * @return array
     * @throws STSAException
     */
    function addGroupMember(array $personID, int $groupCode): array{
        // 初始化变量
        $session = new DatabaseConnector();
        $logger = new STSA_log();
        // 权限检查，需要队长或者对应组组长权限
        if(!check_authorization(["team_leader" => true, "group_leader" => true, "member" => false, "groupID" => $groupCode])) {
            $logger->add_log(__FILE__.":".__LINE__, "addGroupMember, 尝试向ID为{$groupCode}的组添加成员{$personID}失败, 权限错误", "Log");
            throw new STSAException("无权给小组添加成员", 401);
        }
        // 进行数据核对：待添加成员是否已存在且不为该组成员
        $sql = "SELECT 学号 FROM 成员信息 WHERE '{$personID}' not in (SELECT DISTINCT 学号 FROM 工作信息 WHERE 所属组号=$groupCode) and 学号='{$personID}';";
        $personCanAddResult = $session->query($sql);
        if ($personCanAddResult===false) {
            $logger->add_log(__FILE__.":".__LINE__, "addGroupMember, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        $rows = $personCanAddResult->num_rows;
        if ($rows!==1) {
            $personCanAddResult->free();
            $logger->add_log(__FILE__ . ":" . __LINE__, "addGroupMember, 成员{$personID}不可添加, 由于成员不存在或已是该组成员", "Log");
            throw new STSAException("由于成员不存在或已是该组成员", 417);
        }
        $personCanAddResult->free();
        // 核对成功，进行成员添加
        $sql = "INSERT INTO 工作信息 ('学号','所属组号','岗位','基本工资','备注') VALUES ('{$personID}', $groupCode, '组员', '300', '';";
        $addResult = $session->query($sql);
        if ($addResult===false) {
            $logger->add_log(__FILE__.":".__LINE__, "addGroupMember, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        $session->commit();
        $logger->add_log(__FILE__.":".__LINE__, "addGroupMember, 添加成员成功", "Log");
        return [true];
    }
}

if(!function_exists("removeGroupMember")) {
    /**
     * @param array $personID
     * @param string $groupCode
     * @return array
     * @throws STSAException
     */
    function removeGroupMember(array $personID, string $groupCode): array{
        // 初始化变量
        $session = new DatabaseConnector();
        $logger = new STSA_log();
        // 权限检查，需要队长或者对应组组长权限
        if(!check_authorization(["team_leader" => true, "group_leader" => true, "member" => false, "groupID" => $groupCode])) {
            $logger->add_log(__FILE__.":".__LINE__, "removeGroupMember, 尝试从ID为{$groupCode}的组删除成员{$personID}失败, 权限错误", "Log");
            throw new STSAException("无权给小组删除成员", 401);
        }
        // 进行数据核对：待添加成员是否已存在且不为该组成员
        $sql = "SELECT 学号 FROM 工作信息 WHERE 所属组号=$groupCode and 学号='{$personID}';";
        $personCanAddResult = $session->query($sql);
        if ($personCanAddResult===false) {
            $logger->add_log(__FILE__.":".__LINE__, "removeGroupMember, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        $rows = $personCanAddResult->num_rows;
        if ($rows!==1) {
            $personCanAddResult->free();
            $logger->add_log(__FILE__ . ":" . __LINE__, "removeGroupMember, 成员{$personID}不可删除, 由于不是该组成员", "Log");
            throw new STSAException("由于不是该组成员", 417);
        }
        $personCanAddResult->free();
        // 核对成功，进行成员添加
        $sql = "DELETE FROM 工作信息 WHERE 所属组号=$groupCode and 学号='{$personID}';";
        $addResult = $session->query($sql);
        if ($addResult===false) {
            $logger->add_log(__FILE__.":".__LINE__, "removeGroupMember, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        $session->commit();
        $logger->add_log(__FILE__.":".__LINE__, "removeGroupMember, 删除成员成功", "Log");
        return [true];
    }
}

if(!function_exists("searchPreMember")) {
    /**
     * @param string $personalInfo
     * @return array
     * @throws STSAException
     */
    function searchPreMember(string $personalInfo=''): array{
        // 初始化变脸
        $session = new DatabaseConnector();
        $logger = new STSA_log();
        // 权限检查，需要队长或组长权限
        if(!check_authorization(["team_leader" => true, "group_leader" => true, "member" => false])) {
            $logger->add_log(__FILE__.":".__LINE__, "searchPreMember, 无权查看预备成员, 权限错误", "Log");
            throw new STSAException("无权查看预备成员", 401);
        }
        // 如果没有搜索要求（输入为空字符串），则提供所有成员
        if ($personalInfo==='') {
            $sql = "SELECT 成员信息.学号 as 学号,姓名,性别 FROM 成员信息,成员基本信息 WHERE 成员信息.学号=成员基本信息.学号 and 成员信息.学号 not in (SELECT DISTINCT 学号 FROM 工作信息);";
        }
        // 如果有搜索字段则进行搜索
        // FIXME: 处理注入
        else {
            $sql = "SELECT 成员信息.学号 as 学号,姓名,性别 FROM 成员信息,成员基本信息 WHERE 成员信息.学号=成员基本信息.学号 and (成员信息.学号 like '{$personalInfo}' or 姓名 like '{$personalInfo}');";
        }
        $prePersonResults = $session->query($sql);
        if ($prePersonResults===false) {
            $logger->add_log(__FILE__.":".__LINE__, "searchPreMember, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        $rows = $prePersonResults->num_rows;
        $fields = array_column($prePersonResults->fetch_fields(), "name");
        $prePersonResults = $prePersonResults->fetch_all(MYSQLI_ASSOC);
        return ["rows" => $rows, "fields" => $fields, "results" => $personalInfo];
    }
}