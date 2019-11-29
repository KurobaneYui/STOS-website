<?php // 如有录入信息，进行检查和上传数据库
require("../frame/Person_Class_frame.php");

//一些用于反馈注册结果的变量
$yicunzai = false; // 信息已存在
$info_error = false; // 信息存在错误，没有错误false，存在错误返回错误数组
$upload_success = false; // 信息提交成功

if(isset($_POST["注册"]) and $_POST["注册"]=="yes"/*false*/) { // 如果有提交注册信息
    $person = new person_all_info($_POST["学号"]);
    if($person->already_had()) {
        $yicunzai = true;
    }
    else {
        // 写入个人信息
        $person->xinming = $_POST["姓名"];
        $person->xinbie = $_POST["性别"];
        $person->xueyuan = $_POST["学院"];
        $person->minzu = $_POST["民族"];
        $person->jiguan = $_POST["籍贯"];
        $person->dianhua = $_POST["电话"];
        $person->QQ = $_POST["QQ"];
        $person->qinshi_yuan = $_POST["寝室_苑"];
        $person->qinshi_lou = $_POST["寝室_楼"];
        $person->qinshi_hao = $_POST["寝室_号"];
        $person->gongzishenqingshiyinhangkahao = $_POST["工资申请时银行卡号"];
        $person->gongzishenqingshixingming = $person->xinming;
        $person->gongzishenqingshixuehao = $person->xuehao;
        $person->recorder = '否';
        $person->mima = $_POST["密码"];
        
        $person->init_authentic_work_info();
        
        if($person->check_data()===true) {
            $yicunzai = false;
            $upload_success = true;
            $info_error = false;
            
            $person->upload_personal_info();
            $person->upload_personal_work_info();
            $person->upload_mima();
            $person->insert_authentic();
        }
        else {
            $info_error = $person->check_data();
            $upload_success = false;
            $yicunzai = false;
        }
    }
}
?>
<!doctype html>
<html lang="zh, en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Personal Index Page">
    <meta name="author" content="Luo Yinsong">
    <link rel="icon" href="../assets/images/users/STOS.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>成员信息录入</title>

    <!-- 新 Bootstrap4 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/4.1.0/css/bootstrap.min.css">

    <!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
    <script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>

    <!-- popper.min.js 用于弹窗、提示、下拉菜单 -->
    <script src="https://cdn.staticfile.org/popper.js/1.12.5/umd/popper.min.js"></script>

    <!-- 最新的 Bootstrap4 核心 JavaScript 文件 -->
    <script src="../bootstrap-4.0.0/js/bootstrap.min.js"></script>

    <!-- Custom styles for this template -->
    <link href="../bootstrap-4.0.0/css/extra/form-validation.css" rel="stylesheet">
    <link href="../bootstrap-4.0.0/css/extra/jumbotron.css" rel="stylesheet">
</head>

<body class="bg-light">
	<nav id="navbar" class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
		<!--ajax替换文本-->
	</nav>
    <main role="main">
        <div class="container">
        <?php
        if($yicunzai===true) { echo("<h2>提交学号的成员已存在，请重新检查输入的学号</h2><p><a href='register.php'>请重新录入</a></p>"); }
        elseif(!($info_error===false)) { echo("<h2>提交的信息有误</h2><p><a href='register.php'>请重新录入</a></p><br/>"); foreach($info_error as $key=>$value) { echo("<p>{$key}：{$value}</p>"); } }
        elseif($upload_success===true) { echo("<h2>信息提交成功</h2><p>你可以<a href='login.php'>登陆</a>个人中心查看个人信息、岗位信息和工作安排等</p>"); }
        ?>
            
        <?php if(isset($_POST["注册"]) and $_POST["注册"]=="yes") echo("<!--"); ?>
            <div class="py-5 text-center">
                <h2>个人信息注册</h2>
                <p class="lead">
                    本页信息中，除必要联系方式信息展示给组长外，所有信息不会展示给其他队员。<br/>由于部分信息涉及工资申报，请在信息发生变动时及时在<strong>个人中心</strong>修改。
                </p>
            </div>

            <div class="row">
                <div class="col-md-8 order-md-1 offset-md-2">
                    <h4 class="mb-3">基本信息</h4>
                    <form class="needs-validation" novalidate action="#" method="post">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="Name">姓名</label>
                                <input type="text" class="form-control" id="Name" placeholder="" name="姓名" required>
                                <div class="invalid-feedback">
                                    需要填写有效姓名
                                </div>
                            </div>
                            <div class="col-md-2 mb-3 d-block">
                                <label for="gender">性别</label>
                                <div id="gender" class="custom-control custom-radio">
                                    <input id="man" name="性别" value="男" type="radio" class="custom-control-input" checked required>
                                    <label class="custom-control-label" for="man">男</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input id="woman" name="性别" value="女" type="radio" class="custom-control-input" required>
                                    <label class="custom-control-label" for="woman">女</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="school">学院（全称）</label>
                                <input type="text" class="form-control" id="school" placeholder="" name="学院" required>
                                <div class="invalid-feedback">
                                    需要填写有效学院
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="studentID">学号</label>
                                <input type="text" class="form-control" id="studentID" placeholder="" name="学号" required>
                                <div class="invalid-feedback">
                                    需要填写有效学号
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="peoples">民族</label>
                                <input type="text" class="form-control" id="peoples" placeholder="民族" name="民族" required>
                                <div class="invalid-feedback" style="width: 100%;">
                                    需要填写民族
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hometown">籍贯</label>
                                <input type="text" class="form-control" id="hometown" placeholder="籍贯" name="籍贯" required>
                                <div class="invalid-feedback" style="width: 100%;">
                                    需要填写籍贯
                                </div>
                            </div>
                        </div>

                        <hr class="mb-4">

                        <h4 class="mb-3">联系方式及工资卡</h4>

                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="phone">手机号</label>
                                <input type="text" class="form-control" id="phone" placeholder="手机号" name="电话" required>
                                <div class="invalid-feedback" style="width: 100%;">
                                    需要填写有效手机号
                                </div>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="QQ">QQ号</label>
                                <input type="text" class="form-control" id="QQ" placeholder="QQ号" name="QQ" required>
                                <div class="invalid-feedback" style="width: 100%;">
                                    需要填写QQ号
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="grade">寝室</label>
                                <select class="custom-select" id="grade" name="寝室_苑" required>
                                    <option value="">Choose...</option>
                                    <option value="学知苑">学知苑</option>
                                    <option value="硕丰苑">硕丰苑</option>
                                </select>
                                <div class="invalid-feedback" style="width: 100%;">
                                请选择
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="buildings">楼栋号</label>
                                <input type="text" class="form-control" id="buildings" placeholder="例:8" name="寝室_楼" required>
                                <div class="invalid-feedback" style="width: 100%;">
                                请提供有效寝室楼栋号
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="room">房间号</label>
                                <input type="text" class="form-control" id="room" placeholder="例:230" name="寝室_号" required>
                                <div class="invalid-feedback" style="width: 100%;">
                                请提供有效寝室房间号
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label for="bankID">银行卡号</label>
                                <input type="text" class="form-control" id="bankID" placeholder="银行卡号" name="工资申请时银行卡号" required>
                                <div class="invalid-feedback">
                                    需要填写有效银行卡号
                                </div>
                            </div>
                        </div>

                        <hr class="mb-4">

                        <div class="row">
                            <div class="col-md-7 mb-5">
                                <label for="login_pw">设定登陆密码（账户名固定为学号）<br/>密码（6-18位）可由：字母、数字和以下特殊字符组成：<br/>!#$%&'*+-/=?^_`{|}~.[]</label>
                                <input type="text" class="form-control" id="login_pw" placeholder="6-10位字母数字" name="密码" required>
                                <div class="invalid-feedback">
                                    请填写有效密码
                                </div>
                            </div>
                        </div>
                        
                        <hr class="mb-4">
                        <button class="btn btn-primary btn-lg btn-block" type="submit" name="注册" value="yes">提交信息</button>
                    </form>
                    <br/>
                </div>
            </div>
            <?php if(isset($_POST["注册"]) and $_POST["注册"]=="yes") echo("-->"); ?>
        </div>
        
        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">2019 学风督导队</p>
        </footer>
    </main>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
<!--    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>-->
    <script>
        window.jQuery || document.write( '<script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"><\/script>' )
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
    <script src="https://v4.bootcss.com/assets/js/vendor/holder.min.js"></script>
    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        ( function () {
            'use strict';

            window.addEventListener( 'load', function () {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName( 'needs-validation' );

                // Loop over them and prevent submission
                var validation = Array.prototype.filter.call( forms, function ( form ) {
                    form.addEventListener( 'submit', function ( event ) {
                        if ( form.checkValidity() === false ) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add( 'was-validated' );
                    }, false );
                } );
            }, false );
        } )();
    </script>
</body>
</html>
