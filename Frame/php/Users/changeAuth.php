<?php
session_start();
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/Tools/TranslateBetweenChineseAndEnglish.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

if(!function_exists("changeMemberAuth")) {
    /**
     * @param string $personID
     * @param array $authStringArray
     * @param int $groupCode
     * @return array
     * @throws STSAException
     * @throws JsonException
     */
    function changeMemberAuth(string $personID, array $authStringArray, int $groupCode=-1): array{
        // 准备参数环境
        $session = new DatabaseConnector();
        $logger = new STSA_log();
        // 检查authStringArray输入数组有两个元素均为字符，第一个为"+","-"中的一个，第二个为"team","group","member"中的一个
        // 如果authStringArray第二个参数是team，则增删队长权限不改变其他
        // 如果authStringArray第二个参数是group，则增加时同时享有组员权限，删除时自动变更为对应组组员权限，不改变队长等其他权限
        // 如果authStringArray第二个参数是member，则添加时自动去除组长权限仅设置为组员权限，删除时则直接去除对应组组长和组员权限，不改变队长等其他权限
        if (!isset($authStringArray[0], $authStringArray[1])) {
            $logger->add_log(__FILE__.":".__LINE__, "changeMemberAuth, 提供的输入格式错误", "Log");
            throw new STSAException("输入参数有误", 400);
        }
        if (($authStringArray[0] !== "+" && $authStringArray[0] !== "-") || ($authStringArray[1] !== "team" && $authStringArray[1] !== "group" && $authStringArray[1] !== "member")) {
            $logger->add_log(__FILE__.":".__LINE__, "changeMemberAuth, 提供的输入格式错误", "Log");
            throw new STSAException("输入参数有误", 400);
        }
        // 检查数据合法性: $personID is existed
        $sql = "SELECT 学号 FROM 成员信息 WHERE 学号='{$personID}';";
        $personResult = $session->query($sql);
        if ($personResult===false) {
            $logger->add_log(__FILE__.":".__LINE__, "changeMemberAuth, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        $rows = $personResult->num_rows;
        if ($rows<1) {
            $personResult->free();
            $logger->add_log(__FILE__ . ":" . __LINE__, "changeMemberAuth, 改变权限失败, 成员{$personID}不存在", "Log");
            throw new STSAException("成员不存在", 400);
        }
        $personResult->free();
        // 检查数据合法性: groupCode is existed or =-1
        if ($groupCode!==-1) {
            $sql = "SELECT 部门编号 FROM 部门信息 WHERE 部门编号={$groupCode};";
            $groupCodeResult = $session->query($sql);
            if ($groupCodeResult === false) {
                $logger->add_log(__FILE__ . ":" . __LINE__, "changeMemberAuth, 数据库查询错误", "Error");
                throw new STSAException("数据库查询错误", 417);
            }
            $rows = $groupCodeResult->num_rows;
            if ($rows < 1) {
                $groupCodeResult->free();
                $logger->add_log(__FILE__ . ":" . __LINE__, "changeMemberAuth, 改变权限失败, 组号{$groupCode}不存在", "Log");
                throw new STSAException("组号不存在", 400);
            }
            $groupCodeResult->free();
        }
        // 检查权限
        if(($authStringArray[1]==="team" || $authStringArray[1]==="group") && !check_authorization(["team_leader" => true, "group_leader" => false, "member" => false])) {
            $logger->add_log(__FILE__.":".__LINE__, "changeMemberAuth, 无权查看预备成员, 权限错误", "Log");
            throw new STSAException("无权查看预备成员", 401);
        }
        elseif($authStringArray[1]==="member" && !check_authorization(["team_leader" => true, "group_leader" => true, "member" => false, "groupID" => $groupCode])) {
            $logger->add_log(__FILE__.":".__LINE__, "changeMemberAuth, 无权查看预备成员, 权限错误", "Log");
            throw new STSAException("无权查看预备成员", 401);
        }
        // 开始权限变更
        // 获取已有权限
        $sql = "SELECT 权限 FROM 权限信息 WHERE 学号='{$personID}';";
        $authResult = $session->query($sql);
        if ($authResult===false) {
            $logger->add_log(__FILE__.":".__LINE__, "changeMemberAuth, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        if ($authResult->num_rows<1) {
            $authResult->free();
            $authResult = [
                "data" => [
                    "check" => false,
                    "change" => false
                ],
                "super" => false,
                "team_leader" => false,
                "groups" => []
            ];
        }
        else {
            $authResult = $authResult->fetch_all(MYSQLI_ASSOC);
            if (isset($authResult[0]["权限"]) && $authResult[0]["权限"]==="") {
                $authResult = [
                    "data" => [
                        "check" => false,
                        "change" => false
                    ],
                    "super" => false,
                    "team_leader" => false,
                    "groups" => []
                ];
            }
            else {
                $authResult = json_decode($authResult[0]["权限"], true, 512, JSON_THROW_ON_ERROR);
            }
        }
        // 首先判断是否变更队长部分的权限
        if ($authStringArray[1]==="team") {
            if ($authStringArray[0]==="+") {
                $authResult["team_leader"]=true;
            }
            elseif ($authStringArray[0]==="-") {
                $authResult["team_leader"]=false;
            }
        }
        // 判断是否变更组长权限
        elseif ($authStringArray[1]==="group") {
            if ($authStringArray[0]==="+") {
                $authResult["groups"][$groupCode]=["group_leader"=>true];
            }
            elseif ($authStringArray[0]==="-") {
                $authResult["groups"][$groupCode]=["group_leader"=>false];
            }
        }
        // 判断是否变更组员权限
        elseif ($authStringArray[1]==="member") {
            if ($authStringArray[0]==="+") {
                $authResult["groups"][$groupCode]=["group_leader"=>false];
            }
            elseif ($authStringArray[0]==="-") {
                unset($authResult["groups"][$groupCode]);
            }
        }
        // 更新数据库权限信息
        $authResult = json_encode($authResult, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $sql = "INSERT INTO 权限信息 (学号,权限) VALUES (\"{$personID}\",\"{$authResult}\") ON DUPLICATE KEY UPDATE 权限=\"{$authResult}\";";
        $changeAuthResult = $session->query($sql);
        if ($changeAuthResult===false) {
            $logger->add_log(__FILE__ . ':' . __LINE__, "changeMemberAuth, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        $session->commit();
        $logger->add_log(__FILE__ . ':' . __LINE__, "changeMemberAuth, 修改成员{$personID}的权限:{$authStringArray[0]}{$authStringArray[1]}, 修改成功", "Log");
        return [true];
    }
}