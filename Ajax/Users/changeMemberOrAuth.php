<?php
if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../ROOT_PATH.php';
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

$logger = new STSA_log();

if (isset($_POST['requestFunction'])) { // 是否有要请求的类别
    if ($_POST['requestFunction'] === 'getFullMembersAuth') {
        require_once ROOT_PATH . '/Frame/php/Users/changeAuth.php';
        try {
            $returns = new UnionReturnInterface();
            $returns->setData(showMemberAuth());
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-changeMemberOrAuth, function execute successfully", "Log");
            echo $returns;
        } catch (JsonException $e) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-changeMemberOrAuth, json相关错误, 错误信息:\n{$e}", "Error");
            $returns = new UnionReturnInterface('417', '数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-changeMemberOrAuth, meet customException, 错误信息:\n{$returns}", "Warning");
            echo $returns;
        }
    } elseif ($_POST['requestFunction'] === 'changeAuth') {
        require_once ROOT_PATH . '/Frame/php/Users/getGroupCode.php';
        require_once ROOT_PATH . '/Frame/php/Users/changeAuth.php';
        try {
            $returns = new UnionReturnInterface();
            // 从POST信息里获取数据
            if (!isset($_POST["personID"], $_POST["groupName"], $_POST["work"])) {
                $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-changeAuth, 没有提供需要的数据", "Log");
                $returns = new UnionReturnInterface('400', "没有提供需要的数据");
                echo $returns;
                exit;
            }
            if ($_POST["work"] !== "team" && $_POST["work"] !== "group" && $_POST["work"] !== "member") {
                $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-changeAuth, 没有提供正确的数据", "Log");
                $returns = new UnionReturnInterface('400', "没有提供正确的数据");
                echo $returns;
                exit;
            }
            $authPack = ["+", $_POST["work"]];
            // 执行主体
            $returns->setData(changeMemberAuth($_POST["personID"], $authPack, getGroupCode($_POST["groupName"])));
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-changeAuth, function execute successfully", "Log");
            echo $returns;
        } catch (JsonException $e) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-changeAuth, json相关错误, 错误信息:\n{$e}", "Error");
            $returns = new UnionReturnInterface('417', '数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-changeAuth, meet customException, 错误信息:\n{$returns}", "Warning");
            echo $returns;
        }
    } elseif ($_POST['requestFunction'] === 'getGroupFullMembersForWorkChange') {
        require_once ROOT_PATH . '/Frame/php/Users/getGroupCode.php';
        require_once ROOT_PATH . '/Frame/php/Users/getPersonInfo.php';
        try {
            $InfosInGroups = [];
            $returns = new UnionReturnInterface();
            $groupCodeResult = getGroupCodeForTeamOrGroupLeader();
            foreach ($groupCodeResult as $value) {
                $tmp = getGroupPersonBasicInfo($value);
                $rows = $tmp["行数"];
                $fields = ["姓名", "学号", "性别", "部门名称"];
                $cols = count($fields);
                $name = array_column($tmp["数据"], "姓名");
                $ID = array_column($tmp["数据"], "学号");
                $gender = array_column($tmp["数据"], "性别");
                $groupName = array_column($tmp["数据"], "部门名称");
                $tmp = [];
                foreach ($name as $key => $value_) {
                    $tmp[$key]["姓名"] = $value_;
                    $tmp[$key]["学号"] = $ID[$key];
                    $tmp[$key]["性别"] = $gender[$key];
                    $tmp[$key]["部门名称"] = $groupName[$key];
                }
                $InfosInGroups[] = ["行数" => $rows, "列数" => $cols, "字段" => $fields, "数据" => $tmp];
            }
            $returns->setData($InfosInGroups);
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-getGroupFullMembersForWorkChange, function execute successfully", "Log");
            echo $returns;
        } catch (JsonException $e) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-getGroupFullMembersForWorkChange, json相关错误, 错误信息:\n{$e}", "Error");
            $returns = new UnionReturnInterface('417', '数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-getGroupFullMembersForWorkChange, meet customException, 错误信息:\n{$returns}", "Warning");
            echo $returns;
        }
    } elseif ($_POST['requestFunction'] === 'getPreMembersForWorkChange') {
        require_once ROOT_PATH . '/Frame/php/Users/getPersonInfo.php';
        try {
            $returns = new UnionReturnInterface();
            $tmp = getPrePersonBasicInfo();
            $rows = $tmp["行数"];
            $fields = ["姓名", "学号", "性别"];
            $cols = count($fields);
            $name = array_column($tmp["数据"], "姓名");
            $ID = array_column($tmp["数据"], "学号");
            $gender = array_column($tmp["数据"], "性别");
            $tmp = [];
            foreach ($name as $key => $value_) {
                $tmp[$key]["姓名"] = $value_;
                $tmp[$key]["学号"] = $ID[$key];
                $tmp[$key]["性别"] = $gender[$key];
            }
            $returns->setData(["行数" => $rows, "列数" => $cols, "字段" => $fields, "数据" => $tmp]);
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-getPreMembersForWorkChange, function execute successfully", "Log");
            echo $returns;
        } catch (JsonException $e) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-getPreMembersForWorkChange, json相关错误, 错误信息:\n{$e}", "Error");
            $returns = new UnionReturnInterface('417', '数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-getPreMembersForWorkChange, meet customException, 错误信息:\n{$returns}", "Warning");
            echo $returns;
        }
    } elseif ($_POST['requestFunction'] === 'searchMember') {
        require_once ROOT_PATH . '/Frame/php/Users/changeGroupMember.php';
        if (!isset($_POST["searchString"])) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-searchMember, 没有提供需要的数据", "Log");
            $returns = new UnionReturnInterface('400', "没有提供需要的数据");
            echo $returns;
            exit;
        }
        if ($_POST["searchString"] === "") {
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-searchMember, 没有提供正确的数据", "Log");
            $returns = new UnionReturnInterface('400', "没有提供正确的数据");
            echo $returns;
            exit;
        }
        try {
            $returns = new UnionReturnInterface();
            $returns->setData(searchPreMember($_POST["searchString"]));
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-searchMember, function execute successfully", "Log");
            echo $returns;
        } catch (JsonException $e) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-searchMember, json相关错误, 错误信息:\n{$e}", "Error");
            $returns = new UnionReturnInterface('417', '数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-searchMember, meet customException, 错误信息:\n{$returns}", "Warning");
            echo $returns;
        }
    } elseif ($_POST['requestFunction'] === 'addMember') {
        require_once ROOT_PATH . '/Frame/php/Users/getGroupCode.php';
        require_once ROOT_PATH . '/Frame/php/Users/changeGroupMember.php';
        if (!isset($_POST["personID"], $_POST["groupName"])) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-addMember, 没有提供需要的数据", "Log");
            $returns = new UnionReturnInterface('400', "没有提供需要的数据");
            echo $returns;
            exit;
        }
        try {
            $returns = new UnionReturnInterface();
            $groupCodeResult = getGroupCode($_POST["groupName"]);
            $returns->setData(addGroupMember($_POST["personID"], $groupCodeResult));
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-addMember, function execute successfully", "Log");
            echo $returns;
        } catch (JsonException $e) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-addMember, json相关错误, 错误信息:\n{$e}", "Error");
            $returns = new UnionReturnInterface('417', '数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-addMember, meet customException, 错误信息:\n{$returns}", "Warning");
            echo $returns;
        }
    } elseif ($_POST['requestFunction'] === 'removeMember') {
        require_once ROOT_PATH . '/Frame/php/Users/getGroupCode.php';
        require_once ROOT_PATH . '/Frame/php/Users/changeGroupMember.php';
        if (!isset($_POST["personID"], $_POST["groupName"])) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-removeMember, 没有提供需要的数据", "Log");
            $returns = new UnionReturnInterface('400', "没有提供需要的数据");
            echo $returns;
            exit;
        }
        try {
            $returns = new UnionReturnInterface();
            $groupCodeResult = getGroupCode($_POST["groupName"]);
            $returns->setData(removeGroupMember($_POST["personID"], $groupCodeResult));
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-removeMember, function execute successfully", "Log");
            echo $returns;
        } catch (JsonException $e) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-removeMember, json相关错误, 错误信息:\n{$e}", "Error");
            $returns = new UnionReturnInterface('417', '数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
            $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-removeMember, meet customException, 错误信息:\n{$returns}", "Warning");
            echo $returns;
        }
    } else {
        $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-changeMemberOrAuth, 功能不存在", "Log");
        $returns = new UnionReturnInterface('404', "功能不存在");
        echo $returns;
    }
} else {
    $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-changeMemberOrAuth, 没有选择需要的功能", "Log");
    $returns = new UnionReturnInterface('404', "没有选择需要的功能");
    echo $returns;
}