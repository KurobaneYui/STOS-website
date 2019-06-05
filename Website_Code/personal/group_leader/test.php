<?php
session_start();
require("../../frame/Person_Class_frame.php");

if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
    $connection = new STOS_MySQL(); // 建立数据库连接
    $person = new person_all_info( $_SESSION[ "username" ] ); // 获取个人信息
	require("../../frame/empty_time.php");
	if($person->work_info()["权限"]!=2) header( 'refresh:0; url=../../log/logout.php' ); // 如果不是组长，强制登出
}
else { // 没有登陆
    header( 'refresh:0; url=../../log/login.php' ); // 返回登陆页面
}
?>
<?php
if(isset($_POST["重置密码"])) {
    $te = new person_all_info($_POST["重置密码"]);
    $te->mima = $te->xuehao;
    $te->upload_mima();
    $te->__destruct();
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
    <title>组员信息</title>
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
                            <h3 class="text-themecolor m-b-0 m-t-0">组员信息</h3>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)"><?php echo $person->xinming; ?></a></li>
                                <li class="breadcrumb-item active">组员信息</li>
                            </ol>
                        </div>
                        <!-- <div class="col-md-7 col-4 align-self-center">
                            <a href="#" class="btn waves-effect waves-light btn-danger pull-right hidden-sm-down"> Upgrade to Pro</a>
                        </div> -->
                </div>
				

				<div class='row'>
					<div class='col-12'>
						<div class='card'>
							<div class='card-block'>
								<h4 class='card-title'>有组员</h4>
<ul class='nav nav-tabs' id='123-OE-tab' role='tablist'>
  <li class='nav-item'>
    <a class='nav-link active' id='home-tab' data-toggle='tab' href='#123O' role='tab' aria-controls='123O' aria-selected='true'>Home</a>
  </li>
  <li class='nav-item'>
    <a class='nav-link' id='profile-tab' data-toggle='tab' href='#123E' role='tab' aria-controls='123E' aria-selected='false'>Profile</a>
  </li>
</ul>
<div class='tab-content' id='123-OE-content'>
  <div class='tab-pane fade show active' id='123O' role='tabpanel' aria-labelledby='home-tab'>1</div>
  <div class='tab-pane fade' id='123E' role='tabpanel' aria-labelledby='profile-tab'>...</div>
</div>
							</div>
						</div>
					</div>
				</div>
				<div class='row'>
					<div class='col-12'>
						<div class='card'>
							<div class='card-block'>
								<h4 class='card-title'>有组员</h4>
								<ul class='nav nav-tabs' id='123OETab' role='tablist'>
									<li class='nav-item'>
										<a href='#123O' class='nav-link active' id='123Otab' data-toggle='tab' role='tab' aria-controls='123O' aria-selected='true'>单周</a>
									</li>
									<li class='nav-item'>
										<a href='#123E' class='nav-link' id='123Etab' data-toggle='tab' role='tab' aria-controls='123E' aria-selected='false'>双周</a>
									</li>
								</ul>
								<div class='tab-content' id='123OETabContent'>
									<div class='tab-pane fade show active' id='123O' role='tabpanel' aria-labelledby='123Otab'>
										<div class='table-responsive'>
											<table class='table'>
												<thead>
													<tr>
														<th>周一</th>
														<th>周二</th>
														<th>周三</th>
														<th>周四</th>
														<th>周五</th>
													</tr>
												</thead>
												<tbody>
													
												<tr>
													<td>"."</td>
													<td>"."</td>
													<td>"."</td>
													<td>"."</td>
													<td>"."</td>
												</tr>

												</tbody>
											</table>
										</div>
									</div>
									<div class='tab-pane fade' id='123E' role='tabpanel' aria-labelledby='123Etab'>
										<div class='table-responsive'>
											<table class='table'>
												<thead>
													<tr>
														<th>周一</th>
														<th>周二</th>
														<th>周三</th>
														<th>周四</th>
														<th>周五</th>
													</tr>
												</thead>
												<tbody>

												<tr>
													<td>45</td>
													<td>"456."</td>
													<td>"."45</td>
													<td>".45"</td>
													<td>4dgfs</td>
												</tr>

												</tbody>
											</table>
										</div>
									</div>
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