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
				<div class="raw">
					<div class="alert alert-danger alert-dismissible" ><button type="button" class="close" data-dismiss="alert">&times;</button>重置密码：密码重置后，默认为对应组员学号</div>
				</div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-block">
                        <?php
                        if($value = $person->work_info()["管理组"][0]) {
                            echo("<h4 class='card-title'>{$value} 成员信息</h4>");

                            if($value=="早餐组") {
								echo("<h6 class='card-subtitle'>标准人数（不包括组长）：");
							}
							elseif($value=="队长") {
								echo("<h6 class='card-subtitle'>队长/副队 人数：");
							}
							else {
								echo("<h6 class='card-subtitle'>标准人数（包括组长）：");
							}
							
                            echo $connection->search("部门信息","部门人数", array("部门"=>$value))->fetch_assoc()['部门人数'];
                            echo("</h6>");
                            
                            echo("
                            <div class='table-responsive'>
                                <table class='table'>
                                    <thead>
                                        <tr>
                                            <th>序号</th>
                                            <th>姓名</th>
                                            <th>性别</th>
                                            <th>学号</th>
                                            <th>电话</th>
                                            <th>QQ</th>
                                            <th>重置密码</th>
                                        </tr>
                                    </thead>
                                    <tbody> 
                            ");
                                        $group_memberID = $connection->personal_group($value);
                                        $i=0;
                                        while($memberID = $group_memberID->fetch_assoc())
                                        {
                                            $i++;
                                            echo("<tr>");
                                            $info = new person_all_info($memberID["学号"]);
                                            echo("<td>".$i."</td>");
                                            echo("<td>".$info->xinming."</td>");
                                            echo("<td>".$info->xinbie."</td>");
                                            echo("<td>".$info->xuehao."</td>");
                                            echo("<td>".$info->dianhua."</td>");
                                            echo("<td>".$info->QQ."</td>");
                                            if(isset($_POST["重置密码"]) and $_POST["重置密码"]==$memberID["学号"]) {
                                                echo("<td><form class='needs-validation' novalidate action='member_info.php' method='post'><button class='btn btn-success' type='button' name='重置密码'>重置</button></form></td>");
                                            }
                                            else {
                                                echo("<td><form class='needs-validation' novalidate action='member_info.php' method='post'><button class='btn btn-primary' type='submit' name='重置密码' value='{$memberID['学号']}'>重置</button></form></td>");
                                            }
                                            echo("</tr>");
                                        }
                            echo("
                                    </tbody>
                                </table>
                            </div>
                            ");
                        }?>
                            </div>
                        </div>
                    </div>
                </div>
				
				<h2>空课表调整</h2>
				<h4>点击小圆点就可以啦~<?php echo get_color_pic("green"); ?>=>有空 <?php echo get_color_pic("red"); ?>=>没空</h4>
				<div class="row">
					<?php
					if($value = $person->work_info()["管理组"][0]) {
						$group_memberID = $connection->personal_group($value);
						while($memberID = $group_memberID->fetch_assoc()) {
							$info = new person_all_info($memberID["学号"]);
							$kongke = array (
								"0" => $info->work_info()['周一空课'],
								"1" => $info->work_info()['周二空课'],
								"2" => $info->work_info()['周三空课'],
								"3" => $info->work_info()['周四空课'],
								"4" => $info->work_info()['周五空课']
								);
							echo("
					<div class='col-md-6'>
						<div class='card'>
							<div class='card-block'>
								<h4 class='card-title'>{$info->xinming}<span id='badge-{$info->xuehao}' class='badge badge-info'></span></h4>
								<ul class='nav nav-tabs' id='OE-tab' role='tablist'>
									<li class='nav-item'><a href='#O-{$info->xuehao}' class='nav-link active' id='O-{$info->xuehao}-tab' data-toggle='tab' role='tab' aria-controls='O-{$info->xuehao}' aria-selected='true'>单周</a></li>
									<li class='nav-item'><a href='#E-{$info->xuehao}' class='nav-link' id='E-{$info->xuehao}-tab' data-toggle='tab' role='tab' aria-controls='E-{$info->xuehao}' aria-selected='false'>双周</a></li>
								</ul>
								<div class='tab-content' id='OE-{$info->xuehao}-content'>
									<div class='tab-pane fade show active' id='O-{$info->xuehao}' role='tabpanel' aria-labelledby='O-{$info->xuehao}-tab'>
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
						");
												for($i=0;$i<4;$i++) {
													echo("<tr>");
													
													if(intval($kongke[0][$i])%2) {
														echo("<td onClick='change_color(this)' weekday=0>".get_color_pic("green")."</td>");
													}
													else{ echo("<td onClick='change_color(this)' weekday=0>".get_color_pic("red")."</td>"); }
													
													if(intval($kongke[1][$i])%2) {
														echo("<td onClick='change_color(this)' weekday=1>".get_color_pic("green")."</td>");
													}
													else{ echo("<td onClick='change_color(this)' weekday=1>".get_color_pic("red")."</td>"); }
													
													if(intval($kongke[2][$i])%2) {
														echo("<td onClick='change_color(this)' weekday=2>".get_color_pic("green")."</td>");
													}
													else{ echo("<td onClick='change_color(this)' weekday=2>".get_color_pic("red")."</td>"); }
													
													if(intval($kongke[3][$i])%2) {
														echo("<td onClick='change_color(this)' weekday=3>".get_color_pic("green")."</td>");
													}
													else{ echo("<td onClick='change_color(this)' weekday=3>".get_color_pic("red")."</td>"); }
													
													if(intval($kongke[4][$i])%2) {
														echo("<td onClick='change_color(this)' weekday=4>".get_color_pic("green")."</td>");
													}
													else{ echo("<td onClick='change_color(this)' weekday=4>".get_color_pic("red")."</td>"); }
													
													echo("</tr>");
												}
						echo("
												</tbody>
											</table>
										</div>
									</div>
									<div class='tab-pane fade' id='E-{$info->xuehao}' role='tabpanel' aria-labelledby='E-{$info->xuehao}-tab'>
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
						");
												for($i=0;$i<4;$i++) {//s
													echo("<tr>");
													if(intval($kongke[0][$i])>1) {
														echo("<td onClick='change_color(this)' weekday=0>".get_color_pic("green")."</td>");
													}
													else{ echo("<td onClick='change_color(this)' weekday=0>".get_color_pic("red")."</td>"); }
													
													if(intval($kongke[1][$i])>1) {
														echo("<td onClick='change_color(this)' weekday=1>".get_color_pic("green")."</td>");
													}
													else{ echo("<td onClick='change_color(this)' weekday=1>".get_color_pic("red")."</td>"); }
													
													if(intval($kongke[2][$i])>1) {
														echo("<td onClick='change_color(this)' weekday=2>".get_color_pic("green")."</td>");
													}
													else{ echo("<td onClick='change_color(this)' weekday=2>".get_color_pic("red")."</td>"); }
													
													if(intval($kongke[3][$i])>1) {
														echo("<td onClick='change_color(this)' weekday=3>".get_color_pic("green")."</td>");
													}
													else{ echo("<td onClick='change_color(this)' weekday=3>".get_color_pic("red")."</td>"); }
													
													if(intval($kongke[4][$i])>1) {
														echo("<td onClick='change_color(this)' weekday=4>".get_color_pic("green")."</td>");
													}
													else{ echo("<td onClick='change_color(this)' weekday=4>".get_color_pic("red")."</td>"); }
													
													echo("</tr>");
												}
						echo("
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
							");
						}
					}
					?>
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
    <!-- Costum JS -->
    <!-- ============================================================== -->
	<script>
		function change_color(tdFORimg) {
			var kongkediv = tdFORimg.parentElement.parentElement.parentElement.parentElement.parentElement;
			if(tdFORimg.children[0].getAttribute("alt")=="green") {
				tdFORimg.innerHTML="<?php echo(get_color_pic("red")); ?>";
			}
			else {
				tdFORimg.innerHTML="<?php echo(get_color_pic("green")); ?>";
			}
			
			var xmlhttp;
			var jsonstr;
			var str;
			if (window.XMLHttpRequest) {
				// IE7+, Firefox, Chrome, Opera, Safari 浏览器执行代码
				xmlhttp=new XMLHttpRequest();
			}
			else {
				// IE
				try {
					xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
				}
				catch (e) {
					try {
						xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
					}
					catch (e) {
						alert("您的浏览器不支持AJAX！");
						return false;
					}
				}
			}
			
			var xuehao = kongkediv.id.split("-")[1];
			var weekday = tdFORimg.getAttribute("weekday");
			var O_kongketbody = document.getElementById("O-"+xuehao).getElementsByTagName("tbody")[0];
			var E_kongketbody = document.getElementById("E-"+xuehao).getElementsByTagName("tbody")[0];
			var N_S = "";
			for(var i=0;i<4;i++) {
				var N = 0;
				if(O_kongketbody.children[i].children[weekday].children[0].getAttribute("alt")=="green") {
					N += 1;
				}
				if(E_kongketbody.children[i].children[weekday].children[0].getAttribute("alt")=="green") {
					N += 2;
				}
				N_S += N.toString();
			}
			
			xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState==4 && xmlhttp.status==200) {
					jsonstr = xmlhttp.responseText;
					str = JSON.parse(jsonstr);
					if(str["status"]=="true") {
						var notice = document.getElementById("badge-"+xuehao);
						if(notice.innerHTML=="成功") {
							notice.innerHTML = "成功 · 2";
						}
						else if(notice.innerHTML.split(" · ").length==2) {
							var num = Number(notice.innerHTML.split(" · ")[1])+1;
							notice.innerHTML = "成功 · "+String(num);
						}
						else {
							notice.innerHTML = "成功";
						}
					}
					else {
						alert("错误！！："+str["content"]);
						window.location.reload();
					}
				}
			}
			xmlhttp.open("POST","/ajax/personal/group_leader/member_info.php",true);
			xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
			xmlhttp.send("修改空课=yes"+"&组员学号="+xuehao+"&空课周="+weekday+"&空课字符串="+N_S);
		}
	</script>
    <!-- ============================================================== -->
    <!-- End Costum JS -->
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