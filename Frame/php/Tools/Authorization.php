<?php
session_start();

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
    function check_authorization(array $auth_request=array()): bool{
        require_once __DIR__ . '/../../../ROOT_PATH.php';
        require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
        require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
        require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
        //TODO: add log function

        // 未登录则无权进行任何操作
        if(!isset($_SESSION["userID"])) {
            return false;
        }
        // SESSION_ID不一致无权进行任何操作
        if(!isset($_SESSION['isLogin']) || $_SESSION['isLogin'] !== hash('sha256',session_id().$_SESSION['userID'].'true')) {
            return false;
        }
        // 如果无输入参数，或输入是空数组，则表示操作自己的信息，只检查是否登录，以及数据是否一致
        if(is_array($auth_request) && empty($auth_request)) {
            return true;
        }
        // 查询权限信息
        $session = new DatabaseConnector();
        $sql = "SELECT 权限 FROM 权限信息 WHERE `学号`='{$_SESSION["userID"]}';";
        $PersonAuthorization = $session->query($sql);
        if ($PersonAuthorization===false) {
            throw new STSAException("数据库查询错误",417);
        }
        $rows = $PersonAuthorization->num_rows;
        $fields = array_column($PersonAuthorization->fetch_fields(),'name');
        $PersonAuthorization = $PersonAuthorization->fetch_all(MYSQLI_ASSOC)[0]['权限'];
        try {
            $auth = json_decode($PersonAuthorization, true, 512, JSON_THROW_ON_ERROR);
        }
        catch (JsonException $e) {
            return false;
        }
        if($auth['super']) { return true; } // 超级权限放行
        // 如果有队长权限，判断是否在需求区间
        if($auth['team_leader']) {
            if($auth_request['team_leader']) {
                return true;
            }
        }
        // 如果队长权限不在需求区间，则判断是否有组长权限，且是否在需求区间
        if($auth_request['group_leader']) {
            foreach ($auth['groups'] as $value) {
                // 如果成员有组长权限
                if ($value['group_leader']) {
                    // 严格要求组号时，只有组号也对应才放行
                    if (isset($auth_request['groupID'])) {
                        if ($auth_request['groupID'] === $value['groupID']) {
                            return true;
                        }
                    } // 不严格要求组号时，只要有组长权限则放行
                    else {
                        return true;
                    }
                }
            }
        }
        // 如果队长权限和组长权限均不符合要求，则判断组员权限
        if($auth_request['member']) {
            foreach ($auth['groups'] as $value) {
                // 能进入foreach循环说明是某个组的组员
                // 严格要求组号时，只有组号也对应才放行
                if (isset($auth_request['groupID'])) {
                    if ($auth_request['groupID'] === $value['groupID']) {
                        return true;
                    }
                } // 不严格要求组号时，只要是成员就放行
                else {
                    return true;
                }
            }
        }
        // 没有一个匹配的则禁止
        return false;
    }
}