<?php
session_start();
require_once __DIR__ . '/../../ROOT_PATH.php';
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/Tools/TranslateBetweenChineseAndEnglish.php";
<<<<<<< HEAD
// TODO:require log file
=======
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

$logger = new STSA_log();
>>>>>>> website-v2

if (isset($_POST['requestFunction'])) { // 是否有要请求的类别
    if ($_POST['requestFunction']==='getPersonalWorks') {
        require_once ROOT_PATH . '/Frame/php/Users/getPersonWorks.php';
        try {
            $returns = new UnionReturnInterface();
            // 处理信息并打包数据
            $returns->setData(getOnePersonWorks());
<<<<<<< HEAD
            echo $returns;
        } catch (JsonException $e) {
=======
            $logger->add_log(__FILE__.":".__LINE__, "Ajax-personalWorks, function execute successfully", "Log");
            echo $returns;
        } catch (JsonException $e) {
            $logger->add_log(__FILE__.":".__LINE__, "Ajax-personalWorks, json相关错误, 错误信息:\n{$e}", "Error");
>>>>>>> website-v2
            $returns = new UnionReturnInterface('417','数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
<<<<<<< HEAD
            echo $returns;
        }
    } else {
=======
            $logger->add_log(__FILE__.":".__LINE__, "Ajax-personalWorks, meet customException, 错误信息:\n{$returns}", "Warning");
            echo $returns;
        }
    } else {
        $logger->add_log(__FILE__.":".__LINE__, "Ajax-personalWorks, 功能不存在", "Log");
>>>>>>> website-v2
        $returns = new UnionReturnInterface('404', "功能不存在");
        echo $returns;
    }
}
else {
<<<<<<< HEAD
=======
    $logger->add_log(__FILE__.":".__LINE__, "Ajax-personalWorks, 没有选择需要的功能", "Log");
>>>>>>> website-v2
    $returns = new UnionReturnInterface('404', "没有选择需要的功能");
    echo $returns;
}