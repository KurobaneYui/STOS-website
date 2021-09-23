<?php
if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

if (!function_exists("getContactInfo")) {
    /**
     * This function used to get team members' contact info
     * @return array
     * @throws STSAException
     */
    function getContactInfo(): array{ // 用于返回全队联系方式信息
        $logger = new STSA_log();
        // check authorization 属于队伍级别的联系方式权限
        if(!check_authorization(['team_leader'=>true,'group_leader'=>true,'member'=>true])) {
            $logger->add_log(__FILE__.":".__LINE__, "getContactInfo, 查看他人联系方式失败, 权限错误", "Log");
            throw new STSAException("无权限查看他人联系方式信息",401);
        }

        $session = new DatabaseConnector();
        $sql =
            "SELECT (@i:=@i+1)'序号',`姓名`, `性别`, `QQ`, `电话`, `部门名称` as '所属组', `岗位` FROM 成员非保密信息,(SELECT @i:=0)b;";
        $ContactInfos = $session->query($sql);
        if ($ContactInfos===false) {
            $logger->add_log(__FILE__.":".__LINE__, "getContactInfo, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误",417);
        }

        $rows = $ContactInfos->num_rows;
        $fields = array_column($ContactInfos->fetch_fields(),'name');
        $ContactInfos = $ContactInfos->fetch_all(MYSQLI_ASSOC);
        $logger->add_log(__FILE__.":".__LINE__, "getContactInfo, get contact info successfully", "Log");
        return ["行数"=>$rows,"列数"=>count($fields),'表头'=>$fields,"数据"=>$ContactInfos];
    }
}