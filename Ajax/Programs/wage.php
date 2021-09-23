<?php
if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . "/../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";
require_once ROOT_PATH . "/Frame/php/ProgramHandle/ProgramHandleInterface.php";
require_once ROOT_PATH . "/Frame/php/Users/wage.php";

$logger = new STSA_log();

if (isset($_POST['requestFunction'])) { // 是否有要请求的类别
    if ($_POST['requestFunction']==='wageFile') {
        try {
            if (!isset($_POST["date"],$_POST["teacherName"],$_POST["teacherPhone"],$_POST["teacherEmail"],
                $_POST["teamLeaderName"],$_POST["teamLeaderPhone"],$_POST["teamLeaderEmail"],$_POST["workPlace"],
                $_POST["firstMoney"],$_POST["secondMoney"],$_POST["thirdMoney"],$_POST["subsidyDossierNum"]))
            {
                $logger->add_log(__FILE__ . ":" . __LINE__, "Ajax-wageFile, 没有提供需要的数据", "Log");
                $returns = new UnionReturnInterface('400', "没有提供需要的数据");
                echo $returns;
                exit;
            }
            $programArgv = ["/Program/python/FinanceProcess.py",ROOT_PATH."/config/DataBase_STSA.conf",ROOT_PATH."/tmpFiles/wage",$_POST["date"],$_POST["teacherName"],$_POST["teacherPhone"],$_POST["teacherEmail"],
                $_POST["teamLeaderName"],$_POST["teamLeaderPhone"],$_POST["teamLeaderEmail"],$_POST["workPlace"],
                $_POST["firstMoney"],$_POST["secondMoney"],$_POST["thirdMoney"],$_POST["subsidyDossierNum"]];
            $programHandle = new ProgramHandleInterface("python",$programArgv, relative2webroot: true);
            $returns = ($programHandle->runCode())[0];
            $logger->add_log(__FILE__.":".__LINE__, "Ajax-wageFile, function execute successfully", "Log");
            require_once ROOT_PATH . "/Frame/php/Users/wage.php";
            if (uploadWageInfo($_POST)[0]) {
                $logger->add_log(__FILE__.":".__LINE__,"Ajax-wageFile, upload info success","Log");
            } else {
                $logger->add_log(__FILE__.":".__LINE__,"Ajax-wageFile, upload info error","Warning");
            }
            echo $returns;
        } catch (JsonException $e) {
            $logger->add_log(__FILE__.":".__LINE__, "Ajax-wageFile, json相关错误, 错误信息:\n{$e}", "Error");
            $returns = new UnionReturnInterface('417','数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
            $logger->add_log(__FILE__.":".__LINE__, "Ajax-wageFile, meet customException, 错误信息:\n{$returns}", "Warning");
            echo $returns;
        }
    }
    elseif ($_POST['requestFunction']==='wageInfo') {
        try {
            $returns = new UnionReturnInterface();
            $returns->setData(downloadWageInfo());
            $logger->add_log(__FILE__.":".__LINE__,"Ajax-wageInfo, 获取工资表表头成功","Log");
            echo $returns;
        } catch (JsonException $e) {
            $logger->add_log(__FILE__.":".__LINE__, "Ajax-wageInfo, json相关错误, 错误信息:\n{$e}", "Error");
            $returns = new UnionReturnInterface('417','数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
            $logger->add_log(__FILE__.":".__LINE__, "Ajax-wageInfo, meet customException, 错误信息:\n{$returns}", "Warning");
            echo $returns;
        }
    }
    else {
        $logger->add_log(__FILE__.":".__LINE__, "Ajax-wage, 功能不存在", "Log");
        $returns = new UnionReturnInterface('404', "功能不存在");
        echo $returns;
    }
}
else {
    $logger->add_log(__FILE__.":".__LINE__, "Ajax-wage, 没有选择需要的功能", "Log");
    $returns = new UnionReturnInterface('404', "没有选择需要的功能");
    echo $returns;
}