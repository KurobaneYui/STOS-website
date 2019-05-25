<?php
session_start();
require("../frame/Person_Class_frame.php");
if ( isset( $_SESSION[ 'username' ] ) )
    $person = new person_all_info($_SESSION[ 'username' ]);
else
    $logouted = 1;
$_SESSION = array();
session_destroy();
header( 'refresh:3; url=/index.html' );
?>

<!doctype html>
<html lang="zh, en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="logout page">
    <meta name="author" content="Luo Yinsong">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <link rel="icon" href="../assets/images/users/STOS.png">

    <title>退出登录</title>

    <!-- 新 Bootstrap4 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/4.1.0/css/bootstrap.min.css">

    <!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
    <script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>

    <!-- popper.min.js 用于弹窗、提示、下拉菜单 -->
    <script src="https://cdn.staticfile.org/popper.js/1.12.5/umd/popper.min.js"></script>

    <!-- 最新的 Bootstrap4 核心 JavaScript 文件 -->
    <script src="../bootstrap-4.0.0/js/bootstrap.min.js"></script>

    <!-- Custom styles for this template -->
    <link href="../bootstrap-4.0.0/css/extra/floating-labels.css" rel="stylesheet">
</head>

<body>
    <form class="form-signin" action="login.php" method="post">
        <div class="text-center mb-4">
            <img class="mb-4" src="../assets/images/users/STOS.png" alt="" width="150" height="150">
            <h1 class="h3 mb-3 font-weight-normal">
                <?php if(!isset($logouted))echo "{$person->xuehao}，"; ?>已登出!</h1>
        </div>
        <div>
            <p class="mt-5 mb-3 text-muted text-center">您可以<a href='login.php'>重新登陆</a>
                <br/> 我们将在3秒后带您回到
                <a href='/index.html'>主页</a></a>
            </p>
        </div>
        <p class="mt-5 mb-3 text-muted text-center">2019 电子科技大学 学工部 学风督导队</p>
    </form>
</body>
</html>