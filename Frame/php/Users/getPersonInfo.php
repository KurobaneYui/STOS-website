<?php
session_start();
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
// TODO:require log file

if (!function_exists("getOnePersonBasicInfo")) {
    function getOnePersonBasicInfo(string $searchID=""): array { // 用于返回某个人的非保密信息
        if ($searchID=="") { $searchID=$_SESSION["userID"]; } // 如不指定ID则默认使用登录者本人ID
        if ($searchID===$_SESSION["userID"]) {
            // check authorization 属于个人级别的本人非保密信息
            if (!check_authorization()) {
                throw new STSAException("无权限修改本人信息，可能由于未登录或登录信息已过期", 401);
            }
        }
        else {
            // check authorization 属于个人级别的他人非保密信息
            if(!check_authorization(['team_leader'=>true,'group_leader'=>true,'member'=>true])) {
                throw new STSAException("无权限查看他人信息",401);
            }
        }

        $session = new DatabaseConnector();
        $sql = "SELECT * FROM 成员非保密信息 WHERE `学号`='{$searchID}';";
        $PersonBasicInfos = $session->query($sql);
        if ($PersonBasicInfos===false) {
            throw new STSAException("数据库查询错误",417);
        }

        $rows = $PersonBasicInfos->num_rows;
        $fields = array_column($PersonBasicInfos->fetch_fields(),'name');
        $PersonBasicInfos = $PersonBasicInfos->fetch_all(MYSQLI_ASSOC)[0];
        return $PersonBasicInfos;
    }
}

if (!function_exists("getOnePersonAllInfo")) {
    function getOnePersonAllInfo(string $searchID=""): array { // 用于返回某个人的全部保密信息
        if ($searchID=="") { $searchID=$_SESSION["userID"]; } // 如不指定ID则默认使用登录者本人ID
        if ($searchID===$_SESSION["userID"]) {
            // check authorization 属于个人级别的本人保密信息
            if (!check_authorization()) {
                throw new STSAException("无权限修改本人信息，可能由于未登录或登录信息已过期", 401);
            }
        }
        else {
            // TODO:check authorization 属于个人级别的他人保密信息
            if(!check_authorization(['team_leader'=>true,'group_leader'=>true,'member'=>false,'groupID'=>000])) {
                throw new STSAException("无权限查看他人信息",401);
            }
        }

        $session = new DatabaseConnector();
        $sql = "SELECT * FROM 成员保密信息 WHERE `学号`='{$searchID}';";
        $PersonAllInfos = $session->query($sql);
        if ($PersonAllInfos===false) {
            throw new STSAException("数据库查询错误",417);
        }

        $rows = $PersonAllInfos->num_rows;
        $fields = array_column($PersonAllInfos->fetch_fields(),'name');
        return $PersonAllInfos->fetch_assoc();
    }
}

if (!function_exists("getGroupPersonBasicInfo")) {
    function getGroupPersonBasicInfo(int $GroupID): array { // 用于返回某组成员的非保密信息
        // check authorization 属于组级别的的他人非保密信息
        if(!check_authorization(['team_leader'=>true,'group_leader'=>true,'member'=>true])) {
            throw new STSAException("无权限查看组内他人信息",401);
        }

        $session = new DatabaseConnector();
        $sql =
            "SELECT * FROM 成员非保密信息
            WHERE `部门名称`=(SELECT `部门名称` FROM 部门信息 WHERE `部门编号`={$GroupID})";
        $PersonsBasicInfos = $session->query($sql);
        if ($PersonsBasicInfos===false) {
            throw new STSAException("数据库查询错误",417);
        }

        $rows = $PersonsBasicInfos->num_rows;
        $fields = array_column($PersonsBasicInfos->fetch_fields(),'name');
        $PersonsBasicInfos = $PersonsBasicInfos->fetch_all(MYSQLI_ASSOC);
        return ["行数"=>$rows,"列数"=>count($fields),"数据"=>$PersonsBasicInfos];
    }
}

if (!function_exists("getGroupPersonAllInfo")) {
    function getGroupPersonAllInfo(int $GroupID): array { // 用于返回某组成员的全部保密信息
        // check authorization 属于组级别的他人保密信息
        if(!check_authorization(['team_leader'=>true,'group_leader'=>true,'member'=>false,'groupID'=> $GroupID])) {
            throw new STSAException("无权限查看组内他人信息",401);
        }

        $session = new DatabaseConnector();
        $sql =
            "SELECT * FROM 成员保密信息
            WHERE `部门名称`=(SELECT `部门名称` FROM 部门信息 WHERE `部门编号`={$GroupID})";
        $PersonsAllInfos = $session->query($sql);
        if ($PersonsAllInfos===false) {
            throw new STSAException("数据库查询错误",417);
        }

        $rows = $PersonsAllInfos->num_rows;
        $fields = array_column($PersonsAllInfos->fetch_fields(),'name');
        $PersonsAllInfos = $PersonsAllInfos->fetch_all(MYSQLI_ASSOC);
        return ["行数"=>$rows,"列数"=>count($fields),"数据"=>$PersonsAllInfos];
    }
}

if (!function_exists("getPrePersonBasicInfo")) {
    function getPrePersonBasicInfo(): array { // 用于返回预备成员的基本非保密信息
        // check authorization 属于组级别的他人保密信息
        if(!check_authorization(['team_leader'=>true,'group_leader'=>true,'member'=>false])) {
            throw new STSAException("无权限查看预备队员信息",401);
        }

        $session = new DatabaseConnector();
        $sql =
            "SELECT * FROM 预备成员非保密信息 LIMIT 20;";
        $PrePersonsBasicInfos = $session->query($sql);
        if ($PrePersonsBasicInfos===false) {
            throw new STSAException("数据库查询错误",417);
        }

        $rows = $PrePersonsBasicInfos->num_rows;
        $fields = array_column($PrePersonsBasicInfos->fetch_fields(),'name');
        $PrePersonsBasicInfos = $PrePersonsBasicInfos->fetch_all(MYSQLI_ASSOC);
        return ["行数"=>$rows,"列数"=>count($fields),"字段"=>$fields,"数据"=>$PrePersonsBasicInfos];
    }
}
