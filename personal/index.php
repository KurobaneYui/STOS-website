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
								<h2 class="card-title">岗位说明</h2>
								<h4 class="card-subtitle">打字好累，暂时不写</h4>
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
					echo("<h4 class='card-subtitle' style='text-align:center'>第十一周2019-05-06</h4>");
					echo("<div class='table-responsive'>");
					echo("<table class='table'>");
					echo("<thead><tr><th>姓名</th><th>教学楼</th><th>区号</th><th>教室编号</th><th>学院</th><th>所属组</th></tr></thead><tbody id='body1'><tr><td>原琪彬</td><td>品学楼</td><td>A</td><td>101</td><td>信通</td><td>现场组三组</td></tr><tr><td>左迎香</td><td>品学楼</td><td>A</td><td>102</td><td>信通</td><td>现场组三组</td></tr><tr><td>詹鹏</td><td>品学楼</td><td>A</td><td>103</td><td>信通</td><td>现场组三组</td></tr><tr><td>王烨</td><td>品学楼</td><td>A</td><td>104</td><td>信通</td><td>现场组三组</td></tr><tr><td>马旭涛</td><td>品学楼</td><td>A</td><td>105</td><td>信通</td><td>现场组三组</td></tr><tr><td>刘宇晴</td><td>品学楼</td><td>A</td><td>106</td><td>信通</td><td>现场组三组</td></tr><tr><td>高浩风</td><td>品学楼</td><td>A</td><td>107</td><td>信通</td><td>现场组三组</td></tr><tr><td>钟秋圆</td><td>品学楼</td><td>A</td><td>108</td><td>信通</td><td>现场组三组</td></tr><tr><td>任相璇</td><td>品学楼</td><td>A</td><td>109</td><td>电子</td><td>现场组三组</td></tr><tr><td>张子健</td><td>品学楼</td><td>A</td><td>110</td><td>电子</td><td>现场组一组</td></tr><tr><td>安欣威</td><td>品学楼</td><td>A</td><td>201</td><td>电子</td><td>现场组一组</td></tr><tr><td>马敖</td><td>品学楼</td><td>A</td><td>202</td><td>电子</td><td>现场组一组</td></tr><tr><td>温冬阳</td><td>品学楼</td><td>A</td><td>203</td><td>电子</td><td>现场组一组</td></tr><tr><td>马义芬</td><td>品学楼</td><td>A</td><td>204</td><td>电子</td><td>现场组一组</td></tr><tr><td>陈鹏</td><td>品学楼</td><td>A</td><td>205</td><td>电子</td><td>现场组一组</td></tr><tr><td>孙雪童</td><td>品学楼</td><td>A</td><td>206</td><td>电子</td><td>现场组一组</td></tr><tr><td>张婉棱</td><td>品学楼</td><td>A</td><td>207</td><td>电子</td><td>现场组一组</td></tr><tr><td>贾佳</td><td>品学楼</td><td>A</td><td>208</td><td>电子</td><td>现场组一组</td></tr><tr><td>曾百惠</td><td>品学楼</td><td>A</td><td>209</td><td>空天</td><td>现场组一组</td></tr><tr><td>孙翔宇</td><td>品学楼</td><td>A</td><td>210</td><td>机电</td><td>现场组一组</td></tr><tr><td>马宇欣</td><td>品学楼</td><td>A</td><td>211</td><td>机电</td><td>现场组一组</td></tr><tr><td>陈志勇</td><td>品学楼</td><td>A</td><td>212</td><td>机电</td><td>现场组一组</td></tr><tr><td>陈欢</td><td>品学楼</td><td>A</td><td>213</td><td>机电</td><td>现场组二组</td></tr><tr><td>孙彦洁</td><td>品学楼</td><td>A</td><td>301</td><td>机电</td><td>现场组二组</td></tr><tr><td>白毅</td><td>品学楼</td><td>A</td><td>302</td><td>光电</td><td>现场组二组</td></tr><tr><td>李兵</td><td>品学楼</td><td>A</td><td>303</td><td>光电</td><td>现场组二组</td></tr><tr><td>苗思雨</td><td>品学楼</td><td>A</td><td>304</td><td>光电</td><td>现场组二组</td></tr><tr><td>韩雪峰</td><td>品学楼</td><td>A</td><td>305</td><td>光电</td><td>现场组二组</td></tr><tr><td>张玮杰</td><td>品学楼</td><td>A</td><td>306</td><td>光电</td><td>现场组二组</td></tr><tr><td>杨奇</td><td>品学楼</td><td>A</td><td>307</td><td>自动化</td><td>现场组二组</td></tr><tr><td>罗瑞智</td><td>品学楼</td><td>A</td><td>308</td><td>自动化</td><td>现场组二组</td></tr><tr><td>郝千禧</td><td>品学楼</td><td>A</td><td>309</td><td>自动化</td><td>现场组二组</td></tr><tr><td>朱雨喆</td><td>品学楼</td><td>A</td><td>310</td><td>自动化</td><td>现场组二组</td></tr><tr><td>王煚慧</td><td>品学楼</td><td>A</td><td>311</td><td>自动化</td><td>现场组二组</td></tr><tr><td>刘晗</td><td>品学楼</td><td>A</td><td>312</td><td>数学</td><td>现场组二组</td></tr><tr><td>黄理杰</td><td>品学楼</td><td>A</td><td>313</td><td>数学</td><td>现场组六组</td></tr><tr><td>刘斌</td><td>品学楼</td><td>A</td><td>401</td><td>资环</td><td>现场组六组</td></tr><tr><td>玉苏普·阿伍提</td><td>品学楼</td><td>A</td><td>402</td><td>计算机</td><td>现场组六组</td></tr><tr><td>张季聪</td><td>品学楼</td><td>A</td><td>403</td><td>计算机</td><td>现场组六组</td></tr><tr><td>萨恒汗·海拉提</td><td>品学楼</td><td>A</td><td>404</td><td>计算机</td><td>现场组六组</td></tr><tr><td>哈山·热合曼</td><td>品学楼</td><td>A</td><td>405</td><td>计算机</td><td>现场组六组</td></tr><tr><td>张一鸣</td><td>品学楼</td><td>A</td><td>407</td><td>计算机</td><td>现场组六组</td></tr><tr><td>吕炫</td><td>品学楼</td><td>A</td><td>408</td><td>计算机</td><td>现场组六组</td></tr><tr><td>薛小宇</td><td>品学楼</td><td>A</td><td>409</td><td>物理</td><td>现场组六组</td></tr><tr><td>麦麦提艾力·阿卜杜热伊木</td><td>品学楼</td><td>A</td><td>410</td><td>物理</td><td>现场组六组</td></tr><tr><td>黄肖曼</td><td>品学楼</td><td>A</td><td>411</td><td>物理</td><td>现场组六组</td></tr><tr><td>尹思敏</td><td>品学楼</td><td>A</td><td>413</td><td>材料</td><td>现场组六组</td></tr><tr><td>蔡冰洋</td><td>品学楼</td><td>B</td><td>101</td><td>信软</td><td>现场组三组</td></tr><tr><td>焦锐</td><td>品学楼</td><td>B</td><td>102</td><td>信软</td><td>现场组三组</td></tr><tr><td>斯迪克·阿卜杜哈帕尔</td><td>品学楼</td><td>B</td><td>103</td><td>信软</td><td>现场组三组</td></tr><tr><td>张启明</td><td>品学楼</td><td>B</td><td>104</td><td>信软</td><td>现场组三组</td></tr><tr><td>侯雅茹</td><td>品学楼</td><td>B</td><td>105</td><td>信软</td><td>现场组五组</td></tr><tr><td>梁彩玉</td><td>品学楼</td><td>B</td><td>106</td><td>信软</td><td>现场组五组</td></tr><tr><td>袁中野</td><td>品学楼</td><td>B</td><td>107</td><td>信软</td><td>现场组五组</td></tr><tr><td>覃琳玲</td><td>品学楼</td><td>B</td><td>108</td><td>信软</td><td>现场组五组</td></tr><tr><td>奥布力艾散·艾散</td><td>品学楼</td><td>B</td><td>109</td><td>信软</td><td>现场组五组</td></tr><tr><td>刘国蓉</td><td>品学楼</td><td>B</td><td>110</td><td>信软</td><td>现场组五组</td></tr><tr><td>陈正雨</td><td>品学楼</td><td>B</td><td>201</td><td>格院</td><td>现场组五组</td></tr><tr><td>韩超</td><td>品学楼</td><td>B</td><td>202</td><td>格院</td><td>现场组五组</td></tr><tr><td>葛亚蒙</td><td>品学楼</td><td>B</td><td>203</td><td>格院</td><td>现场组五组</td></tr><tr><td>陈丽萍</td><td>品学楼</td><td>B</td><td>204</td><td>格院</td><td>现场组五组</td></tr><tr><td>刘伟龙</td><td>品学楼</td><td>B</td><td>205</td><td>格院</td><td>现场组五组</td></tr><tr><td>丘小欢</td><td>品学楼</td><td>B</td><td>206</td><td>格院</td><td>现场组五组</td></tr><tr><td>张安</td><td>品学楼</td><td>B</td><td>207</td><td>格院</td><td>现场组四组</td></tr><tr><td>张钰涵</td><td>品学楼</td><td>B</td><td>208</td><td>外国语</td><td>现场组四组</td></tr><tr><td>唐振岚</td><td>品学楼</td><td>B</td><td>209</td><td>外国语</td><td>现场组四组</td></tr><tr><td>王雨轩</td><td>品学楼</td><td>B</td><td>210</td><td>外国语</td><td>现场组四组</td></tr><tr><td>闫容赫</td><td>品学楼</td><td>B</td><td>211</td><td>生命</td><td>现场组四组</td></tr><tr><td>张智康</td><td>品学楼</td><td>B</td><td>212</td><td>生命</td><td>现场组四组</td></tr><tr><td>吴珩</td><td>品学楼</td><td>B</td><td>302</td><td>英才</td><td>现场组四组</td></tr><tr><td>张晨旭</td><td>品学楼</td><td>B</td><td>303</td><td>英才</td><td>现场组四组</td></tr><tr><td>刘婵</td><td>品学楼</td><td>B</td><td>308</td><td>医学院</td><td>现场组四组</td></tr><tr><td>杨鑫</td><td>品学楼</td><td>B</td><td>309</td><td>经管</td><td>现场组四组</td></tr><tr><td>顾赟</td><td>品学楼</td><td>B</td><td>311</td><td>公管</td><td>现场组四组</td></tr><tr><td>刘殊璇</td><td>品学楼</td><td>B</td><td>312</td><td>公管</td><td>现场组四组</td></tr><tr><td>李晓文</td><td>品学楼</td><td>B</td><td>313</td><td>公管</td><td>现场组四组</td></tr></tbody>");
					echo("</table></div></div></div></div>");
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
											$sql = "SELECT * FROM `早自习排班` WHERE `查早组员`='{$person->xuehao}' ORDER BY `周起始日期` ASC;";
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
					$sql = "SELECT * FROM `查课排班` WHERE `查课组员`='{$person->xuehao}';";
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
									第十一周，<?php echo($courses_array[0]["日期"].'<br/>'.substr($courses_array[0]["时段与上课周"],0,3)); ?>节，表编号：<?php echo($courses_array[0]["编号"]); ?>
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
		var bd1_c = bd1.childElementCount;
		//var bd2 = document.getElementById("body2");
		//var bd2_c = bd2.childElementCount;
		var bd1_id=true;var bd2_id=true;
		for(var i=0;i<bd1_c;i++) {
			if(bd1.children[i].children[5].innerHTML=="<?php echo $person->work_info()["所属组"]?>") {
				bd1.children[i].setAttribute("style","font-weight: bold; color:blue");
				if(bd1_id)bd1.children[i].setAttribute("id","current_group1");
				bd1_id = false;
			}
		}
		//for(var i=0;i<bd2_c;i++) {
		//	if(bd2.children[i].children[5].innerHTML=="<?php echo $person->work_info()["所属组"]?>") {
		//		bd2.children[i].setAttribute("style","font-weight: bold; color:blue");
		//		if(bd2_id)bd2.children[i].setAttribute("id","current_group2");
		//		bd2_id = false;
		//	}
		//}
		
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
