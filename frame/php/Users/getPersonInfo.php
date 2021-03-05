<?php
session_start();
require_once __DIR__."/../../../ROOT_PATH.php";
require_once ROOT_PATH."/frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH."/frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/frame/php/Connector/DatabaseConnector.php";
// TODO:require log file
// TODO:require authorization file

if (!function_exists("getOnePersonBasicInfo")) {
    function getOnePersonBasicInfo(string $searchID=""): mysqli_result { // 用于返回某个人的非保密信息
        if ($searchID=="") { $searchID=$_SESSION["userID"]; } // 如不指定ID则默认使用登录者本人ID
        // TODO:check authorization 属于个人级别的本人非保密信息；或个人级别的他人非保密信息

        $session = new DatabaseConnector();
        $sql = "SELECT * FROM 成员非保密信息 WHERE `学号`='{$searchID}';";
        $PersonBasicInfos = $session->query($sql);
        if ($PersonBasicInfos===false) {
            throw; // TODO:Throw Errors 417 数据库查询错误
        }

        $rows = $PersonBasicInfos->num_rows;
        $fields = $PersonBasicInfos->fetch_fields();
        $PersonBasicInfos = $PersonBasicInfos->fetch_all(MYSQLI_ASSOC);
        return $PersonBasicInfos;
    }
}

if (!function_exists("getOnePersonAllInfo")) {
    function getOnePersonAllInfo(string $searchID=""): mysqli_result { // 用于返回某个人的全部保密信息
        if ($searchID=="") { $searchID=$_SESSION["userID"]; } // 如不指定ID则默认使用登录者本人ID
        // TODO:check authorization 属于个人级别的本人保密信息；或个人级别的他人保密信息

        $session = new DatabaseConnector();
        $sql = "SELECT * FROM 成员保密信息 WHERE `学号`='{$searchID}';";
        $PersonAllInfos = $session->query($sql);
        if ($PersonAllInfos===false) {
            throw;// TODO:Throw Errors 417 数据库查询错误
        }

        $rows = $PersonAllInfos->num_rows;
        $fields = $PersonAllInfos->fetch_fields();
        $PersonAllInfos = $PersonAllInfos->fetch_all(MYSQLI_ASSOC);
        return $PersonAllInfos;
    }
}

if (!function_exists("getAllPersonBasicInfo")) {
    function getGroupPersonBasicInfo(string $GroupID): array { // 用于返回某组成员的非保密信息
        // TODO:check authorization 属于组级别的的他人非保密信息

        $session = new DatabaseConnector();
        $sql =
            "SELECT * FROM 成员非保密信息
            WHERE `部门名称`=(SELECT `部门名称` FROM 部门信息 WHERE `部门编号`={$GroupID})";
        $PersonsBasicInfos = $session->query($sql);
        if ($PersonsBasicInfos===false) {
            throw;// TODO:Throw Errors 417 数据库查询错误
        }

        $rows = $PersonsBasicInfos->num_rows;
        $fields = $PersonsBasicInfos->fetch_fields();
        $PersonsBasicInfos = $PersonsBasicInfos->fetch_all(MYSQLI_ASSOC);
        return ["行数"=>$rows,"列数"=>count($fields),"数据"=>$PersonsBasicInfos];
    }
}

if (!function_exists("getAllPersonAllInfo")) {
    function getGroupPersonAllInfo(string $GroupID): array { // 用于返回某组成员的全部保密信息
        // TODO:check authorization 属于组级别的他人保密信息

        $session = new DatabaseConnector();
        $sql =
            "SELECT * FROM 成员保密信息
            WHERE `部门名称`=(SELECT `部门名称` FROM 部门信息 WHERE `部门编号`={$GroupID})";
        $PersonsAllInfos = $session->query($sql);
        if ($PersonsAllInfos===false) {
            throw;// TODO:Throw Errors 417 数据库查询错误
        }

        $rows = $PersonsAllInfos->num_rows;
        $fields = $PersonsAllInfos->fetch_fields();
        $PersonsAllInfos = $PersonsAllInfos->fetch_all(MYSQLI_ASSOC);
        return ["行数"=>$rows,"列数"=>count($fields),"数据"=>$PersonsAllInfos];
    }
}
