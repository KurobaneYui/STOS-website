<?php
session_start();
require( "../frame/Person_Class_frame.php" );

if ( isset( $_SESSION[ 'username' ] )and isset( $_SESSION[ 'islogin' ] ) ) // 判断是否已经登陆
    if ( $_SESSION[ 'islogin' ] == 1 ) { // 已登录状态量为真
        header( 'refresh:0; url=../personal/index.php' ); // 跳转至个人主页
        exit; // 退出php
    }
if ( isset( $_POST[ 'login' ] ) ) { // 如果有提交的登陆数据
    $wrong = 'yes'; // 默认登陆信息输入错误
    $username = trim( $_POST[ "U_N" ] ); // 获取输入用户名
    $password = trim( $_POST[ "P_W" ] ); // 获取输入密码
    $person = new person_all_info($username); // 建立输入用户的个人信息实例
    if ( $person->xinming!="" and $password != "" and $password == $person->mima ) { //如果用户名和对应密码相同 且 用户存在 且密码不为空
        $_SESSION[ 'username' ] = $username; // 建立SESSION传递用户学号
        $_SESSION[ 'islogin' ] = 1; // 确定登陆信息为已登录
        if ( $_POST[ "R_C" ] == "yes" ) // 是否记住用户名和密码
            setcookie("R_C", $username." ".$password, time()+1209600); // 设定14天的cookie
        elseif( $_POST["R_C"] != "yes") // 如果没有设置记住用户名和密码
            setcookie("R_C", '', time()-604800); // 删除cookie
        $wrong = 'no'; // 输入账户正确
        header( 'refresh:0; url=../personal/index.php' ); // 跳转至个人主页
        exit; // 退出php
    }
}
?>

<!doctype html>
<html lang="zh, en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="login page">
    <meta name="author" content="Luo Yinsong">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <link rel="icon" href="../assets/images/users/STOS.png">

    <title>队员登陆</title>

    <!-- 新 Bootstrap4 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/4.1.0/css/bootstrap.min.css">

    <!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
    <script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>

    <!-- popper.min.js 用于弹窗、提示、下拉菜单 -->
    <script src="https://cdn.staticfile.org/popper.js/1.12.5/umd/popper.min.js"></script>

    <!-- 最新的 Bootstrap4 核心 JavaScript 文件 -->
    <script src="../bootstrap-4.0.0/js/bootstrap.min.js"></script>

    <!-- Custom styles for this template -->
    <link id="logincss" href="../bootstrap-4.0.0/css/extra/floating-labels.css" rel="stylesheet">
    <link href="../bootstrap-4.0.0/css/extra/jumbotron.css" rel="stylesheet">
</head>

<body>
	<nav id="navbar" class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
		<!--ajax替换文本-->
	</nav>
	<main role="main">
		<br/>
        <form class="form-signin" action="login.php" method="post">
            <div class="text-center mb-4">
                <img class="mb-4" src="../assets/images/users/STOS.png" alt="" width="150" height="150">
                <h1 class="h3 mb-3 font-weight-normal">成员登陆</h1>
                <pre>用户名：学号  密码：注册时设定的密码<br/>新成员请先<a href="register.php">录入个人信息</a></pre>
            </div>

            <div class="form-label-group">
                <input type="text" id="inputUsername" class="form-control" placeholder="用户名" name='U_N' <?php if(isset($_COOKIE["R_C"])){$t = explode(" ",$_COOKIE["R_C"])[0]; echo("value={$t}");} elseif( isset($_POST['login'])) echo("value={$_POST['U_N']}"); ?> required autofocus>
                <label id='label_username' for="inputUsername">用户名</label>
            </div>

            <div class="form-label-group">
                <input type='password' id='inputPassword' class='form-control' placeholder='密码' name='P_W' <?php if(isset($_COOKIE["R_C"])){$t = explode(" ",$_COOKIE["R_C"])[1]; echo("value={$t}");} ?> required>
                <?php
                if ( $wrong == 'yes' )
                    echo( "<label style='color:red' for='inputPassword'>密码错误</label>" );
                else
                    echo( "<label id='label_password' for='inputPassword'>密码</label>" );
                ?>
            </div>

            <div class="checkbox mb-3">
                <label>
                    <input type="checkbox" name="R_C" value="yes" <?php if(isset($_COOKIE['R_C']))echo("checked");?>> 记住用户名和密码
                </label>
            </div>
            <button class="btn btn-lg btn-primary btn-block" type="submit" name="login"><span data-feather="log-in"></span> 登陆</button>
            <p class="mt-5 mb-3 text-muted text-center">2019 电子科技大学 学工部 学风督导队</p>
        </form>
    </main>

    <script>
        var getExplorer = ( function () {
            var explorer = window.navigator.userAgent,
                compare = function ( s ) {
                    return ( explorer.indexOf( s ) >= 0 );
                },
                ie11 = ( function () {
                    return ( "ActiveXObject" in window )
                } )();
            if ( compare( "MSIE" ) || ie11 ) {
                return 'ie';
            } else if ( compare( "Firefox" ) && !ie11 ) {
                return 'Firefox';
            } else if ( compare( "Chrome" ) && !ie11 ) {
                if ( explorer.indexOf( "Edge" ) > -1 ) {
                    return 'Edge';
                } else {
                    return 'Chrome';
                }
            } else if ( compare( "Opera" ) && !ie11 ) {
                return 'Opera';
            } else if ( compare( "Safari" ) && !ie11 ) {
                return 'Safari';
            }

        } )()

        if ( getExplorer == 'ie' ) {
            document.getElementById( "logincss" ).href = "../bootstrap-4.0.0/css/extra/signin.css";
            var p = document.getElementById( "label_username" );
            p.parentNode.removeChild( p );
            var p = document.getElementById( "label_password" );
            p.parentNode.removeChild( p );
        }
        if ( getExplorer == 'Edge' ) {
            document.getElementById( "logincss" ).href = "../bootstrap-4.0.0/css/extra/signin.css";
            var p = document.getElementById( "label_username" );
            p.parentNode.removeChild( p );
            var p = document.getElementById( "label_password" );
            p.parentNode.removeChild( p );
        }
    </script>

	<script>
		var xmlhttp;
		if (window.XMLHttpRequest) {
                // IE7+, Firefox, Chrome, Opera, Safari 浏览器执行代码
                xmlhttp=new XMLHttpRequest();
            }
            else {
                // IE6, IE5 浏览器执行代码
                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState==4 && xmlhttp.status==200) {
					document.getElementById("navbar").innerHTML = xmlhttp.responseText;
				}
			}
			xmlhttp.open("GET","http://132.232.231.109/frame/index_head_frame.html",false);
            xmlhttp.send();
	</script>
	
    <!-- Icons -->
    <script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
    <script>
        feather.replace()
    </script>
</body>
</html>
