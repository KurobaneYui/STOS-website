<?php
session_start();
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
// TODO:require log file

if (!function_exists("getOnePersonWorks")) {
    function getOnePersonWorks(string $searchID=""): array { // 用于返回某个人的基本岗位信息
        if ($searchID=="") { $searchID=$_SESSION["userID"]; } // 如不指定ID则默认使用登录者本人ID
        if ($searchID===$_SESSION["userID"]) {
            // check authorization 属于个人级别的本人保密信息
            if (!check_authorization()) {
                throw new STSAException("无权查看本人基本岗位信息，可能由于未登录或登录信息已过期", 401);
            }
        }
        else {
            // TODO:check authorization 属于个人级别的他人保密信息
            if(!check_authorization(['team_leader'=>true,'group_leader'=>true,'member'=>false,'groupID'=>000])) {
                throw new STSAException("无权限查看他人基本岗位信息",401);
            }
        }

        $session = new DatabaseConnector();
        $sql = "select 学号,周一空课,周二空课,周三空课,周四空课,周五空课,周六空课,周日空课,备注 from 空课信息 where 学号='{$searchID}';";
        $PersonWorkClassInfo = $session->query($sql);
        if ($PersonWorkClassInfo===false) {
            throw new STSAException("数据库查询错误",417);
        }
        $PersonWorkClassInfo = $PersonWorkClassInfo->fetch_assoc();

        $sql = "SELECT 学号,部门名称,岗位,基本工资,备注 FROM 工作信息,部门信息 WHERE `学号`='{$searchID}' and 部门编号=所属组号;";
        $PersonWorkInfo = $session->query($sql);
        if ($PersonWorkInfo===false) {
            throw new STSAException("数据库查询错误",417);
        }
        $PersonWorkInfo = $PersonWorkInfo->fetch_all(MYSQLI_ASSOC);
        return ["学号"=>$searchID,"空课表"=>$PersonWorkClassInfo,"基本岗位信息"=>$PersonWorkInfo];
    }
}

if (!function_exists("getGroupPersonWorks")) {
    function getGroupPersonWorks(string $GroupID): array { // 用于返回某组成员的基本岗位信息
        // check authorization 属于组级别的的他人保密信息
        if(!check_authorization(['team_leader'=>true,'group_leader'=>true,'member'=>false,'groupID'=>$GroupID])) {
            throw new STSAException("无权限查看组内他人基本岗位信息",401);
        }

        $session = new DatabaseConnector();
        $sql = "select 学号,周一空课,周二空课,周三空课,周四空课,周五空课,周六空课,周日空课,备注 from 空课信息 where 学号 in (select distinct 学号 from 工作信息 where 所属组号=$GroupID);";
        $PersonsWorkClassInfo = $session->query($sql);
        if ($PersonsWorkClassInfo===false) {
            throw new STSAException("数据库查询错误",417);
        }
        $rows = $PersonsWorkClassInfo->num_rows;
        $PersonsWorkClassInfo = $PersonsWorkClassInfo->fetch_all(MYSQLI_ASSOC);

        $sql = "SELECT 学号,部门名称,岗位,基本工资,备注 FROM 工作信息,部门信息 WHERE `所属组号`=$GroupID and 部门编号=所属组号;";
        $PersonsWorkInfo = $session->query($sql);
        if ($PersonsWorkInfo===false) {
            throw new STSAException("数据库查询错误",417);
        }
        $PersonsWorkInfo = $PersonsWorkInfo->fetch_all(MYSQLI_ASSOC);

        $returns = ['人数'=>$rows,'数据'=>array()];
        foreach ($PersonsWorkClassInfo as $value) {
            $returns['数据'][$value['学号']] = ['空课表'=>$value,'基本岗位信息'=>array()];
        }
        foreach ($PersonsWorkInfo as $value) {
            $returns['数据'][$value['学号']]['基本岗位信息'][] = $value;
        }
        return $returns;
//        $returns = [
//            '人数'=>44,
//            '数据'=>[
//                $studentID=>[
//                    "空课表"=>$PersonWorkClassInfo,
//                    "基本岗位信息"=>[
//                        [],
//                        []
//                    ]
//                ]
//            ]
//        ];
    }
}