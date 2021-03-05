<?php
session_start();
require_once __DIR__.'/../../ROOT_PATH.php';
require_once ROOT_PATH.'/frame/php/Person.php';
require_once ROOT_PATH.'/frame/php/TransJson.php';

// TODO
//    []: 临时个人信息请求，需要替换
if (isset($_SESSION['isLogin']) && $_SESSION['isLogin'] === hash('sha256', session_id() . $_SESSION['username'] . 'true')) {
    $login = true;
} else {
    echo (new TransJson(false,'14','请登录',''))->encode2json();
    exit;
}

if (isset($_POST['requestFunction'])) { // 是否有要请求的类别
    if ($_POST['requestFunction']==='person_info') { // 如果请求注册
        if (true) { // 判断参数
            $person = new Person($_SESSION['username']);
            $returns = array_merge($person->basic_info(),$person->work_info(),$person->authorization_info());
            $returns = json_encode($returns,JSON_UNESCAPED_UNICODE);
            echo (new TransJson(true,'','',$returns))->encode2json();
        }
        else {
            $returns = new TransJson(false,'12','请确认完整提供了注册所需的所有信息');
            echo $returns->encode2json();
        }
    }
    else {
        $returns = new TransJson(false,'30',"你试图使用 {$_POST['requestFunction']} 功能，但是此功能并不存在");
        echo $returns->encode2json();
    }
}