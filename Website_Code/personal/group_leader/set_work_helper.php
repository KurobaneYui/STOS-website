<?php
session_start();
require("../../frame/Person_Class_frame.php");

if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
    $connection_info = new STOS_MySQL(); // 建立数据库连接
	$connection_data = new STOS_MySQL_data();
    $person = new person_all_info( $_SESSION[ "username" ] ); // 获取个人信息
	if($person->work_info()["权限"]!=2) header( 'refresh:0; url=../../log/logout.php' ); // 如果不是组长，强制登出
}
else { // 没有登陆
    header( 'refresh:0; url=../../log/login.php' ); // 返回登陆页面
}
?>

<?php
if(isset($_POST["代查申请"]) and $_POST["代查申请"]=="yes") {
	$query = array
			(
			"申请类型" => $_POST["申请类型"],
			"申请者学号" => $_POST["申请者学号"],
			"代查日期" => $_POST["代查日期"],
			"申请者姓名" => $connection_info->search("成员信息",array("姓名"),array("学号"=>$_POST["申请者学号"]),false)->fetch_assoc()["姓名"],
			"申请组" => $connection_info->search("成员岗位",array("所属组"),array("学号"=>$_POST["申请者学号"]),false)->fetch_assoc()["所属组"]
			);
	
	if($connection_data->search("代查安排",false,$query,false)->fetch_assoc()) {
		false; // 不用导入
	}
	else {
		$connection_data->insert("代查安排",$query);
	}
}
if(isset($_POST["接受申请"]) and $_POST["接受申请"]=="yes") {
	$conditions = array
			(
			"申请类型" => $_POST["申请类型"],
			"申请者姓名" => $_POST["申请者姓名"],
			"代查日期" => $_POST["代查日期"],
			"申请组" => $_POST["申请组"],
			"申请者学号" => $connection_info->search("成员信息",array("学号"),array("姓名"=>$_POST["申请者姓名"]),false)->fetch_assoc()["学号"]
			);
	$query = array
			(
			"接受者学号" => $_POST["接受者学号"],
			"接受者姓名" => $connection_info->search("成员信息",array("姓名"),array("学号"=>$_POST["接受者学号"]),false)->fetch_assoc()["姓名"],
			"接受组" => $connection_info->search("成员岗位",array("所属组"),array("学号"=>$_POST["接受者学号"]),false)->fetch_assoc()["所属组"]
			);
	if($connection_data->search("代查安排",false,$conditions,false)->fetch_assoc()["接受组"]) {
		false; // 不可导入
	}
	else {
		$connection_data->update("代查安排",$query,$conditions,false);
	}
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
    <title>代查安排</title>
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
                            <h3 class="text-themecolor m-b-0 m-t-0">代查表</h3>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)"><?php echo $person->xinming; ?></a></li>
                                <li class="breadcrumb-item active">代查表</li>
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
                                <h2 class="card-title">代查系统说明</h2>
								<h4 class="card-subtitle">周三当天开始就可以用了。咕咕了这么久，总算来了</h4>
                                <h4 class="card-text">代查系统的变动完全交由组长们处理了，组员可以在群里寻找代查，但是为了保证你们知到组员的代查情况，统一由组长进行代查的申请和接受。</h4>
								<h4 class="card-text">组员和你们说了是哪个组的哪个组员，不论申请还是接受，你们都要及时在系统里提交申请或者接受申请</h4>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="row">
					<div class="col-md-5">
						<div class="card">
							<div class="card-block">
								<form action="#" method="post">
                                    <div class="row">
                                        <div class="col-md-5 mb-3">
                                            <label for="leixin">申请类型</label>
                                            <select class="custom-select" id="leixin" name="申请类型" required>
												<option value="早自习">早自习</option>
												<option value="查课">查课</option>
                                            </select>
                                        </div>
										<div class="col-md-7 mb-3">
                                            <label for="xingming">组员姓名</label>
                                            <select class="custom-select" id="xingming" name="申请者学号" required>
												<?php
												$sql = "SELECT `学号` FROM 成员岗位 WHERE `所属组`='{$person->work_info()['管理组'][0]}';";
												$membersID = $connection_info->execute_query($sql);
												while($memberID = $membersID->fetch_assoc()['学号']) {
													$sql = "SELECT `姓名` FROM 成员信息 WHERE `学号`={$memberID};";
													$xingming = $connection_info->execute_query($sql)->fetch_assoc()["姓名"];
													echo("<option value='{$memberID}'>{$xingming}</option>");
												}
												?>
                                            </select>
                                        </div>
									</div>
									<div class="row">
										<div class="col-md-6 mb-3">
                                            <label for="riqi">代查日期</label>
                                            <input type="date" name="代查日期" id="riqi" class="form-control" required/>
                                        </div>
                                    </div>
									<div class="row">
                                        <div class="col-12 mb-3">
											<button class="btn btn-success" type="submit" name="代查申请" value="yes">提交申请</button>
										</div>
									</div>
                                </form>
							</div>
						</div>
					</div>
					<div class="col-md-7">
						<div class="card">
							<div class="card-block">
								<div class="table-responsive">
									<table class="table">
										<thead>
											<tr>
												<th>申请类型</th>
												<th>代查日期</th>
												<th>申请组</th>
												<th>申请者姓名</th>
												<th>接受组</th>
												<th>接受者姓名</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$sql = "SELECT * FROM `代查安排` WHERE `代查日期` BETWEEN '2019-10-08' and '2019-10-30' ORDER BY `代查日期` DESC;";
											$daicha_paibans = $connection_data->execute_query($sql);
											
											while($daicha_single = $daicha_paibans->fetch_assoc()) {
												echo("<tr>");
												
												echo("<td>{$daicha_single['申请类型']}</td>");
												echo("<td>{$daicha_single['代查日期']}</td>");
												echo("<td>{$daicha_single['申请组']}</td>");
												echo("<td>{$daicha_single['申请者姓名']}</td>");
												if($daicha_single['接受组']) {
													echo("<td>{$daicha_single['接受组']}</td>");
													echo("<td>{$daicha_single['接受者姓名']}</td>");
												}
												else {
													echo("<td colspan='2'>");
													echo("<form action='#' method='post'>");
													
													echo("<input type='hidden' name='申请类型' value='{$daicha_single['申请类型']}' required/>");
													echo("<input type='hidden' name='代查日期' value='{$daicha_single['代查日期']}' required/>");
													echo("<input type='hidden' name='申请组' value='{$daicha_single['申请组']}' required/>");
													echo("<input type='hidden' name='申请者姓名' value='{$daicha_single['申请者姓名']}' required/>");
													
													echo("<select class='custom-select' name='接受者学号' required>");
													$sql = "SELECT `学号` FROM 成员岗位 WHERE `所属组`='{$person->work_info()['管理组'][0]}';";
													$membersID = $connection_info->execute_query($sql);
													while($memberID = $membersID->fetch_assoc()['学号']) {
														$sql = "SELECT `姓名` FROM 成员信息 WHERE `学号`={$memberID};";
														$xingming = $connection_info->execute_query($sql)->fetch_assoc()["姓名"];
														echo("<option value='{$memberID}'>{$xingming}</option>");
													}
													echo("</select>");
													
													echo("<button class='btn btn-success' type='submit' name='接受申请' value='yes'>接受申请</button>");
													
													echo("</form>");
													echo("</td>");
												}
												
												echo("</tr>");
											}
											?>
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
    <script src="../../assets/plugins/jquery/jquery.min.js"></script>
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
