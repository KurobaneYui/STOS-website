<?php
// 获取权限，返回json表示的数字数组，数组每个元素均为数组，侧边栏js读取数字并保留数字对应的侧栏项目
// 0保留成员功能，1保留现场组组员功能，2保留组长功能，3保留队长功能，4保留数据组附加功能
// php返回的数字列表应包含希望显示的功能类目的所有编号，比如队长应返回[0=>true, 1=>false, 2=>false, 3=>true]
// html页面功能编号中，只要有任何一个编号对应到了提供的编号，此功能页面便展示，如果没有功能编号则表示一定展示
// ！！！！！！！！！注意 现场组功能的检查，硬编码于本代码，如果有变动，请修改本代码 !!!!!!!!!!
if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../ROOT_PATH.php';
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";

try {
    $session = new DatabaseConnector();
    $authCode = [0=>false, 1=>false, 2=>false, 3=>false, 4=>false];
    if (!check_authorization()) {
        exit;
    }
    $sql = "SELECT 权限 FROM 权限信息 WHERE 学号='{$_SESSION["userID"]}';";
    $changeFunctionShownResult = $session->query($sql);
    if ($changeFunctionShownResult===false) {
        exit;
    }
    $changeFunctionShownResult = $changeFunctionShownResult->fetch_all(MYSQLI_ASSOC);
    $changeFunctionShownResult = json_decode($changeFunctionShownResult, true, 512, JSON_THROW_ON_ERROR);
    if ($changeFunctionShownResult[0]["super"]===true) {
        echo json_encode([0=>true, 1=>true, 2=>true, 3=>true, 4=>true], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($changeFunctionShownResult[0]["team_leader"]===true) {
        $authCode[3] = true;
    }
    if ($changeFunctionShownResult[0]["data"]["check"]===true || $changeFunctionShownResult[0]["data"]["change"]===true) {
        $authCode[4] = true;
    }
    if (!empty($changeFunctionShownResult[0]["groups"])) {
        $authCode[0] = true;
    }
    foreach ($changeFunctionShownResult[0]["groups"] as $key=>$value) {
        if ( in_array($key, [2,3,4,5,6,7,8], false) ) {
            $authCode[1] = true;
        }
        if ($value["group_leader"]===true) {
            $authCode[2] = true;
        }
    }
    echo json_encode($authCode, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (JsonException | STSAException $e) {
    exit;
}