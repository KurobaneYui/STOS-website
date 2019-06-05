<?php
require("../../../frame/Person_Class_frame.php");
session_start();
if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
	// 可以进行后续数据录入步骤
	$connection = new STOS_MySQL(); // 建立数据库连接
	$connection_data = new STOS_MySQL_data();
    $person = new person_all_info( $_SESSION[ "username" ] ); // 获取个人信息

	$sql = "SELECT * FROM `代查安排` WHERE `代查日期`='".date("Y-m-d",time())."';";
	$daicha_paibans = $connection_data->execute_query($sql);
	$riqi = array();
	$xuehao_d = array();
	$self_study_classroom = array();

	while($daicha_single = $daicha_paibans->fetch_assoc()) {
		if($daicha_single['接受者姓名']==$person->xinming and $daicha_single['申请类型']=="早自习") {
			array_push($riqi,$daicha_single['代查日期']);
			array_push($xuehao_d,$daicha_single['申请者学号']);
			array_push($self_study_classroom,new self_study_data_set($daicha_single['申请者学号'], time())); // 获取查早教室信息
		}
		// 临时用的php，用于查课代查******************************************
		elseif($daicha_single['接受者姓名']==$person->xinming and $daicha_single['申请类型']=="查课") {
			$courses_array = array(); // 查课教室数组，二维
			$sql = "SELECT * FROM `查课排班` WHERE `日期`='{$daicha_single['代查日期']}' AND `查课组员`='{$daicha_single['申请者学号']}' ORDER BY `教学楼`,`区号`,`教室编号` ASC;";
			if($results = $connection_data->execute_query($sql)) {
				while($course = $results->fetch_assoc()) {
					array_push($courses_array,$course);
				}
			}
		}
	}
}
else { // 没有登陆信息
	echo(json_encode(array("status"=>"false","content"=>"请先登录"),JSON_UNESCAPED_UNICODE)); // 返回json数组
	return;
}

// 处理输入数据
if($_SERVER['REQUEST_METHOD']=="POST") { // POST数据
	if(isset($_POST["早自习数据"]) and $_POST["早自习数据"]=="yes") { // 如果提交早自习数据
		upload_selfstudy_data();
		return;
	}
	if(isset($_POST["记名表"]) and $_POST["记名表"]=="yes") { // 如果提交缺勤表数据
		upload_absent_data();
		return;
	}
	if(isset($_POST["查课数据"]) and $_POST["查课数据"]=="yes") { // 如果提交查课数据
		upload_chake_data();
		
	}
	else {
		echo(json_encode(array("status"=>"false","content"=>"数据格式错误"),JSON_UNESCAPED_UNICODE)); // 返回json数组
	}
}
elseif($_SERVER['REQUEST_METHOD']=="GET") { // GET数据
	echo(json_encode(array("status"=>"false","content"=>"暂不支持GET方法，如有疑问请联系管理员"),JSON_UNESCAPED_UNICODE)); // 暂不支持GET方式
	return;
}

function upload_selfstudy_data() {
	global $self_study_classroom;
	$self_study_classroom = $self_study_classroom[0];
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
	echo(json_encode(array("status"=>"true","content"=>"查早数据已提交"),JSON_UNESCAPED_UNICODE));
}

function upload_absent_data() {
	global $self_study_classroom;
	$connection_temp = new STOS_MySQL_data();
	$data_json = array();
	if(isset($_POST["姓名1"]) and $_POST["姓名1"]!="" and $_POST["学号1"]!="") {
		for($i=1;$i<200;$i++){
			if(isset($_POST["姓名".$i]) and $_POST["姓名".$i]!="" and $_POST["学号".$i]!="") {
					$data_json[$_POST["学号".$i]] = $_POST["姓名".$i];
				}
		}
		if(true) {
			switch($_POST["日期"]) {
				case "周一":$queqinbiaoriqi=getWeekRange(time(), 1)[2][0];break;
				case "周二":$queqinbiaoriqi=getWeekRange(time(), 1)[2][1];break;
				case "周三":$queqinbiaoriqi=getWeekRange(time(), 1)[2][2];break;
				case "周四":$queqinbiaoriqi=getWeekRange(time(), 1)[2][3];break;
				case "周五":$queqinbiaoriqi=getWeekRange(time(), 1)[2][4];break;
				case "周六":$queqinbiaoriqi=getWeekRange(time(), 1)[2][5];break;
				case "周日":$queqinbiaoriqi=getWeekRange(time(), 1)[2][6];break;
				default:exit;
			}
			$data_array = array(
				"日期"=>$queqinbiaoriqi,
				"区号"=>$self_study_classroom[0]->get_classroomID()["区号"],
				"教室编号"=>$self_study_classroom[0]->get_classroomID()["教室编号"],
				"教学楼"=>$self_study_classroom[0]->get_classroomID()["教学楼"],
				"缺勤名单"=>json_encode($data_json, JSON_UNESCAPED_UNICODE),
				"提交者"=>$_POST["申请者学号"]
			);

			$conditions = array(
				"日期"=>$data_array["日期"],
				"教学楼"=>$data_array["教学楼"],
				"区号"=>$data_array["区号"],
				"教室编号"=>$data_array["教室编号"]
			);
			if($connection_temp->search("缺勤人员名单",false,$conditions,false)->fetch_assoc()){
				$sql = "DELETE FROM `缺勤人员名单` WHERE `日期`='{$data_array["日期"]}'";
				$sql = $sql." AND `教学楼`='{$data_array['教学楼']}' AND `区号`='{$data_array['区号']}'";
				$sql = $sql." AND `教室编号`='{$data_array['教室编号']}';";
				$connection_temp->execute_query($sql);
			}
			$connection_temp->insert("缺勤人员名单",$data_array);
		}
	}
	echo(json_encode(array("status"=>"true","content"=>"记名表数据已提交"),JSON_UNESCAPED_UNICODE));
}

function upload_chake_data() {
	global $courses_array;
	$connection_temp = new STOS_MySQL_data();
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
				"提交者"=>$_POST['申请者学号'],
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
				$data_array["编号"]=$connection_temp->search("查课排班",false,$conditions,false)->fetch_assoc()["编号"];
				$connection_temp->insert("查课数据",$data_array);
				$connection_temp->get_conn()->commit();
			}
			echo(json_encode(array("status"=>"true","content"=>"查早数据已提交"),JSON_UNESCAPED_UNICODE));
		}
	}
}

//function get_classroom_data() {
//	;
//}
//
//function get_absent_data() {
//	;
//}
?>