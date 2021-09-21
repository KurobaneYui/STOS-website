<?php
session_start();
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/Tools/TranslateBetweenChineseAndEnglish.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";
require_once ROOT_PATH . "/Frame/php/Tools/DateTools.php";

if(!function_exists("transferMorningWork")) {
    /**
     * @param string $personID_ori
     * @param string $personID_new
     * @param string $startDate
     * @param string $endDate
     * @param int $groupCode
     * @return array
     * @throws STSAException
     */
    function transferMorningWork(string $personID_ori, string $personID_new, string $startDate, string $endDate, int $groupCode): array{
        // 准备参数环境
        $session = new DatabaseConnector();
        $logger = new STSA_log();
        // 检查输入参数合法性: 新老成员是否为同一组
        $sql = "SELECT 学号 FROM 工作信息 WHERE 学号='{$personID_new}' and 所属组号 IN (SELECT 所属组号 FROM 工作信息 WHERE 所属组号={$groupCode} and 学号='{$personID_ori}');";
        $isInSameGroupResult = $session->query($sql);
        if ($isInSameGroupResult===false) {
            $logger->add_log(__FILE__.":".__LINE__, "transferMorningWork, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误",417);
        }
        if($isInSameGroupResult->num_rows<1) {
            $logger->add_log(__FILE__.":".__LINE__, "transferMorningWork, 两成员（{$personID_ori}，{$personID_new}）不在同一组, 转移工作失败", "Log");
            throw new STSAException("两成员不在同一组",400);
        }
        $isInSameGroupResult->free();
        // 检查输入参数合法性: 起止时间相差是否不超过两周，起始时间与当前时间差值是否不超过两周
        $currentDatetime = DateTools::getCurrentDatetime();
        $startDatetime = new DateTools($startDate);
        $endDatetime = new DateTools($endDate);
        // 检查起止日期差
        $diffDatetime = date_diff($startDatetime->getBaseDatetime(),$endDatetime->getBaseDatetime());
        if (!is_int($diffDatetime->days) || $diffDatetime->invert!==0 || $diffDatetime->days>14) {
            $logger->add_log(__FILE__.":".__LINE__, "transferMorningWork, 起止日期相差超过两周或截止日期小于起始日期,from {$startDate} to {$endDate}, 转移工作失败", "Log");
            throw new STSAException("起止日期相差超过两周或截止日期小于起始日期",400);
        }
        // 检查当前日期与起始日期差
        $diffDatetime = date_diff($startDatetime->getBaseDatetime(),$currentDatetime);
        if (!is_int($diffDatetime->days) || $diffDatetime->days>14) {
            $logger->add_log(__FILE__.":".__LINE__, "transferMorningWork, 起始日期{$startDate}与当前日期相差超过两周, 转移工作失败", "Log");
            throw new STSAException("起始日期与当前日期相差超过两周",400);
        }
        // 检查当前日期与结束日期差
        $diffDatetime = date_diff($endDatetime->getBaseDatetime(),$currentDatetime);
        if (!is_int($diffDatetime->days) || $diffDatetime->days>14) {
            $logger->add_log(__FILE__.":".__LINE__, "transferMorningWork, 结束日期{$endDate}与当前日期相差超过两周, 转移工作失败", "Log");
            throw new STSAException("结束日期与当前日期相差超过两周",400);
        }
        // 检查权限，转移只能为组长允许，要求新老成员与组长同组
        if (!check_authorization(['team_leader' => false,'group_leader' => true,'member' => false, 'groupID' => $groupCode])) {
            $logger->add_log(__FILE__.":".__LINE__, "transferMorningWork, 交接两人工作失败, 权限错误", "Log");
            throw new STSAException("无权交接两人工作",401);
        }
        // 更新早自习排班操作
        $sql = "UPDATE 查早排班 SET 原定检查者学号='{$personID_new}',最终检查者学号='{$personID_new}'
                    WHERE 学院早自习安排编号 IN (
                        SELECT 学院早自习安排.学院早自习安排编号 as 学院早自习安排编号 FROM 学院早自习安排
                            LEFT JOIN 归档记录 ON 学院早自习安排.学院早自习安排编号=归档记录.学院早自习安排编号
                            WHERE 自习日期 BETWEEN '{$startDate}' and '{$endDate}' and 归档记录.学院早自习安排编号 is null
                    ) and 原定检查者学号='{$personID_ori}' and 原定检查者学号=最终检查者学号;
                UPDATE 查早排班 SET 原定检查者学号='{$personID_new}'
                    WHERE 学院早自习安排编号 IN (
                        SELECT 学院早自习安排.学院早自习安排编号 as 学院早自习安排编号 FROM 学院早自习安排
                            LEFT JOIN 归档记录 ON 学院早自习安排.学院早自习安排编号=归档记录.学院早自习安排编号
                            WHERE 自习日期 BETWEEN '{$startDate}' and '{$endDate}' and 归档记录.学院早自习安排编号 is null
                    ) and 原定检查者学号='{$personID_ori}' and 原定检查者学号<>最终检查者学号;";
        $updateMorningResult = $session->query($sql);
        if ($updateMorningResult===false) {
            $logger->add_log(__FILE__.":".__LINE__, "transferMorningWork, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误",417);
        }
        $session->commit();
        $logger->add_log(__FILE__.":".__LINE__, "transferMorningWork, 转移工作from {$personID_ori} to {$personID_new} 选定日期from {$startDate} to {$endDate}, 完成", "Log");
        return [true];
    }
}

if(!function_exists("transferCoursesWork")) {
    /**
     * @param string $personID_ori
     * @param string $personID_new
     * @param string $startDate
     * @param string $endDate
     * @param int $groupCode
     * @return array
     * @throws STSAException
     */
    function transferCoursesWork(string $personID_ori, string $personID_new, string $startDate, string $endDate, int $groupCode): array{
        // 准备参数环境
        $session = new DatabaseConnector();
        $logger = new STSA_log();
        // 检查输入参数合法性: 新老成员是否为同一组
        $sql = "SELECT 学号 FROM 工作信息 WHERE 学号='{$personID_new}' and 所属组号 IN (SELECT 所属组号 FROM 工作信息 WHERE 所属组号={$groupCode} and 学号='{$personID_ori}');";
        $isInSameGroupResult = $session->query($sql);
        if ($isInSameGroupResult===false) {
            $logger->add_log(__FILE__.":".__LINE__, "transferCoursesWork, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误",417);
        }
        if($isInSameGroupResult->num_rows<1) {
            $logger->add_log(__FILE__.":".__LINE__, "transferCoursesWork, 两成员（{$personID_ori}，{$personID_new}）不在同一组, 转移工作失败", "Log");
            throw new STSAException("两成员不在同一组",400);
        }
        $isInSameGroupResult->free();
        // 检查输入参数合法性: 起止时间相差是否不超过两周，起始时间与当前时间差值是否不超过两周
        $currentDatetime = DateTools::getCurrentDatetime();
        $startDatetime = new DateTools($startDate);
        $endDatetime = new DateTools($endDate);
        // 检查起止日期差
        $diffDatetime = date_diff($startDatetime->getBaseDatetime(),$endDatetime->getBaseDatetime());
        if (!is_int($diffDatetime->days) || $diffDatetime->invert!==0 || $diffDatetime->days>14) {
            $logger->add_log(__FILE__.":".__LINE__, "transferCoursesWork, 起止日期相差超过两周或截止日期小于起始日期,from {$startDate} to {$endDate}, 转移工作失败", "Log");
            throw new STSAException("起止日期相差超过两周或截止日期小于起始日期",400);
        }
        // 检查当前日期与起始日期差
        $diffDatetime = date_diff($startDatetime->getBaseDatetime(),$currentDatetime);
        if (!is_int($diffDatetime->days) || $diffDatetime->days>14) {
            $logger->add_log(__FILE__.":".__LINE__, "transferCoursesWork, 起始日期{$startDate}与当前日期相差超过两周, 转移工作失败", "Log");
            throw new STSAException("起始日期与当前日期相差超过两周",400);
        }
        // 检查当前日期与结束日期差
        $diffDatetime = date_diff($endDatetime->getBaseDatetime(),$currentDatetime);
        if (!is_int($diffDatetime->days) || $diffDatetime->days>14) {
            $logger->add_log(__FILE__.":".__LINE__, "transferCoursesWork, 结束日期{$endDate}与当前日期相差超过两周, 转移工作失败", "Log");
            throw new STSAException("结束日期与当前日期相差超过两周",400);
        }
        // 检查权限，转移只能为组长允许，要求新老成员与组长同组
        if (!check_authorization(['team_leader' => false,'group_leader' => true,'member' => false, 'groupID' => $groupCode])) {
            $logger->add_log(__FILE__.":".__LINE__, "transferCoursesWork, 交接两人工作失败, 权限错误", "Log");
            throw new STSAException("无权交接两人工作",401);
        }
        // 更新查课排班操作
        $sql = "UPDATE 查课排班 SET 原定检查者学号='{$personID_new}',最终检查者学号='{$personID_new}'
                    WHERE 课程编号 IN (
                        SELECT 全校查课信息.课程编号 as 课程编号 FROM 全校查课信息
                            LEFT JOIN 归档记录 ON 全校查课信息.课程编号=归档记录.课程编号
                            WHERE 日期 BETWEEN '{$startDate}' and '{$endDate}' and 归档记录.课程编号 is null
                    ) and 原定检查者学号='{$personID_ori}' and 原定检查者学号=最终检查者学号;
                UPDATE 查课排班 SET 原定检查者学号='{$personID_new}'
                    WHERE 课程编号 IN (
                        SELECT 全校查课信息.课程编号 as 课程编号 FROM 全校查课信息
                            LEFT JOIN 归档记录 ON 全校查课信息.课程编号=归档记录.课程编号
                            WHERE 日期 BETWEEN '{$startDate}' and '{$endDate}' and 归档记录.课程编号 is null
                    ) and 原定检查者学号='{$personID_ori}' and 原定检查者学号<>最终检查者学号;";
        $updateCoursesResult = $session->query($sql);
        if ($updateCoursesResult===false) {
            $logger->add_log(__FILE__.":".__LINE__, "transferCoursesWork, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误",417);
        }
        $session->commit();
        $logger->add_log(__FILE__.":".__LINE__, "transferCoursesWork, 转移工作from {$personID_ori} to {$personID_new} 选定日期from {$startDate} to {$endDate}, 完成", "Log");
        return [true];
    }
}