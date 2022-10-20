<?php
require("../../frame/Person_Class_frame.php");
session_start();
if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
	// 可以进行后续数据录入步骤
	$connection = new STOS_MySQL(); // 建立数据库连接
    $person = new person_all_info( $_SESSION[ "username" ] ); // 获取个人信息
    $self_study_classroom = new self_study_data_set($person->xuehao, time()); // 获取查早教室信息
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
	if(isset($_POST["记名表"]) and $_POST["记名表"]=="yes") { // 如果日胶缺勤表数据
		upload_absent_data();
		return;
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
?>