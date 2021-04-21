<?php
session_start();
require_once __DIR__. '/../../ROOT_PATH.php';
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
// TODO:require log file

function login(string $StudentID, string $Password, bool $RememberMe): bool {
    $session = new DatabaseConnector();
    $sql = "SELECT 学号 FROM 账户密码 WHERE `密码`=AES_ENCRYPT('{$Password}','{$Password}') and `学号`='{$StudentID}';";
    $log_check = $session->query($sql);
    if ($log_check!==false and $log_check->num_rows!==0) {
        $_SESSION['userID'] = $StudentID;
        $_SESSION['isLogin'] = hash('sha256',session_id().$StudentID.'true');
        if ($RememberMe) { // 是否记住用户名和密码
            setcookie('STSA_un', $StudentID, time() + 1209600);
            setcookie('STSA_pd', $Password, time() + 1209600);
        } // 设定14天的cookie
        else { // 如果没有设置记住用户名和密码
            setcookie('STSA_un', '', time() - 604800);
            setcookie('STSA_pd', '', time() - 604800);
        } // 删除cookie
//        (new DeviceAndIPDetector())->uploadDeviceInfo($StudentID);
        return true;
    }

    return false;
}

function resetPassword(string $StudentID, string $Name, string $School, string $Hometown): bool {
    $person = new Person($StudentID);
    if (!$person->exist()) {
        return false;
    }
    $info = $person->basic_info();
    if ($info['姓名']===$Name && $info['学院']===$School && $info['籍贯']===$Hometown) {
        $person->change_info(array('密码'=>$StudentID));
        return $person->commit_password_information();
    }

    return false;
}

function getCookie(): string {
    if (isset($_COOKIE['STSA_un'], $_COOKIE['STSA_pd'])) {
        return json_encode(array('STSA_un'=>$_COOKIE['STSA_un'],'STSA_pd'=>$_COOKIE['STSA_pd'],'STSA_rm'=>true),JSON_UNESCAPED_UNICODE);
    }
    return json_encode(array('STSA_un'=>'','STSA_pd'=>'','STSA_rm'=>false),JSON_UNESCAPED_UNICODE);
}

if (isset($_POST['requestFunction'])) { // 是否有要请求的类别
    if($_POST['requestFunction']==='login') { // 如果请求登录
        if (isset($_POST['StudentID'], $_POST['Password'], $_POST['RememberMe'])) { // 判断是否符合需要的参数
            if (login($_POST['StudentID'], $_POST['Password'], $_POST['RememberMe']==='true')) {
                echo (new TransJson(true,'','',''))->encode2json();
            }
            else {
                echo (new TransJson(false,'00','用户名或密码不正确，请检查',''))->encode2json();
            }
        }
        else {
            $returns = new TransJson(false,'12','请确认提供了姓名、学号以及是否需要记住账户');
            echo $returns->encode2json();
        }
    }
    elseif ($_POST['requestFunction']==='resetPassword') { // 如果请求重置密码
        if (isset($_POST['StudentID'], $_POST['Name'], $_POST['School'], $_POST['Hometown'])) { // 判断是否符合需要的参数
            if (resetPassword($_POST['StudentID'], $_POST['Name'], $_POST['School'], $_POST['Hometown'])) {
                echo (new TransJson(true,'','',''))->encode2json();
            } else {
                echo (new TransJson(false,'00','核验失败，请确保和网站中提交的个人信息保持一致',''))->encode2json();
            }
        }
        else {
            $returns = new TransJson(false,'12','请确认完整提供了姓名、学号、学院、籍贯、银行卡号信息');
            echo $returns->encode2json();
        }
    }
    elseif ($_POST['requestFunction']==='getCookie') {
        $returns = new TransJson(true,'','',getCookie());
        echo $returns->encode2json();
    }
    else {
        $returns = new TransJson(false,'13',"你试图使用 {$_POST['requestFunction']} 功能，但是此功能并不存在");
        echo $returns->encode2json();
    }
}

//$returns = new TransJson(false,'30',"你试图使用 {$_POST['requestFunction']} 功能，但是此功能正在开发维护，暂不提供使用");
