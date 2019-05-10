<?php
session_start();
require("../../frame/Person_Class_frame.php");

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
        header( 'refresh:0; url=../../log/login.php' ); // 返回登陆页面
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
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/STOS.png">
    <title>组员工作</title>
    <!-- Bootstrap Core CSS -->
    <link href="../../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- chartist CSS -->
    <link href="../../assets/plugins/chartist-js/dist/chartist.min.css" rel="stylesheet">
    <link href="../../assets/plugins/chartist-js/dist/chartist-init.css" rel="stylesheet">
    <link href="../../assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.css" rel="stylesheet">
    <!--This page css - Morris CSS -->
    <link href="../../assets/plugins/c3-master/c3.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../css/style.css" rel="stylesheet">
    <!-- You can change the theme colors from here -->
    <link href="../../css/colors/blue.css" id="theme" rel="stylesheet">
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
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <?php require('../../frame/personal_head_frame.php'); ?>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <?php require('../../frame/personal_side_frame.php'); ?>
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
                <div class="row page-titles">
                        <div class="col-md-5 col-8 align-self-center">
                            <h3 class="text-themecolor m-b-0 m-t-0">组员工作</h3>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)"><?php echo $person->xinming; ?></a></li>
                                <li class="breadcrumb-item active">组员工作</li>
                            </ol>
                        </div>
                        <!-- <div class="col-md-7 col-4 align-self-center">
                            <a href="#" class="btn waves-effect waves-light btn-danger pull-right hidden-sm-down"> Upgrade to Pro</a>
                        </div> -->
                </div>
                <?php
				$conn_temp = new STOS_MySQL_data();
                foreach($person->work_info()["管理组"] as $value) {
                    //echo("<h3 class='card-title'>{$value} 工作数据（仅供现场组）</h3>");
                    
                    $group_memberID = $connection->personal_group($value);
					$memberIDs = array();
					while($memberID = $group_memberID->fetch_assoc()) { // 获取组员学号
						array_push($memberIDs,$memberID["学号"]);
					}
					$all_days = getWeekRange(strtotime(date("Y-m-d",time())."-7day"),1)[2]; // 获取本周七天的日期
					
					$paibans = array();
					$sql = "SELECT * FROM `早自习排班` WHERE `周起始日期`='{$all_days[0]}' and `查早组员` IN ('".join('\',\'',$memberIDs)."');";
					if($re = $conn_temp->execute_query($sql)) {
						while($re_ = $re->fetch_assoc()) {
							$paibans[$re_["查早组员"]] = $re_;
						}
					}
					$jiaoshishujus = array();
					$sql = "SELECT * FROM `查早数据` WHERE `日期` IN ('".join('\',\'',$all_days)."') and `提交者` IN ('".join('\',\'',$memberIDs)."') ORDER BY `日期` ASC;";
					if($re = $conn_temp->execute_query($sql)) {
						while($re_ = $re->fetch_assoc()) {
							if(isset($jiaoshishujus[$re_["提交者"]])) {
								array_push($jiaoshishujus[$re_["提交者"]],$re_);
							}
							else {
								$jiaoshishujus[$re_["提交者"]] = array($re_);
							}
						}
					}

					foreach($memberIDs as $memberID) {
						$temp = new person_all_info($memberID);
                        echo("<div class='row'>
                                <div class='col-lg-12'>
                                    <div class='card'>
                                        <div class='card-block'>");
                        echo("<h4 class='card-title'>{$temp->xinming}</h4>");
                        echo("<h5 class = 'card-title'>{$paibans[$memberID]['教学楼']}{$paibans[$memberID]['区号']}{$paibans[$memberID]['教室编号']}  {$paibans[$memberID]["学院"]} -");
                        $temp->__destruct();
						
						$onclick = "get_queqin_data('{$all_days[0]}','{$memberID}')";
						echo(" <button id='btn{$memberID}' onClick={$onclick} class='btn btn-secondary' type='button' data-container='#table{$memberID}' data-toggle='popover' data-placement='bottom' data-html='true' data-delay=200 title='缺勤人员名单'>缺勤表测试中....</button></h5>");
						
                        echo("
                        <div class='table-responsive'>
                            <table id='table{$memberID}' class='table'>
                                <thead>
                                    <tr>
                                        <th>日期</th>
                                        <th>应到人数</th>
                                        <th>迟到人数</th>
                                        <th>第一次出勤</th>
                                        <th>违纪人数</th>
                                        <th>第二次出勤</th>
                                        <th>早退人数</th>
										<th>请假人数</th>
                                        <th>备注</th>
                                    </tr>
                                </thead>
                                <tbody> 
                        ");
                        $zhou = array("周一","周二","周三","周四","周五","周六","周日");
						$num = 0;
                        foreach($zhou as $key=>$value) {
							$jiaoshishuju = json_decode($jiaoshishujus[$memberID][$num]['教室数据'],true);
                            echo("<tr><td>{$value}</td>");
							if($all_days[$key]!=$jiaoshishujus[$memberID][$num]['日期'])continue;
                            echo("<td>{$paibans[$memberID]["应到人数"]}</td>");
                            echo("<td>{$jiaoshishuju['迟到人数']}</td>");
                            echo("<td>{$jiaoshishuju['第一次出勤']}</td>");
                            echo("<td>{$jiaoshishuju['违纪人数']}</td>");
                            echo("<td>{$jiaoshishuju['第二次出勤']}</td>");
                            echo("<td>{$jiaoshishuju['早退人数']}</td>");
							echo("<td>{$jiaoshishuju['请假人数']}</td>");
                            echo("<td>{$jiaoshishuju['备注']}</td>");
                            echo("</tr>");
							$num++;
                        }
                        echo("
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        ");
                    }
                }?>
                            <!-- </div>
                        </div>
                    </div>
                </div> -->
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
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
	<!-- ============================================================== -->
    <!-- Custom script -->
    <!-- ============================================================== -->
	<script>
		function get_queqin_data(day,memberID){
            var xmlhttp;
            var re;
            if (window.XMLHttpRequest)
            {
                // IE7+, Firefox, Chrome, Opera, Safari 浏览器执行代码
                xmlhttp=new XMLHttpRequest();
            }
            else
            {
                // IE6, IE5 浏览器执行代码
                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange=function()
            {
                if (xmlhttp.readyState==4 && xmlhttp.status==200)
                {
					document.getElementById("btn"+memberID).setAttribute("data-content",xmlhttp.responseText);
                }
            }
            xmlhttp.open("GET","http://132.232.231.109/ajax/personal/group_leader/member_work.php?day="+day+"&memberID="+memberID,true);
            xmlhttp.send();
        }
    </script>
    
	<!-- ============================================================== -->
    <!-- End Custom script -->
    <!-- ============================================================== -->
    <script src="../../assets/plugins/jquery/jquery.min.js"></script>
    <script src="../../bootstrap-4.0.0/js/bootstrap.bundle.min.js"></script>
    <script>$(function () {
    $('[data-toggle="popover"]').popover()
    })</script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="../../assets/plugins/bootstrap/js/tether.min.js"></script>
    <script src="../../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="../../js/jquery.slimscroll.js"></script>
    <!--Wave Effects -->
    <script src="../../js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="../../js/sidebarmenu.js"></script>
    <!--stickey kit -->
    <script src="../../assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <!--Custom JavaScript -->
    <script src="../../js/custom.min.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- chartist chart -->
    <script src="../../assets/plugins/chartist-js/dist/chartist.min.js"></script>
    <script src="../../assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.min.js"></script>
    <!--c3 JavaScript -->
    <script src="../../assets/plugins/d3/d3.min.js"></script>
    <script src="../../assets/plugins/c3-master/c3.min.js"></script>
    <!-- Chart JS -->
    <script src="../../js/dashboard1.js"></script>
</body>

</html>
