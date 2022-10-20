<?php
session_start();
ini_set("display_errors","On");
error_reporting(E_ALL);
require("../../frame/Person_Class_frame.php");

if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
    $connection = new STOS_MySQL(); // 建立数据库连接
    $person = new person_all_info( $_SESSION[ "username" ] ); // 获取个人信息
    if($person->work_info()["权限"]!=3 && $person->xuehao!='2016020903001') header( 'refresh:0; url=../../log/logout.php' ); // 如果不是队长，强制登出
}
else { // 没有登陆
    header( 'refresh:0; url=../../log/login.php' ); // 返回登陆页面
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>无标题文档</title>
    <script src="https://cdn.bootcdn.net/ajax/libs/showdown/1.9.1/showdown.js"></script>
</head>
<style>
    blockquote {
        border-left: #eee solid 5px;
        padding-left: 20px;
    }
</style>
<body>
<?php
require(__DIR__.'/../../ROOT_PATH.php');
require(ROOT_PATH . '/frame/php/Database_connector.php');
require(ROOT_PATH . '/frame/php/DateTools.php');
$conn = new Database_connector(ROOT_PATH.'/config/DataBase_Information.conf');
// $t = $conn->query("use STOS_info;SELECT * FROM 成员信息 join 成员岗位 on 成员信息.学号 = 成员岗位.学号 join 登陆信息 on 成员信息.学号 = 登陆信息.学号 WHERE 所属组 IS NOT NULL;");
$t = $conn->query("SELECT * FROM 成员信息 join 成员岗位 on 成员信息.学号 = 成员岗位.学号 WHERE 所属组 IS NOT NULL ORDER BY `岗位` DESC, `所属组` DESC;");
echo "<table>";
$r=$t->fetch_assoc();
echo "<tr>";
foreach($r as $key =>$value)
{
    echo "<th>{$key}</th>";
}
echo "</tr>";
echo "<tr>";
foreach($r as $key=>$value)
{
    echo "<td>{$value}</td>";
}
echo "</tr>";
while($r=$t->fetch_assoc())
{
    echo "<tr>";
    foreach($r as $key=>$value)
    {
        echo "<td>{$value}</td>";
    }
    echo "</tr>";
}
echo "</table>";
?>
