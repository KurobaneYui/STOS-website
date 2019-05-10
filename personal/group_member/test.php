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
								<!--<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
									&times;
								</button>-->
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
									<div class="row">
										<div class="col-md-2 mb-3">
											<h4>1#</h4>
										</div>
										<div class="col-md-5 mb-3">
											<label for="xinming1">姓名</label>
											<input type="text" class="form-control" id="xinming1" name="姓名1" required>
										</div>
										<div class="col-md-5 mb-3">
											<label for="xeuhao1">学号</label>
											<input type="text" class="form-control" id="xeuhao1" name="学号1" required>
										</div>
									</div>
									<div id="add_remove_button" class="row">
										<div class="col-sm-1 mb-3">
											<button type="button" class="btn btn-primary" onClick="removerow()">-</button>
										</div>
										<div class="col-sm-1 mb-3">
											<button type="button" class="btn btn-primary" onClick="addrow()">+</button>
										</div>
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
                                <h4 id="chazaojiaoshi" class="card-subtitle" style="text-align:center"><?php echo(join("",$self_study_classroom->get_classroomID())); ?></h4>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>日期</th>
                                                <th>教室</th>
                                                <th>学院</th>
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
                                                echo("<td>{$result['教学楼']}{$result['区号']}{$result['教室编号']}</td>");
                                                echo("<td>{$result_["学院"]}</td>");
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
		function auto_fill(){
			var riqi;
			switch( document.getElementById("riqi").value )
			{
				case "周一": riqi=0; break;
				case "周二": riqi=1; break;
				case "周三": riqi=2; break;
				case "周四": riqi=3; break;
				case "周五": riqi=4; break;
				case "周六": riqi=5; break;
				case "周日": riqi=6; break;
			}
			var data = document.getElementById("self_study_data_table").children[riqi];
			document.getElementById("chidaorenshu").value = data.children[4].innerHTML;
			document.getElementById("diyicichuqin").value = data.children[5].innerHTML;
			document.getElementById("weijirenshu").value = data.children[6].innerHTML;
			document.getElementById("diercichuqin").value = data.children[7].innerHTML;
			document.getElementById("zaotuirenshu").value = data.children[8].innerHTML;
			document.getElementById("qinjiarenshu").value = data.children[9].innerHTML;
			document.getElementById("beizhu").value = data.children[10].children[0].innerHTML;
			
			document.getElementById("jimingbiao").children[0].children[0].children[1].value = document.getElementById("jimingbiao").children[0].children[0].children[0].innerHTML = document.getElementById("riqi").value;
		}

		function queqinbiao(){
			var jiaoshi = document.getElementById("chazaojiaoshi").innerHTML;
			if(jiaoshi[3]=="A"){
				if(Number(jiaoshi[4])<=2){
					if(confirm("录入缺勤人员名单？\n将离开本页，跳转至“A区1、2楼缺勤表”"))
						window.location.href="#";
				}
				else{
					if(confirm("录入缺勤人员名单？\n将离开本页，跳转至“A区3、4楼缺勤表”"))
						window.location.href="#";
				}
			}
			else{
				if(confirm("录入缺勤人员名单？\n将离开本页，跳转至“B区缺勤表”"))
					window.location.href="#";
			}
		}
		
		function jimingbiaotijiao(){
			document.getElementById("jimingbiao").submit();
		}
		
		function addrow(){
			//获取已显示的数量
			var num = document.getElementById("jimingbiao").childElementCount;
			console.log("已有数量：",num-2);
			num--;
			console.log("添加后数量：",num);
			
			//添加div，属性为row
			var div_row = document.createElement("div");
			div_row.setAttribute("class","row");
				//添加div，属性为col
				var div_col = document.createElement("div");
				div_col.setAttribute("class","col-md-2 mb-3");
					//添加h5
					var div_h5 = document.createElement("h5");
					var node=document.createTextNode(num+'#');
					div_h5.appendChild(node);
					div_col.appendChild(div_h5)
				div_row.appendChild(div_col)
				
				//添加div，属性为col
				var div_col = document.createElement("div");
				div_col.setAttribute("class","col-md-5 mb-3");
					//添加label
					var div_label = document.createElement("label");
					div_label.setAttribute("for","xinming"+num);
					var node=document.createTextNode('姓名');
					div_label.appendChild(node);
					div_col.appendChild(div_label)
					//添加input
					var div_input = document.createElement("input");
					div_input.setAttribute("type","text");
					div_input.setAttribute("class","form-control");
					div_input.setAttribute("id","xinming"+num);
					div_input.setAttribute("name","姓名"+num);
					div_input.setAttribute("required","true")
					div_col.appendChild(div_input)
				div_row.appendChild(div_col)
				
				//添加div，属性为col
				var div_col = document.createElement("div");
				div_col.setAttribute("class","col-md-5 mb-3");
					//添加label
					var div_label = document.createElement("label");
					div_label.setAttribute("for","xuehao"+num);
					var node=document.createTextNode('学号');
					div_label.appendChild(node);
					div_col.appendChild(div_label)
					//添加input
					var div_input = document.createElement("input");
					div_input.setAttribute("type","text");
					div_input.setAttribute("class","form-control");
					div_input.setAttribute("id","xuehao"+num);
					div_input.setAttribute("name","学号"+num);
					div_input.setAttribute("required","true")
					div_col.appendChild(div_input)
				div_row.appendChild(div_col)

			document.getElementById("jimingbiao").insertBefore(div_row,document.getElementById("add_remove_button"))
		}
		
		function removerow(){
			//获取已显示的数量
			var num = document.getElementById("jimingbiao").childElementCount;
			num-=2;
			console.log("已有数量：",num);
			console.log("删除后数量：",num-1);
			if(num>0)
			   document.getElementById("jimingbiao").removeChild(document.getElementById("add_remove_button").previousElementSibling);
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
