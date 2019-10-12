<?php
session_start();
require("../frame/Person_Class_frame.php");

if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
    $connection = new STOS_MySQL(); // 建立数据库连接
    $person = new person_all_info( $_SESSION[ "username" ] ); // 获取个人信息
}
else { // 没有登陆但是cookie中存有登陆信息
    //检查cookie
    if ( isset( $_COOKIE[ 'username' ] ) ) {
        $_SESSION[ 'username' ] = $_COKIE[ 'username' ];
        $_SESSION[ 'islogin' ] = 1;

        $connection = new STOS_MySQL();
        $person = new person_all_info( $_SESSION[ "username" ] );
    }
    else
        header( 'refresh:0; url=../log/login.php' ); // 返回登陆页面
}
?>

<!DOCTYPE html>
<html lang="zh,en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Personal Index Page">
    <meta name="author" content="罗寅松, 张锐">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/STOS.png">
    <title>岗位信息</title>
    <!-- Bootstrap Core CSS -->
    <link href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
    <?php require("../frame/empty_time.php"); ?>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
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
                        <h3 class="text-themecolor">岗位信息</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)"><?php echo $person->xinming; ?></a></li>
                            <li class="breadcrumb-item active">岗位信息</li>
                        </ol>
                    </div>
                    <!-- <div class="col-md-7 col-4 align-self-center">
                        <a href="#" class="btn waves-effect waves-light btn-danger pull-right hidden-sm-down"> Upgrade to Pro</a>
                    </div> -->
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>注意!</strong> 空课时间如有错误，请及时联系组长对数据进行更正，否则会影响查课排班。
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-xlg-3 col-md-5">
                        <div class="card">
                            <img class="card-img-top" src="../assets/images/background/profile-bg.jpg" alt="Card image cap">
                            <div class="card-block little-profile text-center">
                                <div class="pro-img"><img src="../assets/images/users/STOS.png" alt="user" /></div>
                                <h1 class="m-b-0"><?php echo $person->xinming; ?></h1>
                                <!-- <p> 现场二组&amp; 组长</p> -->
                                <!-- <a href="javascript:void(0)" class="m-t-10 waves-effect waves-dark btn btn-primary btn-md btn-rounded">Follow</a> -->
                                <div class="row text-center m-t-20">
                                    <div class="col-lg-4 col-md-4 m-t-20">
                                        <h3 class="m-b-0 font-light">所属组</h3><h5><?php echo($person->work_info()["所属组"]); ?></h5></div>
                                    <div class="col-lg-4 col-md-4 m-t-20">
                                        <h3 class="m-b-0 font-light">岗位</h3><h5><?php echo($person->work_info()["岗位"]); ?></h5></div>
                                    <div class="col-lg-4 col-md-4 m-t-20">
                                        <!-- <h3 class="m-b-0 font-light">工资</h3><small>350</small></div> -->
                                        <h3 class="m-b-0 font-light">工资</h3><h5><?php echo($person->work_info()["工资"]); ?></h5></div>
                                </div>
                            </div>
                        </div>
                        <div class="crad">
                            <div class="card-block bg-info">
                                <h3 class="text-white card-title">岗位备注</h3>
                                <h5 class="card-subtitle text-white m-b-0 op-5">一些与岗位相关的信息将发布在这里</h5>
                            </div>
                            <div class="card-block">
                                <div class="message-box contact-box">
                                    <h2 class="add-ct-btn"><button type="button" class="btn btn-circle btn-lg btn-success waves-effect waves-dark">+</button></h2>
                                    <div class="message-widget contact-widget">
                                        <!-- Message -->
                                        <a href="#">
                                            <!-- <div class="user-img"> <img src="./assets/images/users/1.jpg" alt="user" class="img-circle"> <span class="profile-status online pull-right"></span> </div> -->
                                            <div class="mail-contnet">
                                                <h5>备注1:</h5> <span class="mail-desc"><h6><?php echo($person->work_info()["备注"]); ?></h6></span></div>
                                        </a>
                                        <!-- Message -->
                                        <a href="#">
                                            <!-- <div class="user-img"> <img src="./assets/images/users/2.jpg" alt="user" class="img-circle"> <span class="profile-status busy pull-right"></span> </div> -->
                                            <div class="mail-contnet">
                                                <h5>备注2</h5> <span class="mail-desc"><h6><?php echo($person->work_info()["备注"]); ?></h6></span></div>
                                        </a>
                                        <!-- Message -->
                                        <a href="#">
                                            <!-- <div class="user-img"> <span class="round">A</span> <span class="profile-status away pull-right"></span> </div> -->
                                            <div class="mail-contnet">
                                                <h5>备注3</h5> <span class="mail-desc"><h6><?php echo($person->work_info()["备注"]); ?></h6></span></div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-xlg-9 col-md-7">
                        <div class="card">
                            <div class="card-block">
                            <h3 class="mb-3">空课时间表</h3>
                                <div class="col-md-12 mb-3">
                                    <h6><img height='15' width='15' src='<?php echo $green;?>'>为空闲，<img height='15' width='15' src='<?php echo $red;?>'>为有课</h6>
                                    <table class="table table-striped table-sm">
                                        <thead class="text-center">
                                            <tr class="text-center">
                                                <th class="text-primary"><strong>单</strong></th>
                                                <th>周一</th>
                                                <th>周二</th>
                                                <th>周三</th>
                                                <th>周四</th>
                                                <th>周五</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <tr>
                                                <th>1-2节</th>
                                                <?php // 1-2节
                                                    echo get_empyt_color($person->work_info()["周一空课"],0)[0];
                                                    echo get_empyt_color($person->work_info()["周二空课"],0)[0];
                                                    echo get_empyt_color($person->work_info()["周三空课"],0)[0];
                                                    echo get_empyt_color($person->work_info()["周四空课"],0)[0];
                                                    echo get_empyt_color($person->work_info()["周五空课"],0)[0];
                                                ?>
                                            </tr>
                                            <tr>
                                                <th>3-4节</th>
                                                <?php // 1-2节
                                                    echo get_empyt_color($person->work_info()["周一空课"],1)[0];
                                                    echo get_empyt_color($person->work_info()["周二空课"],1)[0];
                                                    echo get_empyt_color($person->work_info()["周三空课"],1)[0];
                                                    echo get_empyt_color($person->work_info()["周四空课"],1)[0];
                                                    echo get_empyt_color($person->work_info()["周五空课"],1)[0];
                                                ?>
                                            </tr>
                                            <tr>
                                                <th>5-6节</th>
                                                <?php // 1-2节
                                                    echo get_empyt_color($person->work_info()["周一空课"],2)[0];
                                                    echo get_empyt_color($person->work_info()["周二空课"],2)[0];
                                                    echo get_empyt_color($person->work_info()["周三空课"],2)[0];
                                                    echo get_empyt_color($person->work_info()["周四空课"],2)[0];
                                                    echo get_empyt_color($person->work_info()["周五空课"],2)[0];
                                                ?>
                                            </tr>
                                            <tr>
                                                <th>7-8节</th>
                                                <?php // 1-2节
                                                    echo get_empyt_color($person->work_info()["周一空课"],3)[0];
                                                    echo get_empyt_color($person->work_info()["周二空课"],3)[0];
                                                    echo get_empyt_color($person->work_info()["周三空课"],3)[0];
                                                    echo get_empyt_color($person->work_info()["周四空课"],3)[0];
                                                    echo get_empyt_color($person->work_info()["周五空课"],3)[0];
                                                ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table class="table table-striped table-sm">
                                        <thead class="text-center">
                                            <tr>
                                                <th class="text-primary"><strong>双</strong></th>
                                                <th>周一</th>
                                                <th>周二</th>
                                                <th>周三</th>
                                                <th>周四</th>
                                                <th>周五</th>
                                            </tr>
                                        </thead>
                                         <tbody class="text-center">
                                            <tr>
                                                <th>1-2节</th>
                                                <?php // 1-2节
                                                    echo get_empyt_color($person->work_info()["周一空课"],0)[1];
                                                    echo get_empyt_color($person->work_info()["周二空课"],0)[1];
                                                    echo get_empyt_color($person->work_info()["周三空课"],0)[1];
                                                    echo get_empyt_color($person->work_info()["周四空课"],0)[1];
                                                    echo get_empyt_color($person->work_info()["周五空课"],0)[1];
                                                ?>
                                            </tr>
                                            <tr>
                                                <th>3-4节</th>
                                                <?php // 1-2节
                                                    echo get_empyt_color($person->work_info()["周一空课"],1)[1];
                                                    echo get_empyt_color($person->work_info()["周二空课"],1)[1];
                                                    echo get_empyt_color($person->work_info()["周三空课"],1)[1];
                                                    echo get_empyt_color($person->work_info()["周四空课"],1)[1];
                                                    echo get_empyt_color($person->work_info()["周五空课"],1)[1];
                                                ?>
                                            </tr>
                                            <tr>
                                                <th>5-6节</th>
                                                <?php // 1-2节
                                                    echo get_empyt_color($person->work_info()["周一空课"],2)[1];
                                                    echo get_empyt_color($person->work_info()["周二空课"],2)[1];
                                                    echo get_empyt_color($person->work_info()["周三空课"],2)[1];
                                                    echo get_empyt_color($person->work_info()["周四空课"],2)[1];
                                                    echo get_empyt_color($person->work_info()["周五空课"],2)[1];
                                                ?>
                                            </tr>
                                            <tr>
                                                <th>7-8节</th>
                                                <?php // 1-2节
                                                    echo get_empyt_color($person->work_info()["周一空课"],3)[1];
                                                    echo get_empyt_color($person->work_info()["周二空课"],3)[1];
                                                    echo get_empyt_color($person->work_info()["周三空课"],3)[1];
                                                    echo get_empyt_color($person->work_info()["周四空课"],3)[1];
                                                    echo get_empyt_color($person->work_info()["周五空课"],3)[1];
                                                ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>   

            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer"> © 2019 学风督导队 <i class="mdi mdi-account-multiple"></i><span>罗寅松 张锐 吴金辰 </span>  </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
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
