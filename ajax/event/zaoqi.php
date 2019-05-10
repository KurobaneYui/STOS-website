<?php
require("../../frame/SQL_connect_frame.php");
require("../../frame/tools.php");

function show_all_people() {
	$event_SQL = new STOS_MySQL_data();
	$sql = "SELECT `姓名`, `学号`, `校区`, `分组` FROM `早起21天活动-2019上半年` ORDER BY `早起21天活动-2019上半年`.`分组` ASC;";
	if($tables = $event_SQL->execute_query($sql)) {
		while($table = $tables->fetch_assoc()) {
			echo("<tr>");
			foreach($table as $value) {
				echo("<td>{$value}</td>");
			}
			echo("</tr>");
		}
	}
}

function person_signin($name, $stuID, $QQ, $xiaoqv) { // 姓名，学号，QQ，校区
	$event_SQL = new STOS_MySQL_data();
	$sql = "SELECT * FROM `早起21天活动-2019上半年` WHERE `学号`='{$stuID}';";
	if($result = $event_SQL->execute_query($sql)) {
		if($result = $result->fetch_assoc()) {
			//echo "输入学号已经存在，请检查学号";
			return "输入学号已经存在，请检查学号。如有其他问题，请联系群管理员";
		}
	}
	$sql = "INSERT INTO `早起21天活动-2019上半年`(`姓名`, `学号`, `QQ号`, `校区`) VALUES ('{$name}','{$stuID}','{$QQ}','{$xiaoqv}');";
	$event_SQL->execute_query($sql);
	$event_SQL->get_conn()->commit();
	
	$sql = "SELECT * FROM `早起21天活动-2019上半年` WHERE `学号`='{$stuID}';";
	if($result = $event_SQL->execute_query($sql)) {
		if($result = $result->fetch_assoc()) {
			return "报名成功！分组等在下方列表展示";
		}
	}
	//echo "报名失败，请联系活动QQ群管理员";
	return "报名失败，请联系活动QQ群管理员";
}

if(isset($_GET ['name'])) {
	$name = $_GET ['name'];
	$stuID = $_GET ['xh'];
	$qq = $_GET ['q'];
	$xiaoqu = $_GET ['xq'];
	echo person_signin($name, $stuID, $qq, $xiaoqu);
}
else {
	echo show_all_people();
}
?>