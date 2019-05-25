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
    <title>个人中心</title>
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
	<style type="text/css">
		.get_red_pic
		{
			opacity:0.3;
			filter:alpha(opacity=30); /* 针对 IE8 以及更早的版本 */
		}
	</style>
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
                        <h3 class="text-themecolor">个人中心</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)"><?php echo $person->xinming; ?></a></li>
                            <li class="breadcrumb-item active">个人中心</li>
                        </ol>
                    </div>
                    <!-- <div class="col-md-7 col-4 align-self-center">
                        <a href="#" class="btn waves-effect waves-light btn-danger pull-right hidden-sm-down"> Upgrade to Pro</a>
                    </div> -->
                </div>

				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-block">
								<h2 class="card-title">通知中心</h2>
								<h4 class="card-subtitle">偶尔用一用 (●'◡'●)</h4>
								<h4 class="card-text">1.部分学院缺勤人数较多，可以不录入，比如格拉斯哥学院，已经联系学院书记。如果缺勤人数大于教室人数的一半，就不用录了，直接备注一下缺勤人数较多即可。</h4>
								<h4 class="card-text">2.如果没有班委可以确定缺勤学生，请直接让班级同学提供，一个班的同学，而且座位也是按学号顺序，如果还是不知道，那就直接手机拍下来教室人员情况，第二天再写名单。</h4>
								<h4 class="card-text">3.前几天搜了一下，貌似是IOS系统比较旧的话，有些功能不兼容，另外因为还在和学校交涉，所以现在还是http协议，有些浏览器会屏蔽不安全链接，可能也会对功能造成影响。建议升级系统或者尝试不同设备。</h4>
							</div>
						</div>
					</div>
				</div>
				<?php
				$t=$person->work_info();
				if($t["权限"]==3 or ($t["权限"]==2 and strstr($t["所属组"],"现场组"))){
					echo("<div class='row'>");
					echo("<div class='col-md-6'><div class='card'><div class='card-block'>");
					echo("<h2 class='card-title' style='text-align:center'>早自习检查教室安排</h2><button onClick='close_(1)'>关闭</button><a href='#current_group1'><button>跳转至本组</button></a>");
					echo("<h4 class='card-subtitle' style='text-align:center'>第十二周2019-05-13</h4>");
					echo("<div class='table-responsive'>");
					echo("<table class='table'>");
					echo("<thead><tr><th>姓名</th><th>教室</th><th>学院</th><th>应到人数</th><th>组号</th></tr></thead><tbody id='body1'>");
					
					$connection_t = new STOS_MySQL_data();
					$results = $connection_t->execute_query("SELECT * FROM `查早排班` WHERE `周起始日期` = '2019-05-20';");
					while($result = $results->fetch_assoc()) {
						$result['所属组'] = $connection->search("成员岗位",array("所属组"),array("学号"=>$result["查早组员"]),false)->fetch_assoc()['所属组'];
						echo("<tr>");
						echo("<td>{$result['姓名']}</td>
						<td>{$result['教学楼']}{$result['区号']}{$result['教室编号']}</td>
						<td>{$result['学院']}</td>
						<td>{$result['应到人数']}</td>
						<td>{$result['所属组']}</td>");
						echo("</tr>");
					}
					
					echo("</tbody></table></div></div></div></div>");
					echo("</div>");
				}
				?>
				<div class="row" <?php if($t["权限"]==1 and strstr($t["所属组"],"现场组")) echo("");else echo("hidden='true'"); ?>>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-block">
                                <h2 class="card-title" style="text-align:center">早自习检查教室安排</h2>
								<h4 class="card-subtitle" style="text-align:center">
									检查教室每周更换，不受放假调课等影响<br/>每周教室安排为随机生成
								</h4>
								<div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>周数</th>
                                                <th>起始日期</th>
                                                <th>教室</th>
												<th>
													<?php
													require("../frame/empty_time.php");
													echo(get_color_pic("green"));
													echo("本周 ");
													echo(get_color_pic("yellow"));
													echo("下一周");
													?>
												</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
											$temp_connection = new STOS_MySQL_data();
											$sql = "SELECT * FROM `查早排班` WHERE `查早组员`='{$person->xuehao}' ORDER BY `周起始日期` ASC;";
											if($temp = $temp_connection->execute_query($sql)) {
												while($t = $temp->fetch_assoc()) {
													echo("<tr>");
													echo("<td>{$t['周数']}</td>");
													echo("<td>{$t['周起始日期']}</td>");
													$x = join("",array($t["教学楼"],$t["区号"],$t["教室编号"]));
													echo("<td>{$x}</td>");
													if(getWeekRange(time(),1)[0]==$t['周起始日期']) {
														echo("<td>".get_color_pic("green")."</td>");
													}
													elseif(date("Y-m-d",strtotime(getWeekRange(time(),1)[0]."+1week"))==$t['周起始日期']) {
														echo("<td>".get_color_pic("yellow")."</td>");
													}
													elseif((date("Y-m-d",strtotime(getWeekRange(time(),1)[0]."+1week"))<=>$t['周起始日期'])===-1) {
														echo("<td>".get_color_pic("red")."</td>");
													}
													echo("</tr>");
												}
											}
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php // 一下均为临时用的php，第二周起要加入日期******************************************
					$courses_array = array(); // 查课教室数组，二维
					$sql = "SELECT * FROM `查课排班` WHERE `查课组员`='{$person->xuehao}' AND 日期 IN ('".join("','",getWeekRange(time(),1)[2])."') ORDER BY `教学楼`,`区号`,`教室编号` ASC;";
					if($results = $temp_connection->execute_query($sql)) {
						while($course = $results->fetch_assoc()) {
							array_push($courses_array,$course);
						}
					}
					?>
					<div class="col-md-6">
						<div class="card">
							<div class="card-block">
								<h2 class="card-title" style="text-align:center">查课教室安排</h2>
								<h4 class="card-subtitle" style="text-align:center">
									第十二周，<?php echo($courses_array[0]["日期"].'<br/>'.substr($courses_array[0]["时段与上课周"],0,3)); ?>节，表编号：<?php echo($courses_array[0]["编号"]); ?>
								</h4>
								<table class="table">
									<thead>
										<tr>
											<th>时段与上课周</th>
											<th>教学楼</th>
											<th>应到人数</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach($courses_array as $i) {
											echo("<tr>");
											echo("<td>{$i['时段与上课周']}</td>");
											echo("<td>{$i['教学楼']}{$i['区号']}{$i['教室编号']}</td>");
											echo("<td>{$i['应到人数']}</td>");
											echo("</tr>");
										}//以上为临时php***************************************************************
										?>
									</tbody>
								</table>
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
    <!-- Custom JavaScript -->
    <!-- ============================================================== -->
	<script>
		var bd1 = document.getElementById("body1");
		if(bd1!=null) {
			var bd1_c = bd1.childElementCount;
			var bd1_id=true;
			for(var i=0;i<bd1_c;i++) {
				if(bd1.children[i].children[4].innerHTML=="<?php echo $person->work_info()["所属组"]?>") {
					bd1.children[i].setAttribute("style","font-weight: bold; color:blue");
					if(bd1_id)bd1.children[i].setAttribute("id","current_group1");
					bd1_id = false;
				}
			}
		}
		var bd2 = document.getElementById("body2");
		if(bd2!=null) {
			var bd2_c = bd2.childElementCount;
			var bd2_id=true;
			for(var i=0;i<bd2_c;i++) {
				if(bd2.children[i].children[4].innerHTML=="<?php echo $person->work_info()["所属组"]?>") {
					bd2.children[i].setAttribute("style","font-weight: bold; color:blue");
					if(bd2_id)bd2.children[i].setAttribute("id","current_group2");
					bd2_id = false;
				}
			}
		}
		
		function close_(x) {
			if(x==1) {
				var re = document.getElementById("body1").parentNode.parentNode.parentNode.parentNode.parentNode;
				re.parentNode.removeChild(re);
			}
			else if(x==2) {
				var re = document.getElementById("body2").parentNode.parentNode.parentNode.parentNode.parentNode;
				re.parentNode.removeChild(re);
			}
		}
	</script>
	<!-- ============================================================== -->
    <!-- End Custom JavaScript -->
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
