<?php
session_start();
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
// TODO:require log file

if (!function_exists("getOnePersonBasicInfo")) {
    function getOnePersonBasicInfo(string $searchID=""): mysqli_result { // 用于返回某个人的非保密信息
        if ($searchID=="") { $searchID=$_SESSION["userID"]; } // 如不指定ID则默认使用登录者本人ID
        if ($searchID===$_SESSION["userID"]) {
            // TODO:check authorization 属于个人级别的本人非保密信息
            if(!check_authorization("")) {
                throw new STSAException("无权限查看本人信息",401);
            }
        }
        else {
            // TODO:check authorization 属于个人级别的他人非保密信息
            if(!check_authorization("")) {
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
        $PersonBasicInfos = $PersonBasicInfos->fetch_all(MYSQLI_ASSOC);
        return $PersonBasicInfos;
    }
}

if (!function_exists("getOnePersonAllInfo")) {
    function getOnePersonAllInfo(string $searchID=""): mysqli_result { // 用于返回某个人的全部保密信息
        if ($searchID=="") { $searchID=$_SESSION["userID"]; } // 如不指定ID则默认使用登录者本人ID
        if ($searchID===$_SESSION["userID"]) {
            // TODO:check authorization 属于个人级别的本人保密信息
            if(!check_authorization("")) {
                throw new STSAException("无权限查看个人信息",401);
            }
        }
        else {
            // TODO:check authorization 属于个人级别的他人保密信息
            if(!check_authorization("")) {
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
        $PersonAllInfos = $PersonAllInfos->fetch_all(MYSQLI_ASSOC);
        return $PersonAllInfos;
    }
}

if (!function_exists("getAllPersonBasicInfo")) {
    function getGroupPersonBasicInfo(string $GroupID): array { // 用于返回某组成员的非保密信息
        // TODO:check authorization 属于组级别的的他人非保密信息
        if(!check_authorization("")) {
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

if (!function_exists("getAllPersonAllInfo")) {
    function getGroupPersonAllInfo(string $GroupID): array { // 用于返回某组成员的全部保密信息
        // TODO:check authorization 属于组级别的他人保密信息
        if(!check_authorization("")) {
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

if (!function_exists("getContactInfo")) {
    function getContactInfo(): array{ // 用于返回全队联系方式信息
        // TODO:check authorization 属于队伍级别的联系方式权限
        if(!check_authorization("")) {
            throw new STSAException("无权限查看他人联系方式信息",401);
        }

        $session = new DatabaseConnector();
        $sql =
            "SELECT (@i:=@i+1)'序号',`姓名`, `性别`, `QQ`, `电话`, `部门名称` as '所属组', `岗位` FROM 成员非保密信息,(SELECT @i:=0)b;";
        $ContactInfos = $session->query($sql);
        if ($ContactInfos===false) {
            throw new STSAException("数据库查询错误",417);
        }

        $rows = $ContactInfos->num_rows;
        $fields = array_column($ContactInfos->fetch_fields(),'name');
        $ContactInfos = $ContactInfos->fetch_all(MYSQLI_ASSOC);
        return ["行数"=>$rows,"列数"=>count($fields),'表头'=>$fields,"数据"=>$ContactInfos];
    }
}
