
<?php
require("../frame/SQL_connect_frame.php");
require("../frame/tools.php");

function temp_getdata_xueyuan($xueyuan,$nianji,$time) { // 例如：$xueyuan="信通", $nianji='2018', $time="2019-04-03"
    $xinxiku = new STOS_MySQL();
    $shujuku = new STOS_MySQL_data();

    //搜索学院对应的教室
    if($result = $shujuku->search("查课排班",false,array("学院"=>$xueyuan,"年级"=>$nianji,"日期"=>$time),false)) {
        $returns = array();
        while($jiaoshi_d = $result->fetch_assoc()) {
            if($t = $shujuku->search("查课数据",array("时段与上课周","教学楼","区号","教室编号","教室数据"),array("教学楼"=>$jiaoshi_d["教学楼"],"区号"=>$jiaoshi_d["区号"],"教室编号"=>$jiaoshi_d["教室编号"],"日期"=>$time),false)) {
                if($t = $t->fetch_assoc()) {
                    $data = json_decode($t["教室数据"],true);
                    unset($t["教室数据"]);
                    $t["应到人数"] = $jiaoshi_d["应到人数"];
                    $t["课程名称"] = $jiaoshi_d["课程名称"];
                    array_push($returns,array_merge($data,$t));
                }
            }
        }
        return json_encode($returns,JSON_UNESCAPED_UNICODE); // 配套JavaScript时可改为echo
    }
    else return false;
}

$xy = $_GET ['xy'];
$nj = $_GET ['nj'];
$date = $_GET['date'];
$date_start = $_GET['date_start'];
$date_end = $_GET['date_end'];
echo temp_getdata_xueyuan($xy,$nj,$date);
//response.write(temp_getdata_xueyuan($xy,$date));
?>

