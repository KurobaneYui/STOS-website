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
    if ($_POST['requestFunction']==='getContact') {
        require_once ROOT_PATH . '/Frame/php/Users/getContactInfo.php';
        try {
            $returns = new UnionReturnInterface();
            $returns->setData(getContactInfo());
            $logger->add_log(__FILE__.":".__LINE__, "Ajax-contact, function execute successfully", "Log");
            echo $returns;
        } catch (JsonException $e) {
            $logger->add_log(__FILE__.":".__LINE__, "Ajax-contact, json相关错误, 错误信息:\n{$e}", "Error");
            $returns = new UnionReturnInterface('417','数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
            $logger->add_log(__FILE__.":".__LINE__, "Ajax-contact, meet customException, 错误信息:\n{$returns}", "Warning");
            echo $returns;
        }
    }
    else {
        $logger->add_log(__FILE__.":".__LINE__, "Ajax-contact, 功能不存在", "Log");
        $returns = new UnionReturnInterface('404', "功能不存在");
        echo $returns;
    }
}
else {
    $logger->add_log(__FILE__.":".__LINE__, "Ajax-contact, 没有选择需要的功能", "Log");
    $returns = new UnionReturnInterface('404', "没有选择需要的功能");
    echo $returns;
}