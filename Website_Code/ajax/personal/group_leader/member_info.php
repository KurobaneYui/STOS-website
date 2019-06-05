<?php
session_start();
require("../../../frame/Person_Class_frame.php");

if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
    $connection = new STOS_MySQL(); // 建立数据库连接
    $person = new person_all_info( $_SESSION[ "username" ] ); // 获取个人信息
	require("../../../frame/empty_time.php");
	if($person->work_info()["权限"]!=2) header( 'refresh:0; url=../../../log/logout.php' ); // 如果不是组长，强制登出
}
else { // 没有登陆
    header( 'refresh:0; url=../../../log/login.php' ); // 返回登陆页面
}
?>
<?php
if(isset($_POST["修改空课"]) and $_POST["修改空课"]=="yes") {
    if($value = $person->work_info()["管理组"][0]) {
		$group_memberID = $connection->personal_group($value);
		$wrong = false;
		switch($_POST["空课周"]) {
			case 0:$_POST["空课周"]="周一空课";break;
			case 1:$_POST["空课周"]="周二空课";break;
			case 2:$_POST["空课周"]="周三空课";break;
			case 3:$_POST["空课周"]="周四空课";break;
			case 4:$_POST["空课周"]="周五空课";break;
			default: $wrong = true;
		}
		$success = false;
		while(!$wrong and !$success and ($memberID = $group_memberID->fetch_assoc()["学号"])) {
			if($memberID==$_POST["组员学号"]) {
				$success = true;
				$sql = "UPDATE `成员岗位` SET `{$_POST["空课周"]}`='{$_POST["空课字符串"]}' WHERE `学号`='{$memberID}';";
				$connection->execute_query($sql);
			}
		}
		if($success) { echo(json_encode(array("status"=>"true","content"=>"已修改"),JSON_UNESCAPED_UNICODE)); }
		else { echo(json_encode(array("status"=>"false","content"=>"数据格式错误"),JSON_UNESCAPED_UNICODE)); };
	}
}
else {
	echo(json_encode(array("status"=>"false","content"=>"数据格式错误"),JSON_UNESCAPED_UNICODE)); // 返回json数组
}
?>