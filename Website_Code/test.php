<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>无标题文档</title>


</head>

<body>
	<?php
	$pattern1 = "/(品学楼)/";
	$pattern2 = "/(立人楼)/";
	if(preg_match_all($pattern1,$a)) {echo 1;
		echo(strstr($a,'品学楼',true));echo "<br/>";
		echo(strstr($a,'品学楼',false));echo "<br/>";
	}
	else if(preg_match_all($pattern2,$a)) { echo 2;
		echo(strstr($a,'立人楼',true));echo "<br/>";
		echo(strstr($a,'立人楼',false));echo "<br/>";
	}
	?>
</body>
</html>