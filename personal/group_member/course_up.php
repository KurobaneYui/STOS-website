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
<?php // 临时用的php，第二周起要加入日期******************************************
$connection_temp = new STOS_MySQL_data();
if(isset($person)) {
	$courses_array = array(); // 查课教室数组，二维
	$sql = "SELECT * FROM `查课排班` WHERE `日期` BETWEEN '2019-05-13' AND '2019-05-19' AND `查课组员`='{$person->xuehao}';";
	if($results = $connection_temp->execute_query($sql)) {
		while($course = $results->fetch_assoc()) {
			array_push($courses_array,$course);
		}
	}
}
?>
<?php
if(isset($person)) {
	if(isset($_POST["查课数据"]) and $_POST["查课数据"]==="yes") {
		$data_array = array
		(
			"第一次出勤"=>$_POST["第一次出勤"],
			"第一次违纪"=>$_POST["第一次违纪"],
			"第二次出勤"=>$_POST["第二次出勤"],
			"第二次违纪"=>$_POST["第二次违纪"],
			"备注"=>str_replace("\r","\\r",str_replace("\n","\\n",$_POST["备注"]))
		);
		$jiaoshishuju = json_encode($data_array, JSON_UNESCAPED_UNICODE);
		unset($data_array);
		
		$pattern1 = "/(品学楼)/";
		$pattern2 = "/(立人楼)/";
		if(preg_match_all($pattern1,$_POST['教室'])) {
			$shiduan = strstr($_POST['教室'],'品学楼',true);
			$jiaoshi = strstr($_POST['教室'],'品学楼',false);
		}
		else if(preg_match_all($pattern2,$_POST['教室'])) {
			$shiduan = strstr($_POST['教室'],'立人楼',true);
			$jiaoshi = strstr($_POST['教室'],'立人楼',false);
		}
		
		foreach($courses_array as $i) {
			if($i["教学楼"].$i["区号"].$i["教室编号"] === $jiaoshi and $i['时段与上课周'] === $shiduan) {
				$data_array = array
				(
					"日期"=>$i['日期'],
					"时段与上课周"=>$i['时段与上课周'],
					"教学楼"=>$i['教学楼'],
					"区号"=>$i['区号'],
					"教室编号"=>$i['教室编号'],
					"教室数据"=>$jiaoshishuju,
					"提交者"=>$person->xuehao
				);
				
				$conditions = array
					(
						"日期"=>$i['日期'],
						"时段与上课周"=>$i['时段与上课周'],
						"教学楼"=>$i['教学楼'],
						"区号"=>$i['区号'],
						"教室编号"=>$i['教室编号']
					);
				if($connection_temp->search("查课数据",false,$conditions,false)->fetch_assoc()) {
					$connection_temp->update("查课数据",$data_array,$conditions,false);
					$connection_temp->get_conn()->commit();
				}
				else {
					$connection_temp->insert("查课数据",$data_array);
					$connection_temp->get_conn()->commit();
				}
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
    <title>查课数据录入</title>
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
						<h3 class="text-themecolor m-b-0 m-t-0">查课信息录入</h3>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="javascript:void(0)"><?php echo $person->xinming; ?></a></li>
							<li class="breadcrumb-item active">查课信息录入</li>
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
                            <strong>注意!</strong> 每节课的数据分开提交，不然数据就飞走了(～o￣3￣)～
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class = "card">
                            <div class="card-block">
                                <form action="#" method="post">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="jiaoshi">选择课程时间及对应教室</label>
                                            <select class="custom-select" id="jiaoshi" name="教室" required onchange="auto_fill()">
												<?php
												foreach($courses_array as $i) {
													echo("<option value='{$i['时段与上课周']}{$i['教学楼']}{$i['区号']}{$i['教室编号']}'>{$i['时段与上课周']}：{$i['教学楼']}{$i['区号']}{$i['教室编号']}</option>");
												}
												?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="diyicichuqin">第一次<strong>出勤</strong>人数</label>
                                            <input type="text" class="form-control" id="diyicichuqin" name="第一次出勤" required>
                                        </div>
										<div class="col-md-6 mb-3">
                                            <label for="diyiciweiji">第一次<strong>违纪</strong>人数</label>
                                            <input type="text" class="form-control" id="diyiciweiji" name="第一次违纪" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="diercichuqin"><strong>第二次</strong>出勤人数</label>
                                            <input type="text" class="form-control" id="diercichuqin" name="第二次出勤" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="dierciweiji"><strong>第二次</strong>违纪人数</label>
                                            <input type="text" class="form-control" id="dierciweiji" name="第二次违纪" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                        <label for="beizhu">备注</label>
                                        <textarea class="form-control" id="beizhu" name="备注"></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 mb-3">
                                            <button class="btn btn-primary btn-block" type="submit" name="查课数据" value="yes">提交修改</button>
                                        </div>
                                    </div>
                                </form>
                                <br/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-block">
                                <h2 class="card-title" style="text-align:center">查课教室</h2>
                                <h4 id="chazaojiaoshi" class="card-subtitle" style="text-align:center">
									查课日期及时段：<?php echo($courses_array[0]["日期"].' '.substr($courses_array[0]["时段与上课周"],0,3)); ?>节，表编号：<?php echo($courses_array[0]["编号"]); ?>
								</h4>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>课程名称</th>
                                                <th>上课教室</th>
												<th>应到人数</th>
                                                <th>第一次出勤</th>
                                                <th>第一次违纪</th>
                                                <th>第二次出勤</th>
                                                <th>第二次违纪</th>
                                                <th>学院年级</th>
                                                <th>备注</th>
                                            </tr>
                                        </thead>

                                        <tbody id="courses_data_table">
											<?php
											foreach($courses_array as $i) {
												$sql = "SELECT * FROM `查课数据` WHERE 日期='{$i['日期']}' and 时段与上课周='{$i['时段与上课周']}' and 教学楼='{$i['教学楼']}' and 区号='{$i['区号']}' and 教室编号='{$i['教室编号']}';";
												if($d = $connection_temp->execute_query($sql)) {
													if($d = $d->fetch_assoc()) {
														$d = json_decode($d["教室数据"],true);
													}
												}
												echo("<tr>");
												echo("<td>{$i['课程名称']}</td>");
												echo("<td>{$i['时段与上课周']}{$i['教学楼']}{$i['区号']}{$i['教室编号']}</td>");
												echo("<td>{$i['应到人数']}</td>");
												echo("<td>{$d['第一次出勤']}</td>");
												echo("<td>{$d['第一次违纪']}</td>");
												echo("<td>{$d['第二次出勤']}</td>");
												echo("<td>{$d['第二次违纪']}</td>");
												echo("<td>{$i['学院']}{$i['年级']}</td>");
												echo("<td><textarea>{$d['备注']}</textarea></td>");
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
		function auto_fill(){ // 根据教室展示不同数据
			var jiaoshi = document.getElementById("jiaoshi").value; // 获取选择的教室
			var tbody = document.getElementById("courses_data_table");
			var jiaoshi_num = tbody.childElementCount;
			for(var num=0;num<jiaoshi_num;num++) {
				if(tbody.children[num].children[1].innerHTML==jiaoshi) {
					document.getElementById("diyicichuqin").value = tbody.children[num].children[3].innerHTML;
					document.getElementById("diyiciweiji").value = tbody.children[num].children[4].innerHTML;
					document.getElementById("diercichuqin").value = tbody.children[num].children[5].innerHTML;
					document.getElementById("dierciweiji").value = tbody.children[num].children[6].innerHTML;
					document.getElementById("beizhu").innerHTML = tbody.children[num].children[8].children[0].innerHTML;
					break;
				}
			}
			var data = document.getElementById("courses_data_table").children[riqi]; // 选择表格中指定日期的行
			document.getElementById("chidaorenshu").value = data.children[2].innerHTML; // 根据指定行的数据更新表单
			document.getElementById("diyicichuqin").value = data.children[3].innerHTML; // ...
			document.getElementById("weijirenshu").value = data.children[4].innerHTML; // ...
			document.getElementById("diercichuqin").value = data.children[5].innerHTML; // ...
			document.getElementById("zaotuirenshu").value = data.children[6].innerHTML; // ...
			document.getElementById("qinjiarenshu").value = data.children[7].innerHTML; // ...
			document.getElementById("beizhu").value = data.children[8].children[0].innerHTML; // ...
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
