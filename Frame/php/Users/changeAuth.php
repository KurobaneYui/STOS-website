<?php
if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/Tools/TranslateBetweenChineseAndEnglish.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

if(!function_exists("showMemberAuth")) {
    /**
     * @return array
     * @throws STSAException
     */
    function showMemberAuth(): array{
        // 准备参数环境
        $session = new DatabaseConnector();
        $logger = new STSA_log();
        // 检查权限
        if(!check_authorization(["team_leader" => true, "group_leader" => false, "member" => false])) {
            $logger->add_log(__FILE__.":".__LINE__, "showMemberAuth, 无权查看成员权限, 权限错误", "Log");
            throw new STSAException("无权查看成员权限", 401);
        }
        // 查询权限并返回
        $returns = [];
        $sql = "SELECT 部门编号 FROM 部门信息 ORDER BY 部门编号;";
        $groupCodesResult = $session->query($sql);
        if ($groupCodesResult===false) {
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__.":".__LINE__, "showMemberAuth, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        $groupCodesResult = array_column($groupCodesResult->fetch_all(MYSQLI_ASSOC),'部门编号');
        foreach ($groupCodesResult as $value) {
            $sql = "SELECT 姓名,成员基本信息.学号 AS '学号',部门名称,岗位 FROM 成员基本信息,工作信息,部门信息 WHERE 工作信息.学号=成员基本信息.学号 and 工作信息.所属组号=部门信息.部门编号 and 部门信息.部门编号={$value} ORDER BY 岗位 DESC;";
            $fullMemberAuthResult = $session->query($sql);
            if ($fullMemberAuthResult===false) {
                $errorList2string = mysqli_error($session->getSession());
                $logger->add_log(__FILE__.":".__LINE__, "showMemberAuth, 数据库查询错误：{$errorList2string}", "Error");
                throw new STSAException("数据库查询错误", 417);
            }
            $rows = $fullMemberAuthResult->num_rows;
            $fields = array_column($fullMemberAuthResult->fetch_fields(),'name');
            $cols = count($fields);
            $fullMemberAuthResult = $fullMemberAuthResult->fetch_all(MYSQLI_ASSOC);
            $name = $fullMemberAuthResult[0]["部门名称"];
            $returns[] = ["行数"=>$rows,"列数"=>$cols,"部门名称"=>$name,"表头"=>$fields,"数据"=>$fullMemberAuthResult];
        }
        return $returns;
    }
}

if(!function_exists("changeMemberAuth")) {
    /**
     * @param string $personID
     * @param array $authStringArray
     * @param int $groupCode
     * @return array
     * @throws STSAException
     * @throws JsonException
     */
    function changeMemberAuth(string $personID, array $authStringArray, int $groupCode): array{
        // 准备参数环境
        $session = new DatabaseConnector();
        $logger = new STSA_log();
        // 检查authStringArray输入数组有两个元素均为字符，第一个为"+","-"中的一个，第二个为"group","member"中的一个
        // 如果authStringArray第二个参数是group，则增加时同时享有组员权限，删除时自动变更为对应组组员权限，不改变队长等其他权限
        // 如果authStringArray第二个参数是member，则添加时自动去除组长权限仅设置为组员权限，删除时则直接去除对应组组长和组员权限，不改变队长等其他权限
        // 每次还会检查groupCode是否为队长组，增删时会影响队长权限和组长权限，如果是队长组成员则自动有队长权限和队长组组长权限，如果不是则自动去除队长权限
        // 每次还会修正数据权限，队长有数据查看和导出权限，现场组组员和组长有数据查看、修改权限，数据组组员有数据查看、修改权限，数据组组长有数据查看、修改、导入和导出权限
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
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__.":".__LINE__, "changeMemberAuth, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        $rows = $personResult->num_rows;
        if ($rows<1) {
            $personResult->fetch_all();
            $logger->add_log(__FILE__ . ":" . __LINE__, "changeMemberAuth, 改变权限失败, 成员{$personID}不存在", "Log");
            throw new STSAException("成员不存在", 400);
        }
        $personResult->fetch_all();
        // 检查数据合法性: groupCode is existed
        $sql = "SELECT 部门编号 FROM 部门信息 WHERE 部门编号={$groupCode};";
        $groupCodeResult = $session->query($sql);
        if ($groupCodeResult === false) {
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__ . ":" . __LINE__, "changeMemberAuth, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        $rows = $groupCodeResult->num_rows;
        if ($rows < 1) {
            $groupCodeResult->fetch_all();
            $logger->add_log(__FILE__ . ":" . __LINE__, "changeMemberAuth, 改变权限失败, 组号{$groupCode}不存在", "Log");
            throw new STSAException("组号不存在", 400);
        }
        $groupCodeResult->fetch_all();
        // 检查权限
        if($authStringArray[1]==="group" && !check_authorization(["team_leader" => true, "group_leader" => false, "member" => false])) {
            $logger->add_log(__FILE__.":".__LINE__, "changeMemberAuth, 无权修改组长级权限, 权限错误", "Log");
            throw new STSAException("无权修改组长级权限", 401);
        }
        elseif($authStringArray[1]==="member" && !check_authorization(["team_leader" => true, "group_leader" => true, "member" => false, "groupID" => $groupCode])) {
            $logger->add_log(__FILE__.":".__LINE__, "changeMemberAuth, 无权修改成员级权限, 权限错误", "Log");
            throw new STSAException("无权修改成员级权限", 401);
        }
        // 开始权限变更
        // 获取已有权限
        $sql = "SELECT 权限 FROM 权限信息 WHERE 学号='{$personID}';";
        $authResult = $session->query($sql);
        if ($authResult===false) {
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__.":".__LINE__, "changeMemberAuth, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        if ($authResult->num_rows<1) {
            $authResult->fetch_all();
            $authResult = [
                "data" => [
                    "check" => false,
                    "change" => false,
                    "input" => false,
                    "output" => false
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
                        "change" => false,
                        "input" => false,
                        "output" => false
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
        // 获取队长组组号、数据组组号和现场组组号
        $leaderCode = getGroupCode("队长");
        $xianchangzuCodes = getGroupsCodes("现场组%");
        $shujuzuCode = getGroupCode("数据组");
        // 判断是否变更组长权限
        if ($authStringArray[1]==="group") {
            require_once ROOT_PATH . "/Frame/php/Users/getGroupCode.php";
            if ($authStringArray[0]==="+") {
                $authResult["groups"][$groupCode]=["group_leader"=>true];
                $sql = "UPDATE 工作信息 SET 岗位='组长',基本工资='350' WHERE 学号='{$personID}' and 所属组号={$groupCode};";
                if ($groupCode===$leaderCode) {
                    $authResult["team_leader"]=true;
                    $sql = "UPDATE 工作信息 SET 岗位='队长',基本工资='350' WHERE 学号='{$personID}' and 所属组号={$groupCode};";
                }
                $session->query($sql);
                $session->commit();
            }
            elseif ($authStringArray[0]==="-") {
                $authResult["groups"][$groupCode]=["group_leader"=>false];
                $sql = "UPDATE 工作信息 SET 岗位='组员',基本工资='300' WHERE 学号='{$personID}' and 所属组号={$groupCode};";
                if ($leaderCode===$groupCode) {
                    $authResult["groups"][$groupCode]=["group_leader"=>true];
                    $authResult["team_leader"]=true;
                    $sql = "UPDATE 工作信息 SET 岗位='队长',基本工资='350' WHERE 学号='{$personID}' and 所属组号={$groupCode};";
                }
                $session->query($sql);
                $session->commit();
            }
        }
        // 判断是否变更组员权限
        elseif ($authStringArray[1]==="member") {
            if ($authStringArray[0]==="+") {
                $authResult["groups"][$groupCode]=["group_leader"=>false];
                $sql = "UPDATE 工作信息 SET 岗位='组员',基本工资='300' WHERE 学号='{$personID}' and 所属组号={$groupCode};";
                if ($groupCode===$leaderCode) {
                    $authResult["groups"][$groupCode]=["group_leader"=>true];
                    $authResult["team_leader"]=true;
                    $sql = "UPDATE 工作信息 SET 岗位='队长',基本工资='350' WHERE 学号='{$personID}' and 所属组号={$groupCode};";
                }
                $session->query($sql);
                $session->commit();
            }
            elseif ($authStringArray[0]==="-") {
                unset($authResult["groups"][$groupCode]);
                if ($leaderCode===$groupCode) {
                    $authResult["team_leader"]=false;
                }
            }
        }
        // 更新数据权限
        if ($authResult["super"]===true) {
            $authResult["data"]["check"] = true;
            $authResult["data"]["change"] = true;
            $authResult["data"]["input"] = true;
            $authResult["data"]["output"] = true;
        }
        else {
            // 队长的数据权限
            if ($authResult["team_leader"] === true) {
                $authResult["data"]["check"] = true;
                $authResult["data"]["output"] = true;
            }
            // 检查组长组员的数据权限
            foreach ($authResult["groups"] as $key=>$value) {
                // 如果是现场组的组员或组长
                if (in_array($key,$xianchangzuCodes)) {
                    $authResult["data"]["check"] = true;
                    $authResult["data"]["change"] = true;
                }
                // 如果是数据组的组员（默认是组员）
                if ($key===$shujuzuCode) {
                    $authResult["data"]["check"] = true;
                    $authResult["data"]["change"] = true;
                    // 如果还是组长
                    if ($authResult["groups"][$key]["group_leader"]===true) {
                        $authResult["data"]["check"] = true;
                        $authResult["data"]["change"] = true;
                        $authResult["data"]["input"] = true;
                        $authResult["data"]["output"] = true;
                    }
                }
            }
        }
        // 更新数据库权限信息
        $authResult = json_encode($authResult, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $sql = "INSERT INTO 权限信息 (学号,权限) VALUES ('{$personID}','{$authResult}') ON DUPLICATE KEY UPDATE 权限='{$authResult}';";
        $changeAuthResult = $session->query($sql);
        if ($changeAuthResult===false) {
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__ . ':' . __LINE__, "changeMemberAuth, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        $session->commit();
        $logger->add_log(__FILE__ . ':' . __LINE__, "changeMemberAuth, 修改成员{$personID}的权限:{$authStringArray[0]}{$authStringArray[1]}, 修改成功", "Log");
        return [true];
    }
}