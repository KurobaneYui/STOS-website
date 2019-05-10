<?php
session_start();
require("../../frame/Person_Class_frame.php");

if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
    $connection = new STOS_MySQL(); // 建立数据库连接
    $person = new person_all_info( $_SESSION[ "username" ] ); // 获取个人信息
    $self_study_classroom = new self_study_data_set($person->xuehao, time()); // 获取查早教室信息
}
else { // 没有登陆但是cookie中存有登陆信息
    //检查cookie
    if ( isset( $_COOKIE[ 'username' ] ) ) {
        $_SESSION[ 'username' ] = $_COKIE[ 'username' ];
        $_SESSION[ 'islogin' ] = 1;

        $connection = new STOS_MySQL();
        $person = new person_all_info( $_SESSION[ "username" ] );
        $self_study_classroom = new self_study_data_set($person->xuehao, time()); // 获取查早教室信息
    }
    else
        header( 'refresh:0; url=../../log/login.php' ); // 返回登陆页面
}
?>
<?php
if(isset($_POST["早自习数据"]) and $_POST["早自习数据"]=="yes") {
    $data_array = array
	(
	    "第一次出勤"=>$_POST["第一次出勤"],
        "第二次出勤"=>$_POST["第二次出勤"],
	    "违纪人数"=>$_POST["违纪人数"],
	    "早退人数"=>$_POST["早退人数"],
	    "迟到人数"=>$_POST["迟到人数"],
		"请假人数"=>$_POST["请假人数"],
	    "备注"=>$_POST["备注"]
	);
    $self_study_classroom->change_classroom_data($_POST["日期"],$data_array);
}
?>
<?php
$connection_temp = new STOS_MySQL_data();
if(isset($_POST["记名表"]) and $_POST["记名表"]=="yes") {
	$data_json = array();
	if(isset($_POST["姓名1"]) and $_POST["姓名1"]!="" and $_POST["学号1"]!="") {
		for($i=1;$i<200;$i++){
			if(isset($_POST["姓名".$i]) and $_POST["姓名".$i]!="" and $_POST["学号".$i]!="") {
					$data_json[$_POST["学号".$i]] = $_POST["姓名".$i];
				}
		}
		if($a = $connection_temp->search("早自习排班",false,array("查早组员"=>$_SESSION['username'],"周起始日期"=>getWeekRange(time(), 1)[0]),false)->fetch_assoc()) {
			switch($_POST["日期"]) {
				case "周一":$queqinbiaoriqi=getWeekRange(time(), 1)[2][0];break;
				case "周二":$queqinbiaoriqi=getWeekRange(time(), 1)[2][1];break;
				case "周三":$queqinbiaoriqi=getWeekRange(time(), 1)[2][2];break;
				case "周四":$queqinbiaoriqi=getWeekRange(time(), 1)[2][3];break;
				case "周五":$queqinbiaoriqi=getWeekRange(time(), 1)[2][4];break;
				case "周六":$queqinbiaoriqi=getWeekRange(time(), 1)[2][5];break;
				case "周日":$queqinbiaoriqi=getWeekRange(time(), 1)[2][6];break;
			}
			$data_array = array(
				"日期"=>$queqinbiaoriqi,
				"教学楼"=>$a["教学楼"],
				"区号"=>$a["区号"],
				"教室编号"=>$a["教室编号"],
				"缺勤名单"=>json_encode($data_json, JSON_UNESCAPED_UNICODE),
				"提交者"=>$_SESSION[ 'username' ]
			);
			
			$conditions = array(
				"日期"=>$data_array["日期"],
				"教学楼"=>$data_array["教学楼"],
				"区号"=>$data_array["区号"],
				"教室编号"=>$data_array["教室编号"]
			);
			if($connection_temp->search("缺勤人员名单",false,$conditions,false)->fetch_assoc()){
				
				$connection_temp->update("缺勤人员名单",$data_array,$conditions,false);
			}
			else {
				$connection_temp->insert("缺勤人员名单",$data_array);
			}
		}
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
    <title>查早数据录入</title>
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

<body class="fix-header fix-sidebar card-no-border"  onload="auto_fill()">
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
                            <h3 class="text-themecolor m-b-0 m-t-0">查早信息录入</h3>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)"><?php echo $person->xinming; ?></a></li>
                                <li class="breadcrumb-item active">查早信息录入</li>
                            </ol>
                        </div>
                        <!-- <div class="col-md-7 col-4 align-self-center">
                            <a href="#" class="btn waves-effect waves-light btn-danger pull-right hidden-sm-down"> Upgrade to Pro</a>
                        </div> -->
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>注意!</strong> 请先提交查早数据，再提交缺勤数据。
                        </div>
                    </div>
                </div>
                <div class ="row">
                    <div class="col-lg-12">
                        <div class = "card">
                            <div class="card-block">
                                <form action="#" method="post">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="riqi">日期</label>
                                            <select class="custom-select" id="riqi" name="日期" required onchange="auto_fill()">
                                                <option value="周一" <?php if(date('w',time())==1)echo("selected"); ?>>周一</option>
                                                <option value="周二" <?php if(date('w',time())==2)echo("selected"); ?>>周二</option>
                                                <option value="周三" <?php if(date('w',time())==3)echo("selected"); ?>>周三</option>
                                                <option value="周四" <?php if(date('w',time())==4)echo("selected"); ?>>周四</option>
                                                <option value="周五" <?php if(date('w',time())==5)echo("selected"); ?>>周五</option>
                                                <option value="周六" <?php if(date('w',time())==6)echo("selected"); ?>>周六</option>
                                                <option value="周日" <?php if(date('w',time())==0)echo("selected"); ?>>周日</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="chidaorenshu">迟到人数</label>
                                            <input type="text" class="form-control" id="chidaorenshu" name="迟到人数" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="qinjiarenshu">请假人数</label>
                                            <input type="text" class="form-control" id="qinjiarenshu" name="请假人数" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="diyicichuqin">第一次出勤人数</label>
                                            <input type="text" class="form-control" id="diyicichuqin" name="第一次出勤" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="weijirenshu">违纪人数</label>
                                            <input type="text" class="form-control" id="weijirenshu" name="违纪人数" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="diercichuqin">第二次出勤人数</label>
                                            <input type="text" class="form-control" id="diercichuqin" name="第二次出勤" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="zaotuirenshu">早退人数</label>
                                            <input type="text" class="form-control" id="zaotuirenshu" name="早退人数" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                        <label for="beizhu">备注</label>
                                        <textarea class="form-control" id="beizhu" name="备注"></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
										<div class="col-sm-6 mb-3">
											<button class="btn btn-primary btn-block" data-toggle="modal" data-target="#myModal" type="button">
												缺勤人员名单
											</button>
										</div>
                                        <div class="col-sm-6 mb-3">
                                            <button class="btn btn-primary btn-block" type="submit" name="早自习数据" value="yes">提交修改</button>
                                        </div>
                                    </div>
                                </form>
                                <br/>
                            </div>
                        </div>
                    </div>
                </div>
				<!-- ============================================================== -->
				<!-- Modal  -->
				<!-- ============================================================== -->
				<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title" id="myModalLabel">
									<?php echo(join("",$self_study_classroom->get_classroomID())); ?>记名表
								</h4>
							</div>
							<div class="modal-body">
								<form id="jimingbiao" action="#" method="post">
									<div class="row">
										<div class="col-md-4 mb-3">
											<h4>日期：</h4><input type="hidden" name="日期" value=""/>
										</div>
									</div>
									<!--<div class="row">
										<div class="col-sm-2 mb-3">
											<h4>1#</h4>
											<button type="button" class="btn btn-primary" onClick="removerow(false,this)">
										</div>
										<div class="col-md-5 mb-3">
											<label for="xinming1">姓名</label>
											<input type="text" class="form-control" id="xinming1" name="姓名1" required>
										</div>
										<div class="col-md-5 mb-3">
											<label for="xeuhao1">学号</label>
											<input type="text" class="form-control" id="xeuhao1" name="学号1" required>
										</div>
									</div>-->
									<div id="add_remove_button" class="row">
										<div class="col-sm-1 mb-3">
											<button type="button" class="btn btn-primary" onClick="addrow()">添加新行</button>
										</div>
										<input type="hidden" value="yes" name="记名表"/>
									</div>
								</form>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
								<button type="button" class="btn btn-primary" onClick="jimingbiaotijiao()">提交更改</button>
							</div>
						</div>
					</div>
				</div>
				<!-- ============================================================== -->
				<!-- Modal End  -->
				<!-- ============================================================== -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-block">
                                <h2 class="card-title" style="text-align:center">查早教室</h2>
                                <h4 id="chazaojiaoshi" class="card-subtitle" style="text-align:center">
									<?php
									$result_ = $self_study_classroom->get_classroom_info_array("周一");
									echo(join("",$self_study_classroom->get_classroomID())." ".$result_["学院"]);
									?>
								</h4>
                                <div class="table-responsive">
                                    <table class="table">
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

                                        <tbody id="self_study_data_table">
                                            <?php
                                            $zhou = array("周一","周二","周三","周四","周五","周六","周日");
                                            foreach($zhou as $value) {
                                                echo("<tr><td>{$value}</td>");
                                                
                                                $result = $self_study_classroom->get_data_array($value);
                                                $result_ = $self_study_classroom->get_classroom_info_array($value);
                                                echo("<td>{$result_["应到人数"]}</td>");
                                                echo("<td>{$result['迟到人数']}</td>");
                                                echo("<td>{$result['第一次出勤']}</td>");
                                                echo("<td>{$result['违纪人数']}</td>");
                                                echo("<td>{$result['第二次出勤']}</td>");
                                                echo("<td>{$result['早退人数']}</td>");
                                                echo("<td>{$result['请假人数']}</td>");
                                                echo("<td><textarea>{$result['备注']}</textarea></td>");
                                                
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
				<div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-block">
                                <h2 class="card-title" style="text-align:center">记名表</h2>
                                <h4 class="card-subtitle" style="text-align:center">
									周一
								</h4>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>序号</th>
                                                <th>姓名</th>
                                                <th>学号</th>
                                            </tr>
                                        </thead>

                                        <tbody id="zhouyijimingbiao">
                                            <?php
											if($a = $connection_temp->search("缺勤人员名单",false,array("日期"=>getWeekRange(time(), 1)[2][0],"提交者"=>$person->xuehao),false)) {
												if($t = $a->fetch_assoc()["缺勤名单"]) {
													$t = json_decode($t,true);
													$count = 1;
													foreach($t as $key=>$value){
														echo("<tr>");
														echo("<td>{$count}</td>");
														echo("<td>{$value}</td>");
														echo("<td>{$key}</td>");
														echo("</tr>");
														$count++;
													}
												}
											}
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
					<div class="col-md-6">
                        <div class="card">
                            <div class="card-block">
                                <h2 class="card-title" style="text-align:center">记名表</h2>
                                <h4 class="card-subtitle" style="text-align:center">
									周二
								</h4>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>序号</th>
                                                <th>姓名</th>
                                                <th>学号</th>
                                            </tr>
                                        </thead>

                                        <tbody id="zhouerjimingbiao">
                                            <?php
											if($a = $connection_temp->search("缺勤人员名单",false,array("日期"=>getWeekRange(time(), 1)[2][1],"提交者"=>$person->xuehao))) {
												if($t = $a->fetch_assoc()["缺勤名单"]) {
													$t = json_decode($t,true);
													$count = 1;
													foreach($t as $key=>$value){
														echo("<tr>");
														echo("<td>{$count}</td>");
														echo("<td>{$value}</td>");
														echo("<td>{$key}</td>");
														echo("</tr>");
														$count++;
													}
												}
											}
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-block">
                                <h2 class="card-title" style="text-align:center">记名表</h2>
                                <h4 class="card-subtitle" style="text-align:center">
									周三
								</h4>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>序号</th>
                                                <th>姓名</th>
                                                <th>学号</th>
                                            </tr>
                                        </thead>

                                        <tbody id="zhousanjimingbiao">
                                            <?php
											if($a = $connection_temp->search("缺勤人员名单",false,array("日期"=>getWeekRange(time(), 1)[2][2],"提交者"=>$person->xuehao))) {
												if($t = $a->fetch_assoc()["缺勤名单"]) {
													$t = json_decode($t,true);
													$count = 1;
													foreach($t as $key=>$value){
														echo("<tr>");
														echo("<td>{$count}</td>");
														echo("<td>{$value}</td>");
														echo("<td>{$key}</td>");
														echo("</tr>");
														$count++;
													}
												}
											}
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
					<div class="col-md-6">
                        <div class="card">
                            <div class="card-block">
                                <h2 class="card-title" style="text-align:center">记名表</h2>
                                <h4 class="card-subtitle" style="text-align:center">
									周四
								</h4>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>序号</th>
                                                <th>姓名</th>
                                                <th>学号</th>
                                            </tr>
                                        </thead>

                                        <tbody id="zhousijimingbiao">
                                            <?php
											if($a = $connection_temp->search("缺勤人员名单",false,array("日期"=>getWeekRange(time(), 1)[2][3],"提交者"=>$person->xuehao))) {
												if($t = $a->fetch_assoc()["缺勤名单"]) {
													$t = json_decode($t,true);
													$count = 1;
													foreach($t as $key=>$value){
														echo("<tr>");
														echo("<td>{$count}</td>");
														echo("<td>{$value}</td>");
														echo("<td>{$key}</td>");
														echo("</tr>");
														$count++;
													}
												}
											}
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-block">
                                <h2 class="card-title" style="text-align:center">记名表</h2>
                                <h4 class="card-subtitle" style="text-align:center">
									周五
								</h4>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>序号</th>
                                                <th>姓名</th>
                                                <th>学号</th>
                                            </tr>
                                        </thead>

                                        <tbody id="zhouwujimingbiao">
                                            <?php
											if($a = $connection_temp->search("缺勤人员名单",false,array("日期"=>getWeekRange(time(), 1)[2][4],"提交者"=>$person->xuehao))) {
												if($t = $a->fetch_assoc()["缺勤名单"]) {
													$t = json_decode($t,true);
													$count = 1;
													foreach($t as $key=>$value){
														echo("<tr>");
														echo("<td>{$count}</td>");
														echo("<td>{$value}</td>");
														echo("<td>{$key}</td>");
														echo("</tr>");
														$count++;
													}
												}
											}
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
					<div class="col-md-6">
                        <div class="card">
                            <div class="card-block">
                                <h2 class="card-title" style="text-align:center">记名表</h2>
                                <h4 class="card-subtitle" style="text-align:center">
									周六
								</h4>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>序号</th>
                                                <th>姓名</th>
                                                <th>学号</th>
                                            </tr>
                                        </thead>

                                        <tbody id="zhouliujimingbiao">
                                            <?php
											if($a = $connection_temp->search("缺勤人员名单",false,array("日期"=>getWeekRange(time(), 1)[2][5],"提交者"=>$person->xuehao))) {
												if($t = $a->fetch_assoc()["缺勤名单"]) {
													$t = json_decode($t,true);
													$count = 1;
													foreach($t as $key=>$value){
														echo("<tr>");
														echo("<td>{$count}</td>");
														echo("<td>{$value}</td>");
														echo("<td>{$key}</td>");
														echo("</tr>");
														$count++;
													}
												}
											}
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-block">
                                <h2 class="card-title" style="text-align:center">记名表</h2>
                                <h4 class="card-subtitle" style="text-align:center">
									周日
								</h4>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>序号</th>
                                                <th>姓名</th>
                                                <th>学号</th>
                                            </tr>
                                        </thead>

                                        <tbody id="zhourijimingbiao">
                                            <?php
											if($a = $connection_temp->search("缺勤人员名单",false,array("日期"=>getWeekRange(time(), 1)[2][6],"提交者"=>$person->xuehao))) {
												if($t = $a->fetch_assoc()["缺勤名单"]) {
													$t = json_decode($t,true);
													$count = 1;
													foreach($t as $key=>$value){
														echo("<tr>");
														echo("<td>{$count}</td>");
														echo("<td>{$value}</td>");
														echo("<td>{$key}</td>");
														echo("</tr>");
														$count++;
													}
												}
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
            <footer class="footer"> © 2019 学风督导队 <i class="mdi mdi-account-multiple"></i><span>罗寅松 张锐 吴金辰</span> </footer>
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
    <!-- Custom script -->
    <!-- ============================================================== -->
	<script>
		function __creat_HTML_element(eleTagName,attributes,innerContent) {
			console.log(eleTagName,"- -",attributes,"- -",innerContent);
			var Element = document.createElement(eleTagName);
			console.log(Element);
			if(attributes!==null) {
				for(var key in attributes) {
					console.log(key);
					Element.setAttribute(key,attributes[key]);
				}
			}
			console.log(Element);
			if(innerContent!==null) {
				var node=document.createTextNode(innerContent);
				Element.appendChild(node);
			}
			console.log(Element);
			return Element;
		}
		
		function auto_fill(){ // 根据日期展示不同数据
			var riqi, tbody_id;
			switch( document.getElementById("riqi").value ) // 判断选择的日期
			{
				case "周一": riqi=0; tbody_id = "zhouyijimingbiao"; break;
				case "周二": riqi=1; tbody_id = "zhouerjimingbiao"; break;
				case "周三": riqi=2; tbody_id = "zhousanjimingbiao"; break;
				case "周四": riqi=3; tbody_id = "zhousijimingbiao"; break;
				case "周五": riqi=4; tbody_id = "zhouwujimingbiao"; break;
				case "周六": riqi=5; tbody_id = "zhouliujimingbiao"; break;
				case "周日": riqi=6; tbody_id = "zhourijimingbiao"; break;
			}
			var data = document.getElementById("self_study_data_table").children[riqi]; // 选择表格中指定日期的行
			document.getElementById("chidaorenshu").value = data.children[2].innerHTML; // 根据指定行的数据更新表单
			document.getElementById("diyicichuqin").value = data.children[3].innerHTML; // ...
			document.getElementById("weijirenshu").value = data.children[4].innerHTML; // ...
			document.getElementById("diercichuqin").value = data.children[5].innerHTML; // ...
			document.getElementById("zaotuirenshu").value = data.children[6].innerHTML; // ...
			document.getElementById("qinjiarenshu").value = data.children[7].innerHTML; // ...
			document.getElementById("beizhu").value = data.children[8].children[0].innerHTML; // ...
			
			//更新记名表提交日期和显示日期
			document.getElementById("jimingbiao").children[0].children[0].children[1].value = document.getElementById("jimingbiao").children[0].children[0].children[0].innerHTML = document.getElementById("riqi").value;
			//更新记名表表单数据（更新前先删除原有行数）
			removerow(true);
			for(var i=0;i<document.getElementById(tbody_id).childElementCount;i++) {
				addrow({"xinming":document.getElementById(tbody_id).children[i].children[1].innerHTML,"xuehao":document.getElementById(tbody_id).children[i].children[2].innerHTML});
			}
		}
		
		function jimingbiaotijiao(){
			document.getElementById("jimingbiao").submit();
		}
		
		function addrow(data=null){
			//获取已显示的数量
			var num = document.getElementById("jimingbiao").childElementCount;
			console.log("已有数量：",num-2);
			num--;
			console.log("添加后数量：",num);
			
			//**********************************************************************************
			var div_row = __creat_HTML_element("div",{"class":"row"},null);
				var div_col = __creat_HTML_element("div",{"class":"col-sm-2 mb-3"},null);
					div_col.appendChild(__creat_HTML_element("h5",null,num+"#"));
					div_col.appendChild(__creat_HTML_element("button",{"type":"button","class":"btn btn-primary","onClick":"removerow(false,this)"},"删除本行"));
				div_row.appendChild(div_col);
				
				div_col = __creat_HTML_element("div",{"class":"col-sm-5 mb-3"},null);
					div_col.appendChild(__creat_HTML_element("label",{"for":"xinming"+num},"姓名"));
					div_col.appendChild(__creat_HTML_element("input",{"type":"text","class":"form-control","id":"xinming"+num,"name":"姓名"+num,"required":"true"},null));
						if(data!=null)div_col.lastElementChild.setAttribute("value",data["xinming"]);
				div_row.appendChild(div_col);
			
				div_col = __creat_HTML_element("div",{"class":"col-sm-5 mb-3"},null);
					div_col.appendChild(__creat_HTML_element("label",{"for":"xuehao"+num},"学号"));
					div_col.appendChild(__creat_HTML_element("input",{"type":"text","class":"form-control","id":"xuehao"+num,"name":"学号"+num,"required":"true"},null));
						if(data!=null)div_col.lastElementChild.setAttribute("value",data["xuehao"]);
				div_row.appendChild(div_col);

			document.getElementById("jimingbiao").insertBefore(div_row,document.getElementById("add_remove_button"))
		}
		
		function removerow(is_all=false, element=null){
			//获取已显示的数量
			var num = document.getElementById("jimingbiao").childElementCount;
			num-=2;
			console.log("已有数量：",num);
			console.log("删除后数量：",num-1);
			if(is_all===true) {
				while(num>0) {
					document.getElementById("jimingbiao").removeChild(document.getElementById("add_remove_button").previousElementSibling);
					num--;
				}
			}
			else {
				if(element==null) {
					if(num>0) {
						document.getElementById("jimingbiao").removeChild(document.getElementById("add_remove_button").previousElementSibling);
					}
				}
				else if(element.tagName===document.createElement("button").tagName) {
					if(num>0) {
						var next_ele = element.parentNode.parentNode.nextElementSibling;
						
						var No = next_ele.children[0].children[0].innerHTML.replace("#","");
						while(No<=num) {
							
							console.log(next_ele.children[0].children[0].innerHTML," will be set as ",No-1+"#");
							next_ele.children[0].children[0].innerHTML = No-1+"#";
							
							next_ele.children[1].children[0].setAttribute("for","xinming"+(No-1))
							
							next_ele.children[1].children[1].setAttribute("id","xinming"+(No-1))
							next_ele.children[1].children[1].setAttribute("name","姓名"+(No-1))
							
							next_ele.children[2].children[0].setAttribute("for","xuehao"+(No-1))
							
							next_ele.children[2].children[1].setAttribute("id","xuehao"+(No-1))
							next_ele.children[2].children[1].setAttribute("name","学号"+(No-1))
							
							next_ele = next_ele.nextElementSibling;
							No = next_ele.children[0].children[0].innerHTML.replace("#","");
						}
						element.parentNode.parentNode.parentNode.removeChild(element.parentNode.parentNode);
					}
				}
				else console.log("Wrong Element!");
			}
			
		}
	</script>
	<!-- ============================================================== -->
    <!-- End Custom script -->
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
