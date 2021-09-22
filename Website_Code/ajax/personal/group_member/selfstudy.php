<?php
require("../../../frame/Person_Class_frame.php");
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
	global $self_study_classroom;
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
	$connection_temp = new STOS_MySQL_data();
	$data_json = array();
    for($i=1;$i<200;$i++){
        if(isset($_POST["姓名".$i]) and $_POST["姓名".$i]!="" and $_POST["学号".$i]!="") {
                $data_json[$_POST["学号".$i]] = $_POST["姓名".$i];
            }
    }
    if($a = $connection_temp->search("查早排班",false,array("查早组员"=>$_SESSION['username'],"周起始日期"=>getWeekRange(time(), 1)[0]),false)->fetch_assoc()) {
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
            $sql = "DELETE FROM `缺勤人员名单` WHERE `日期`='{$data_array["日期"]}'";
            $sql = $sql." AND `教学楼`='{$data_array['教学楼']}' AND `区号`='{$data_array['区号']}'";
            $sql = $sql." AND `教室编号`='{$data_array['教室编号']}';";
            $connection_temp->execute_query($sql);
        }
        if(isset($_POST["姓名1"]) and $_POST["姓名1"]!="" and $_POST["学号1"]!="")
            $connection_temp->insert("缺勤人员名单",$data_array);
    }

	echo(json_encode(array("status"=>"true","content"=>"记名表数据已提交"),JSON_UNESCAPED_UNICODE));
}

//function get_classroom_data() {
//	;
//}
//
//function get_absent_data() {
//	;
//}
?>