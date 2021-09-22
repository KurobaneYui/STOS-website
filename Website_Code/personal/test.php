<?php
session_start();
require ('../ROOT_PATH.php');
require (ROOT_PATH.'/frame/php_frame/Database_connector.php');
require (ROOT_PATH.'/frame/php_frame/Person_connector.php');

if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
    $connection = new Database_connector(ROOT_PATH.'/config/DataBase_CollectionData.conf'); // 建立数据库连接
    $person = new Person_connector( $_SESSION[ "username" ] ); // 获取个人信息
}
else { // 没有登陆但是cookie中存有登陆信息
    //检查cookie
    if ( isset( $_COOKIE[ 'username' ] ) ) {
        $_SESSION[ 'username' ] = $_COOKIE[ 'username' ];
        $_SESSION[ 'islogin' ] = 1;

        $connection = new Database_connector(ROOT_PATH.'/config/DataBase_CollectionData.conf'); // 建立数据库连接
        $person = new Person_connector( $_SESSION[ "username" ] ); // 获取个人信息
    }
    else
        header( 'refresh:0; url=../log/login.php' ); // 返回登陆页面
}
?>

<?php
//一些用于反馈注册结果的变量
$info_error = array(); // 信息存在错误，没有错误是空数组，存在错误返回错误数组
$upload_success = false; // 信息提交成功

if(isset($_POST["修改"]) and $_POST["修改"]=="yes") { // 如果有提交注册信息
    // 写入个人信息
    $person->change_info(
        array(
            '姓名'=>$_POST["姓名"]
        )
    );
    $upload_success = $person->commit_basic_information();
    $info_error = array();
}
?>

    <!doctype html>
    <html lang="zh, en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Personal Index Page">
        <meta name="author" content="罗寅松, 张锐">
        <!-- Favicon icon -->
        <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/STOS.png">
        <title>个人信息</title>
        <!-- Bootstrap Core CSS -->
        <link href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
        <script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>
        <!-- popper.min.js 用于弹窗、提示、下拉菜单 -->
        <script src="https://cdn.staticfile.org/popper.js/1.12.5/umd/popper.min.js"></script>
        <!-- chartist CSS -->
        <link href="../assets/plugins/chartist-js/dist/chartist.min.css" rel="stylesheet">
        <link href="../assets/plugins/chartist-js/dist/chartist-init.css" rel="stylesheet">
        <link href="../assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.css" rel="stylesheet">
        <!--This page css - Morris CSS -->
        <link href="../assets/plugins/c3-master/c3.min.css" rel="stylesheet">
        <!-- Custom CSS -->
        <link href="../css/style.css" rel="stylesheet">
        <!-- You can change the theme colors from here -->
        <link href="../css/colors/blue.css" id="theme" rel="stylesheet">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body class="fix-header fix-sidebar card-no-border">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"></circle> </svg>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <?php require('../frame/personal_head_frame.php'); ?>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <?php require('../frame/personal_side_frame.php'); ?>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- =====here======================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row page-titles">
                    <div class="col-md-5 col-8 align-self-center">
                        <h3 class="text-themecolor">个人信息</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)"><?php echo $person->basic_info()['姓名']; ?></a></li>
                            <li class="breadcrumb-item active">个人信息</li>
                        </ol>
                    </div>
                    <!-- <div class="col-md-7 col-4 align-self-center">
                        <a href="#" class="btn waves-effect waves-light btn-danger pull-right hidden-sm-down"> Upgrade to Pro</a>
                    </div> -->
                    <!-- Start Page Content -->
                    <!-- ============================================================== -->
                    <!-- Row -->
                    <div class="row">
                        <!-- Column -->
                        <div class="col-lg-4 col-xlg-3 col-md-5">
                            <div class="card">
                                <div class="card-block">
                                    <center class="m-t-30"> <img src="../assets/images/users/5.jpg" class="img-circle" width="150" />
                                        <h4 class="card-title m-t-10">修改个人信息</h4>
                                        <h6 class="card-subtitle">本页信息中，除必要联系方式信息展示给组长外所有信息不会展示给其他队员</h6>
                                        <h6 class="card-subtitle">由于部分信息涉及工资申报，请在信息发生变动时及时修改</h6>
                                        <!-- <div class="row text-center justify-content-md-center">
                                            <div class="col-4"><a href="javascript:void(0)" class="link"><i class="icon-people"></i> <font class="font-medium">254</font></a></div>
                                            <div class="col-4"><a href="javascript:void(0)" class="link"><i class="icon-picture"></i> <font class="font-medium">54</font></a></div>
                                        </div> -->
                                    </center>
                                </div>
                            </div>
                        </div>
                        <!-- Column -->
                        <!-- Column -->
                        <div class="col-lg-8 col-xlg-9 col-md-7">
                            <div class="card">
                                <div class="card-block">
                                    <?php
                                    if(!empty($info_error)) { echo("<h2>提交的信息有误</h2><p>本次提交我们不会修改你的个人信息，请检查后重新填写</p><br/>"); foreach($info_error as $key=>$value) { echo("<p>{$key}：{$value}</p>"); } }
                                    elseif($upload_success===true) { echo("<h2>信息提交成功</h2><p>个人信息已修改，你可以刷新本页查看修改后信息</p>"); }
                                    ?>
                                    <form class="form-horizontal form-material needs-validation" id="myform" novalidate action="test.php" method="post">
                                        <h3 class ="card-title">基本信息</h3>
                                        <hr class="mb-12">
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="Name">姓名</label>
                                                <input type="text" class="form-control" id="Name" placeholder="" name="姓名" <?php echo("value={$person->basic_info()['姓名']}"); ?> required>
                                                <!-- <div class="invalid-feedback">
                                                    需要填写有效姓名
                                                </div> -->
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="gender" class="col-md-12">性别</label>
                                            <div class="col-md-12 custom-control custom-radi" id="gender">
                                                <input class="form-control" id="man" name="性别" value="男" type="radio" <?php if($person->basic_info()['性别']=="男")echo("checked"); ?> required>
                                                <label class="custom-control-label" for="man">男</label>
                                            </div>
                                            <div class="col-md-12 custom-control custom-radi">
                                                <input class="form-control" id="woman" name="性别" value="女" type="radio" <?php if($person->basic_info()['性别']=="女")echo("checked"); ?> required>
                                                <label class="custom-control-label" for="woman">女</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-12 mb-3">
                                                <label for="school">学院（全称）</label>
                                                <input type="text" class="form-control form-control-line" id="school" placeholder="" name="学院" <?php echo("value={$person->basic_info()['学院']}"); ?> required>
                                                <!-- <div class="invalid-feedback">
                                                    需要填写有效学院
                                                </div> -->
                                            </div>

                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="peoples">民族</label>
                                                <input type="text" class="form-control form-control-line" id="peoples" placeholder="民族" name="民族" <?php echo("value={$person->basic_info()['民族']}"); ?> required>
                                                <!-- <div class="invalid-feedback" style="width: 100%;">
                                                    需要填写民族
                                                </div>                                     -->
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="hometown">籍贯</label>
                                                <input type="text" class="form-control form-control-line" id="hometown" placeholder="籍贯" name="籍贯" <?php echo("value={$person->basic_info()['籍贯']}"); ?> required>
                                                <!-- <div class="invalid-feedback" style="width: 100%;">
                                                    需要填写籍贯
                                                </div> -->
                                            </div>
                                        </div>

                                        <h3 class ="card-title">联系方式及工资卡</h3>
                                        <hr class="mb-12">
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="phone">手机号</label>
                                                <input type="text" class="form-control form-control-line" id="phone" placeholder="手机号" name="电话" <?php echo("value={$person->basic_info()['电话']}"); ?> required>
                                                <!-- <div class="invalid-feedback" style="width: 100%;">
                                                    需要填写有效手机号
                                                </div> -->
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="QQ">QQ号</label>
                                                <input type="text" class="form-control form-control-line" id="QQ" placeholder="QQ号" name="QQ" <?php echo("value={$person->basic_info()['QQ']}"); ?> required>
                                                <!-- <div class="invalid-feedback" style="width: 100%;">
                                                    需要填写QQ号
                                                </div> -->
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="grade">寝室</label>
                                                <select class="form-control" id="grade" name="寝室_苑" required>>
                                                    <option value="">Choose...</option>
                                                    <option value="学知苑" <?php if($person->basic_info()['寝室_苑']=="学知苑")echo("selected"); ?>>学知苑</option>
                                                    <option value="硕丰苑" <?php if($person->basic_info()['寝室_苑']=="硕丰苑")echo("selected"); ?>>硕丰苑</option>
                                                </select>
                                                <!-- <div class="invalid-feedback" style="width: 100%;">
                                                    请选择
                                                </div> -->
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="buildings">楼栋号</label>
                                                <input type="text" class="form-control" id="buildings" placeholder="例:8" name="寝室_楼" <?php echo("value={$person->basic_info()['寝室_楼']}"); ?> required>
                                                <!-- <div class="invalid-feedback" style="width: 100%;">
                                                    请提供有效寝室房间号
                                                </div> -->
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="room">房间号</label>
                                                <input type="text" class="form-control" id="room" placeholder="例:230" name="寝室_号" <?php echo("value={$person->basic_info()['寝室_号']}"); ?> required>
                                                <!-- <div class="invalid-feedback" style="width: 100%;">
                                                    请提供有效寝室房间号
                                                </div> -->
                                            </div>
                                        </div>
                                        <hr/>
                                        <h3 class ="card-title">工资申请所需信息</h3>
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="shenqingzheName">申请者姓名</label>
                                                <input type="text" class="form-control form-control-line" id="shenqingzheName" placeholder="" name="工资申请时姓名" <?php echo("value='{$person->basic_info()['工资申请时姓名']}'"); ?>required>
                                                <!-- <div class="invalid-feedback" style="width: 100%;">
                                                    需要填写有效银行卡号
                                                </div> -->
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="shenqingzheID">申请者学号</label>
                                                <input type="text" class="form-control form-control-line" id="shenqingzheID" placeholder="" name="工资申请时学号" <?php echo("value='{$person->basic_info()['工资申请时学号']}'"); ?>required>
                                                <!-- <div class="invalid-feedback" style="width: 100%;">
                                                    需要填写有效银行卡号
                                                </div> -->
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="bankID">申请者银行卡号</label>
                                                <input type="text" class="form-control form-control-line" id="bankID" placeholder="银行卡号" name="工资申请时银行卡号" <?php echo("value='{$person->basic_info()['工资申请时银行卡号']}'"); ?>required>
                                                <!-- <div class="invalid-feedback" style="width: 100%;">
                                                    需要填写有效银行卡号
                                                </div> -->
                                            </div>
                                        </div>

                                        <h3 class ="card-title">密码修改</h3>
                                        <hr class="mb-12">
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="mima">密码（6-18位）可由：字母、数字和以下特殊字符组成：<br/>!#$%&'*+-/=?^_`{|}~.[]</label>
                                                <input type="password" class="form-control form-control-line" id="mima" name="密码" <?php echo("value={$person->basic_info()['密码']}"); ?> required>
                                                <!-- <div class="invalid-feedback">
                                                    需要填写有效密码
                                                </div> -->
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <button class="btn btn-success btn-primary btn-block" type="submit" name="修改" value="yes">提交修改</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Column -->
                    </div>
                    <!-- Row -->
                    <!-- ============================================================== -->
                    <!-- End PAge Content -->
                </div>

            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer"> © 2019 学风督导队 <i class="mdi mdi-account-multiple"></i><span>罗寅松 张锐 吴金辰</span>  </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>

        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        ( function () {
            'use strict';

            window.addEventListener( 'load', function () {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementById( 'myform' );

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
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="../assets/plugins/bootstrap/js/tether.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="../js/jquery.slimscroll.js"></script>
    <!--Wave Effects -->
    <script src="../js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="../js/sidebarmenu.js"></script>
    <!--stickey kit -->
    <script src="../assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <!--Custom JavaScript -->
    <script src="../js/custom.min.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- chartist chart -->
    <script src="../assets/plugins/chartist-js/dist/chartist.min.js"></script>
    <script src="../assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.min.js"></script>
    <!--c3 JavaScript -->
    <script src="../assets/plugins/d3/d3.min.js"></script>
    <script src="../assets/plugins/c3-master/c3.min.js"></script>
    <!-- Chart JS -->
    <script src="../js/dashboard1.js"></script>
    </body>

    </html>
<?php
