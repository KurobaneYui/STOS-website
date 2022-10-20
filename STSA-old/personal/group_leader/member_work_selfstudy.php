<?php
session_start();
require("../../frame/Person_Class_frame.php");

if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
    $connection = new STOS_MySQL(); // 建立数据库连接
    $person = new person_all_info( $_SESSION[ "username" ] ); // 获取个人信息
	if($person->work_info()["权限"]!=2) header( 'refresh:0; url=../../log/logout.php' ); // 如果不是组长，强制登出
}
else { // 没有登陆
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
                <div class="raw">
                    <div id="txtHint" class="col-lg-12 col-md-12">
                        </div>
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
					$all_days = getWeekRange(time(),1)[2]; // 获取本周七天的日期
					
					$paibans = array();
					$sql = "SELECT * FROM `查早排班` WHERE `周起始日期`='{$all_days[0]}' and `查早组员` IN ('".join('\',\'',$memberIDs)."');";
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
                        echo("<h5 class = 'card-title'>{$paibans[$memberID]['教学楼']}{$paibans[$memberID]['区号']}{$paibans[$memberID]['教室编号']}  {$paibans[$memberID]["学院"]}");
						
						echo("<ul class='nav nav-tabs' id='myTab{$memberID}' role='tablist'>
						<li class='nav-item'>
							<a class='nav-link active' id='renshu-tab{$memberID}' data-toggle='tab' href='#renshu{$memberID}' role='tab' aria-controls='renshu{$memberID}' aria-selected='true'>教室人数数据</a>
						</li>
						<li class='nav-item'>
							<a class='nav-link' id='queqinbiao12-tab{$memberID}' data-toggle='tab' href='#queqinbiao12{$memberID}' role='tab' aria-controls='queqinbiao12{$memberID}' aria-selected='false'>周一二缺勤表</a>
						</li>
						<li class='nav-item'>
							<a class='nav-link' id='queqinbiao34-tab{$memberID}' data-toggle='tab' href='#queqinbiao34{$memberID}' role='tab' aria-controls='queqinbiao34{$memberID}' aria-selected='false'>周三四缺勤表</a>
						</li>
						<li class='nav-item'>
							<a class='nav-link' id='queqinbiao567-tab{$memberID}' data-toggle='tab' href='#queqinbiao567{$memberID}' role='tab' aria-controls='queqinbiao567{$memberID}' aria-selected='false'>周五六日缺勤表</a>
						</li>
						</ul>");
						
						echo("<div class='tab-content' id='myTabContent{$memberID}'>");
						
						echo("<div class='tab-pane fade show active' id='renshu{$memberID}' role='tabpanel' aria-labelledby='renshu-tab{$memberID}'>");
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
						$key_data = 0;
                        foreach($zhou as $key_week=>$value) {
                            echo("<tr><td>{$value}</td>");
							if($jiaoshishujus[$memberID][$key_data]['日期'] == $all_days[$key_week]) {
								$jiaoshishuju = json_decode($jiaoshishujus[$memberID][$key_data]['教室数据'],true);
								echo("<td>{$paibans[$memberID]["应到人数"]}</td>");
								echo("<td>{$jiaoshishuju['迟到人数']}</td>");
								echo("<td>{$jiaoshishuju['第一次出勤']}</td>");
								echo("<td>{$jiaoshishuju['违纪人数']}</td>");
								echo("<td>{$jiaoshishuju['第二次出勤']}</td>");
								echo("<td>{$jiaoshishuju['早退人数']}</td>");
								echo("<td>{$jiaoshishuju['请假人数']}</td>");
								echo("<td>{$jiaoshishuju['备注']}</td>");
								$key_data++;
							}
                            echo("</tr>");
                        }
                        echo("
                                        </tbody>
                                    </table>
                                </div>
								");
						
						echo("</div>");
						
						echo("<div class='tab-pane fade' id='queqinbiao12{$memberID}' role='tabpanel' aria-labelledby='queqinbiao12-tab{$memberID}'>");
						echo("
							<div class='row'>
								<div class='col-sm-6 table-responsive'>
									<table class='table'>
										<thead>
											<tr>
												<th colspan='3' class='text-center'>周一</th>
											</tr>
											<tr>
												<th class='text-center'>序号</th>
												<th class='text-center'>姓名</th>
												<th class='text-center'>学号</th>
											</tr>
										</thead>

										<tbody id='zhouyijimingbiao'>");
											$conn_temp = new STOS_MySQL_data();
											if($a = $conn_temp->search('缺勤人员名单',false,array('日期'=>$all_days[0],'提交者'=>$memberID),false)) {
												if($t = $a->fetch_assoc()['缺勤名单']) {
													$t = json_decode($t,true);
													$count = 1;
													foreach($t as $key=>$value){
														echo("<tr>");
														echo("<td class='text-center'>{$count}</td>");
														echo("<td class='text-center'>{$value}</td>");
														echo("<td class='text-center'>{$key}</td>");
														echo("</tr>");
														$count++;
													}
												}
											}
						echo("
										</tbody>
									</table>
								</div>
								<div class='col-sm-6 table-responsive'>
									<table class='table'>
										<thead>
											<tr>
												<th colspan='3' class='text-center'>周二</th>
											</tr>
											<tr>
												<th class='text-center'>序号</th>
												<th class='text-center'>姓名</th>
												<th class='text-center'>学号</th>
											</tr>
										</thead>


										<tbody id='zhouerjimingbiao'>");
											if($a = $conn_temp->search('缺勤人员名单',false,array('日期'=>$all_days[1],'提交者'=>$memberID))) {
												if($t = $a->fetch_assoc()['缺勤名单']) {
													$t = json_decode($t,true);
													$count = 1;
													foreach($t as $key=>$value){
														echo("<tr>");
														echo("<td class='text-center'>{$count}</td>");
														echo("<td class='text-center'>{$value}</td>");
														echo("<td class='text-center'>{$key}</td>");
														echo("</tr>");
														$count++;
													}
												}
											}
							echo("
										</tbody>
									</table>
								</div>
							</div>");
						echo("</div>");
						
						echo("<div class='tab-pane fade' id='queqinbiao34{$memberID}' role='tabpanel' aria-labelledby='queqinbiao34-tab{$memberID}'>");
                        echo("
                        <div class='row'>
                            <div class='col-sm-6 table-responsive'>
                                <table class='table'>
                                    <thead>
                                        <tr>
                                            <th colspan='3' class='text-center'>周三</th>
                                        </tr>
                                        <tr>
                                            <th class='text-center'>序号</th>
                                            <th class='text-center'>姓名</th>
                                            <th class='text-center'>学号</th>
                                        </tr>
                                    </thead>

                                    <tbody id='zhousanjimingbiao'>");
                                        $conn_temp = new STOS_MySQL_data();
                                        if($a = $conn_temp->search('缺勤人员名单',false,array('日期'=>$all_days[2],'提交者'=>$memberID),false)) {
                                            if($t = $a->fetch_assoc()['缺勤名单']) {
                                                $t = json_decode($t,true);
                                                $count = 1;
                                                foreach($t as $key=>$value){
                                                    echo("<tr>");
                                                    echo("<td class='text-center'>{$count}</td>");
                                                    echo("<td class='text-center'>{$value}</td>");
                                                    echo("<td class='text-center'>{$key}</td>");
                                                    echo("</tr>");
                                                    $count++;
                                                }
                                            }
                                        }
                    echo("
                                    </tbody>
                                </table>
                            </div>
                            <div class='col-sm-6 table-responsive'>
                                <table class='table'>
                                    <thead>
                                        <tr>
                                            <th colspan='3' class='text-center'>周四</th>
                                        </tr>
                                        <tr>
                                            <th class='text-center'>序号</th>
                                            <th class='text-center'>姓名</th>
                                            <th class='text-center'>学号</th>
                                        </tr>
                                    </thead>


                                    <tbody id='zhousijimingbiao'>");
                                        if($a = $conn_temp->search('缺勤人员名单',false,array('日期'=>$all_days[3],'提交者'=>$memberID))) {
                                            if($t = $a->fetch_assoc()['缺勤名单']) {
                                                $t = json_decode($t,true);
                                                $count = 1;
                                                foreach($t as $key=>$value){
                                                    echo("<tr>");
                                                    echo("<td class='text-center'>{$count}</td>");
                                                    echo("<td class='text-center'>{$value}</td>");
                                                    echo("<td class='text-center'>{$key}</td>");
                                                    echo("</tr>");
                                                    $count++;
                                                }
                                            }
                                        }
                        echo("
                                    </tbody>
                                </table>
                            </div>
                        </div>");
						echo("</div>");
						
						echo("<div class='tab-pane fade' id='queqinbiao567{$memberID}' role='tabpanel' aria-labelledby='queqinbiao-tab567{$memberID}'>");
                        echo("
                        <div class='row'>
                            <div class='col-sm-4 table-responsive'>
                                <table class='table'>
                                    <thead>
                                        <tr>
                                            <th colspan='3' class='text-center'>周五</th>
                                        </tr>
                                        <tr>
                                            <th class='text-center'>序号</th>
                                            <th class='text-center'>姓名</th>
                                            <th class='text-center'>学号</th>
                                        </tr>
                                    </thead>

                                    <tbody id='zhouwujimingbiao'>");
                                        $conn_temp = new STOS_MySQL_data();
                                        if($a = $conn_temp->search('缺勤人员名单',false,array('日期'=>$all_days[4],'提交者'=>$memberID),false)) {
                                            if($t = $a->fetch_assoc()['缺勤名单']) {
                                                $t = json_decode($t,true);
                                                $count = 1;
                                                foreach($t as $key=>$value){
                                                    echo("<tr>");
                                                    echo("<td class='text-center'>{$count}</td>");
                                                    echo("<td class='text-center'>{$value}</td>");
                                                    echo("<td class='text-center'>{$key}</td>");
                                                    echo("</tr>");
                                                    $count++;
                                                }
                                            }
                                        }
                    echo("
                                    </tbody>
                                </table>
                            </div>
                            <div class='col-sm-4 table-responsive'>
                                <table class='table'>
                                    <thead>
                                        <tr>
                                            <th colspan='3' class='text-center'>周六</th>
                                        </tr>
                                        <tr>
                                            <th class='text-center'>序号</th>
                                            <th class='text-center'>姓名</th>
                                            <th class='text-center'>学号</th>
                                        </tr>
                                    </thead>


                                    <tbody id='zhouliujimingbiao'>");
                                        if($a = $conn_temp->search('缺勤人员名单',false,array('日期'=>$all_days[5],'提交者'=>$memberID))) {
                                            if($t = $a->fetch_assoc()['缺勤名单']) {
                                                $t = json_decode($t,true);
                                                $count = 1;
                                                foreach($t as $key=>$value){
                                                    echo("<tr>");
                                                    echo("<td class='text-center'>{$count}</td>");
                                                    echo("<td class='text-center'>{$value}</td>");
                                                    echo("<td class='text-center'>{$key}</td>");
                                                    echo("</tr>");
                                                    $count++;
                                                }
                                            }
                                        }
                    echo("
                                    </tbody>
                                </table>
                            </div>
                            <div class='col-sm-4 table-responsive'>
                                <table class='table'>
                                    <thead>
                                        <tr>
                                            <th colspan='3' class='text-center'>周日</th>
                                        </tr>
                                        <tr>
                                            <th class='text-center'>序号</th>
                                            <th class='text-center'>姓名</th>
                                            <th class='text-center'>学号</th>
                                        </tr>
                                    </thead>


                                    <tbody id='zhourijimingbiao'>");
                                        if($a = $conn_temp->search('缺勤人员名单',false,array('日期'=>$all_days[6],'提交者'=>$memberID))) {
                                            if($t = $a->fetch_assoc()['缺勤名单']) {
                                                $t = json_decode($t,true);
                                                $count = 1;
                                                foreach($t as $key=>$value){
                                                    echo("<tr>");
                                                    echo("<td class='text-center'>{$count}</td>");
                                                    echo("<td class='text-center'>{$value}</td>");
                                                    echo("<td class='text-center'>{$key}</td>");
                                                    echo("</tr>");
                                                    $count++;
                                                }
                                            }
                                        }

                        echo("
                                    </tbody>
                                </table>
                            </div>
                        </div>");

						echo("</div>");
						
						echo("</div>");
						
						echo("
							</div>
                        </div>
                    </div>
                </div>");
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
            xmlhttp.open("GET","/ajax/personal/group_leader/member_work.php?day="+day+"&memberID="+memberID,true);
            xmlhttp.send();
        }
    </script>
    <script>
        var week = new Date().getDay();
        var flag = 0;
        var innerstring='<div class="alert alert-danger alert-dismissible" ><button type="button" class="close" data-dismiss="alert">&times;</button>以下组员今天没有录入查早信息：';
        if(week==0){
            week = 6;
        }
        else{
            week = week -1;
        }
        var a = document.getElementsByClassName('card-block');
        for(var i =0;i<a.length;i++){
            var b =a[i].getElementsByTagName('tbody')[0];
            if (b.children[week].children.length<3){
                innerstring = innerstring+a[i].getElementsByTagName('h4')[0].innerHTML+' ';
                flag=1;
            }
        }
        innerstring = innerstring+"</div>";
        if (flag==1){
            document.getElementById("txtHint").innerHTML=innerstring;
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
