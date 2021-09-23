<?php
if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../ROOT_PATH.php';
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/Users/loginTypeFunctions.php";
// TODO:require log file

if (isset($_POST['requestFunction'])) { // 是否有要请求的类别
    if ($_POST['requestFunction'] === 'getCookie') { // 检查输入
        $returns = new UnionReturnInterface();
        try {
            $returns->setData(getCookie());
            echo $returns;
        } catch (JsonException $e) {
            $returns = new UnionReturnInterface('417','数据封装过程中出现错误');
            echo $returns;
        }
    } elseif ($_POST['requestFunction'] === 'login') {
        if (isset($_POST['StudentID'], $_POST['Password'], $_POST["RememberMe"])) { // 检查输入
            $inputArray = ['学号'=>$_POST['StudentID'], '密码'=>$_POST['Password'], '记住我'=>$_POST["RememberMe"]];
            try {
                $returns = new UnionReturnInterface();
                $returns->setData(login($inputArray));
                echo $returns;
            } catch (JsonException $e) {
                $returns = new UnionReturnInterface('417','数据封装过程中出现错误');
                echo $returns;
            } catch (STSAException $e) {
                $returns = new UnionReturnInterface();
                $returns->boundSTSAException($e);
                echo $returns;
            }
        } else {
            $returns = new UnionReturnInterface('400','提供信息不完整，请联系管理员');
            echo $returns;
        }
    } elseif ($_POST['requestFunction'] === 'resetPassword') {
        if (isset($_POST['StudentID'], $_POST['Name'], $_POST["School"], $_POST["Hometown"])) { // 检查输入
            $inputArray = ['学号'=>$_POST['StudentID'], '姓名'=>$_POST['Name'], '学院'=>$_POST["School"], '籍贯'=>$_POST["Hometown"]];
            try {
                $returns = new UnionReturnInterface();
                $returns->setData(resetPassword($inputArray));
                echo $returns;
            } catch (JsonException $e) {
                $returns = new UnionReturnInterface('417','数据封装过程中出现错误');
                echo $returns;
            } catch (STSAException $e) {
                $returns = new UnionReturnInterface();
                $returns->boundSTSAException($e);
                echo $returns;
            }
        } else {
            $returns = new UnionReturnInterface('400','提供信息不完整，请联系管理员');
            echo $returns;
        }
    } elseif ($_POST['requestFunction'] === 'directlyLogin') {
        if(isset($_SESSION['userID'],$_SESSION['userName'],$_SESSION['isLogin']) && $_SESSION['isLogin']===hash('sha256',session_id().$_SESSION['userID'].'true')) {
            echo 'true';
        }
    } else {
        $returns = new UnionReturnInterface('404', "功能不存在");
        echo $returns;
    }
}
else {
    $returns = new UnionReturnInterface('404', "没有选择需要的功能");
    echo $returns;
}
