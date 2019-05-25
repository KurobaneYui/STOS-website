<?php
require("../../../frame/SQL_connect_frame.php");

function get_queqin_data($day,$memberID) {
	$conn_temp = new STOS_MySQL_data();
	if($day_datas = $conn_temp->execute_query("SELECT * FROM `缺勤人员名单` WHERE `日期` = '{$day}' AND `提交者`='{$memberID}';")) {
		echo("<table class='table'>");
		echo("<thead><tr><th>序号</th><th>学号</th><th>姓名</th></tr></thead><tbody>");
		$count = 1;
		foreach(json_decode($day_datas->fetch_assoc()["缺勤名单"],true) as $key=>$value) {
			echo("<tr>");
			echo("<td>{$count}</td>");
			echo("<td>{$value}</td>");
			echo("<td>{$key}</td>");
			echo("</tr>");
			$count++;
			}
		echo("</tbody></table>");
	}
	$conn_temp->__destruct();
}

if(isset($_GET["day"])) {
	get_queqin_data($_GET["day"],$_GET["memberID"]);
}
?>