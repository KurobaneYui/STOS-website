<?php
if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); }

if(!function_exists("check_authorization")){
    /**
     * This function is used to make authorization
     * input is expect authorization code. And if have right to do query, will return true, otherwise will return false.
     * @param array $auth_request two params request: team_leader(bool), group_leader(bool), member(bool); and one param option: groupID(int)
     * @return bool
     * @throws STSAException
     * @package php\Tools
     * @author LuoYinsong
     */
    function check_authorization(array $auth_request=array(), array $auth_request_for_data=array("check"=>false,"change"=>false,"input"=>false,"output"=>false)): bool{
        require_once __DIR__ . '/../../../ROOT_PATH.php';
        require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
        require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
        require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
        require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";
        $logger = new STSA_log();

        // 未登录则无权进行任何操作
        if(!isset($_SESSION["userID"])) {
            $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, not login (no userID in session)", "Log");
            return false;
        }
        // SESSION_ID不一致无权进行任何操作
        if(!isset($_SESSION['isLogin']) || $_SESSION['isLogin'] !== hash('sha256',session_id().$_SESSION['userID'].'true')) {
            $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, not login (sessionID not match isLogin)", "Log");
            return false;
        }
        // 如果无输入参数，或输入是空数组，则表示操作自己的信息，只检查是否登录，以及数据是否一致
        if(is_array($auth_request) && empty($auth_request)) {
            $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, auth_request is empty array, allow process oneself infos", "Log");
            return true;
        }
        // 查询权限信息
        $session = new DatabaseConnector();
        $sql = "SELECT 权限 FROM 权限信息 WHERE `学号`='{$_SESSION["userID"]}';";
        $PersonAuthorization = $session->query($sql);
        if ($PersonAuthorization===false) {
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误",417);
        }
        $rows = $PersonAuthorization->num_rows;
        $fields = array_column($PersonAuthorization->fetch_fields(),'name');
        $PersonAuthorization = $PersonAuthorization->fetch_all(MYSQLI_ASSOC)[0]['权限'];
        try {
            $auth = json_decode($PersonAuthorization, true, 512, JSON_THROW_ON_ERROR);
        }
        catch (JsonException $e) {
            $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, json解包错误, 待解包数据为：\n{$PersonAuthorization}", "Error");
            return false;
        }
        // 检查数据权限
        foreach ($auth["data"] as $key=>$value) {
            if($value===false && $auth_request_for_data[$key]===true) {
                $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, data rights check error, not allowed", "Log");
                return false;
            }
        }
        // 检查身份权限
        if($auth['super']) { // 超级权限放行
            $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, super rights, allow", "Log");
            return true;
        }
        if($auth['team_leader']) { // 如果有队长权限，判断是否在需求区间
            if($auth_request['team_leader']) {
                $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, team leader rights, allow", "Log");
                return true;
            }
        }
        // 如果队长权限不在需求区间，则判断是否有组长权限，且是否在需求区间
        if($auth_request['group_leader']) {
            // 如果严格要求组号
            if (isset($auth_request['groupID'])) {
                // 如果有对应组号的组长权限则放行
                if (isset($auth['groups'][$auth_request['groupID']]) && $auth['groups'][$auth_request['groupID']]["group_leader"]) {
                    $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, group leader rights and match the group required, allow", "Log");
                    return true;
                }
            }
            // 如果不严格要求组号，只要有组长权限则放行
            else {
                foreach ($auth['groups'] as $value) {
                    if ($value['group_leader']) {
                        $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, group leader rights and do not need to match the group, allow", "Log");
                        return true;
                    }
                }
            }
        }
        // 如果队长权限和组长权限均不符合要求，则判断组员权限
        if($auth_request['member']) {
            // 如果严格要求组号
            if (isset($auth_request['groupID'])) {
                if (isset($auth['groups'][$auth_request['groupID']])) {
                    $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, member rights and match the group required, allow", "Log");
                    return true;
                }
            }
            // 如果不严格要求组号，只要是成员就放行
            elseif (!empty($auth['groups'])) {
                // 非空则说明是某个组的组员
                $logger->add_log(__FILE__ . ':' . __LINE__, "Check authorization, member rights and do not need to match the group, allow", "Log");
                return true;
            }
        }
        // 没有一个匹配的则禁止
        $logger->add_log(__FILE__.':'.__LINE__, "Check authorization, no rights matched, not allow", "Log");
        return false;
    }
}