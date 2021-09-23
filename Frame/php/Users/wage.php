<?php
if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/ProgramHandle/ProgramHandleInterface.php";

if (!function_exists("uploadWageInfo")) {
    /**
     * @param array $info
     * @return array
     * @throws STSAException
     */
    function uploadWageInfo(array $info): array{
        // info check
        $logger = new STSA_log();
        $session = new DatabaseConnector();
        if (!isset($info["date"],$info["teacherName"],$info["teacherPhone"],$info["teacherEmail"],
            $info["teamLeaderName"],$info["teamLeaderPhone"],$info["teamLeaderEmail"],$info["workPlace"],
            $info["firstMoney"],$info["secondMoney"],$info["thirdMoney"],$info["subsidyDossierNum"]))
        {
            $logger->add_log(__FILE__.":".__LINE__, "uploadWageInfo, 未提供必要数据", "Log");
            throw new STSAException("未提供必要数据", 400);
        }
        //authority check
        if (!check_authorization(["team_leader" => true, "group_leader" => false, "member" => false])) {
            $logger->add_log(__FILE__.":".__LINE__, "uploadWageInfo, 无权上传财务报账表信息, 权限错误", "Log");
            throw new STSAException("无权上传财务报账表信息", 401);
        }
        //operate
        $sql = "INSERT INTO 财务报账表头 (日期,指导老师姓名,指导老师电话,指导老师邮箱,骨干姓名,骨干电话,骨干邮箱,办公地点,
                    一档金额,二档金额,三档金额,建档立卡专设岗位)
                    VALUES ('{$info['date']}','{$info['teacherName']}','{$info['teacherPhone']}','{$info['teacherEmail']}',
                            '{$info['teamLeaderName']}','{$info['teamLeaderPhone']}','{$info['teamLeaderEmail']}','{$info['workPlace']}',
                            '{$info['firstMoney']}','{$info['secondMoney']}','{$info['thirdMoney']}','{$info['subsidyDossierNum']}')
                    ON DUPLICATE KEY UPDATE
                        指导老师姓名='{$info['teacherName']}',指导老师电话='{$info['teacherPhone']}',指导老师邮箱='{$info['teacherEmail']}',
                        骨干姓名='{$info['teamLeaderName']}',骨干电话='{$info['teamLeaderPhone']}',骨干邮箱='{$info['teamLeaderEmail']}',
                        办公地点='{$info['workPlace']}',
                        一档金额='{$info['firstMoney']}',二档金额='{$info['secondMoney']}',三档金额='{$info['thirdMoney']}',建档立卡专设岗位='{$info['subsidyDossierNum']}';";
        $wageInfoResult = $session->query($sql);
        if ($wageInfoResult===false) {
            $logger->add_log(__FILE__.":".__LINE__,"uploadWageInfo, 数据库查询错误","Error");
            throw new STSAException("数据库查询错误",417);
        }
        $session->commit();
        $logger->add_log(__FILE__.":".__LINE__,"uploadWageInfo, 成功上传工资表表头信息, success","Log");
        return [true];
    }
}

if (!function_exists("downloadWageInfo")) {
    /**
     * @return array
     * @throws STSAException
     */
    function downloadWageInfo(): array{
        $logger = new STSA_log();
        $session = new DatabaseConnector();
        //authority check
        if (!check_authorization(["team_leader" => true, "group_leader" => false, "member" => false])) {
            $logger->add_log(__FILE__.":".__LINE__, "downloadWageInfo, 无权下载财务报账表信息, 权限错误", "Log");
            throw new STSAException("无权下载财务报账表信息", 401);
        }
        //operate
        $sql = "SELECT 日期,指导老师姓名,指导老师电话,指导老师邮箱,骨干姓名,骨干电话,骨干邮箱,办公地点,
                    一档金额,二档金额,三档金额,建档立卡专设岗位 FROM 财务报账表头 ORDER BY 日期 DESC LIMIT 1;";
        $wageInfoResult = $session->query($sql);
        if ($wageInfoResult===false) {
            $logger->add_log(__FILE__ . ":" . __LINE__, "uploadWageInfo, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误", 417);
        }
        return $wageInfoResult->fetch_all(MYSQLI_ASSOC)[0];
    }
}