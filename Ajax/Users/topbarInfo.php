<?php
session_start();
require_once __DIR__ . '/../../ROOT_PATH.php';
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
// TODO:require log file

if (isset($_POST['requestFunction'])) { // 是否有要请求的类别
    if ($_POST['requestFunction']==='getTopbarInfo') {
        if(isset($_SESSION['userID'],$_SESSION['userName'])) {
            require_once ROOT_PATH . '/Frame/php/Users/getNameWorkforPage.php';
            try {
                $returns = new UnionReturnInterface();
                $returns->setData(getNameWorkforPage());
                echo $returns;
            } catch (JsonException $e) {
                $returns = new UnionReturnInterface('417','数据封装过程中出现错误');
                echo $returns;
            } catch (STSAException $e) {
                $returns = new UnionReturnInterface();
                $returns->boundSTSAException($e);
                echo $returns;
            }
        }
        else {
            $returns = new UnionReturnInterface('400','会话信息不正确，请尝试重新登陆');
            echo $returns;
        }
    }
    else {
        $returns = new UnionReturnInterface('404', "功能不存在");
        echo $returns;
    }
}
else {
    $returns = new UnionReturnInterface('404', "没有选择需要的功能");
    echo $returns;
}