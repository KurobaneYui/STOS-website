
<?php
require("../frame/SQL_connect_frame.php");
require("../frame/tools.php");

// 参数：
// 起始时间（包括），注意格式如："2019-03-21"
// 截止时间（不包括）
// 学院或者教室（允许传递数组获取多个），注意格式如：array(品学楼A203","机电")
// 用于指示前一个参数指定学院还是教室，true为学院，false为教室（允许传递数组，每一个元素指示上一个参数数组对应的元素），注意格式如：array(false,true)
function get_selfstudy_presentdata($time_start,$time_end,$school_or_classroom,$selection) { 
    $xinxiku = new STOS_MySQL();
    $shujuku = new STOS_MySQL_data();
    
    // 判断输入格式无误
    if(is_array($school_or_classroom)===is_array($selection) and count($school_or_classroom)===count($selection)) {
        $returns = array(); //返回数组，第一维等于最后一个函数参数的元素个数，第二维等于时间数，第三维是返回的教室个数，第四维是查询结果
        if(!is_array($selection)) { // 非数组
            // 判断是否为学院
            if($selection==true) { // 是则搜索
                ;
            }
            else { // 否则拆分后查询
                $jiaoshibianhao = substr($school_or_classroom,-3);
                $qvhao = substr($school_or_classroom,-4,1);
                $jiaoxuelou = rtrim(rtrim($school_or_classroom,$jiaoshibianhao),$qvhao);
            }
            $SQL = "SELECT * FROM `查早数据` WHERE `日期` BETWEEN '2019-04-08' AND '2019-04-24' AND `教学楼` LIKE '品学楼' AND `区号` LIKE 'A' AND `教室编号` LIKE '101';";
        }
        for($i=0;$i<count($selection);$i++) { // 数组
            $SQL = "SELECT * FROM `查早数据` WHERE `日期` BETWEEN '2019-04-08' AND '2019-04-24' AND `教学楼` LIKE '品学楼' AND `区号` LIKE 'A' AND `教室编号` LIKE '101';";
        }
    }
    else
        return;
}

function get_course_presentdata($time_start,$time_end,$school_or_classroom,$selection) {
    ;
}

function temp_getdata_xueyuan($xueyuan,$time) { // 例如：$xueyuan="信通", $time="2019-04-03"
    $xinxiku = new STOS_MySQL();
    $shujuku = new STOS_MySQL_data();
    
    //给定日期对应的起始日期
    $zhou_first = getWeekRange(strtotime($time),1)[0];
    //搜索学院对应的教室
    if($result = $shujuku->search("查早排班",false,array("学院"=>$xueyuan,"周起始日期"=>$zhou_first),false)) {
        $returns = array();
        while($jiaoshi_d = $result->fetch_assoc()) {
            if($t = $shujuku->search("查早数据",array("日期","教学楼","区号","教室编号","教室数据"),array("教学楼"=>$jiaoshi_d["教学楼"],"区号"=>$jiaoshi_d["区号"],"教室编号"=>$jiaoshi_d["教室编号"],"日期"=>$time),false)) {
                if($t = $t->fetch_assoc()) {
                    $data = json_decode($t["教室数据"],true);
                    unset($t["教室数据"]);
                    $t["应到人数"] = $jiaoshi_d["应到人数"];
                    array_push($returns,array_merge($data,$t));
                }
            }
        }
        return json_encode($returns,JSON_UNESCAPED_UNICODE); // 配套JavaScript时可改为echo
    }
    else return false;
}

function temp_getdata_jiaoshi($jiaoshi,$time) { // 例如：$jiaoshibianhao="品学楼A102", $time="2019-04-03"
    $xinxiku = new STOS_MySQL();
    $shujuku = new STOS_MySQL_data();
    
    //给定日期对应的起始日期
    $zhou_first = getWeekRange(strtotime($time),1)[0];
    //搜索数据
    $jiaoshibianhao = substr($jiaoshi,-3);
    $qvhao = substr($jiaoshi,-4,1);
    $jiaoxuelou = rtrim(rtrim($jiaoshi,$jiaoshibianhao),$qvhao);
    if($result = $shujuku->search("查早数据",array("日期","教学楼","区号","教室编号","教室数据"),array("教学楼"=>$jiaoxuelou,"区号"=>$qvhao,"教室编号"=>$jiaoshibianhao,"日期"=>$time),false)) {
        if($result = $result->fetch_assoc()) {
            $data = json_decode($result["教室数据"],true);
            unset($result["教室数据"]);
            if($jiaoshi_d = $shujuku->search("查早排班",false,array("教学楼"=>$jiaoxuelou,"区号"=>$qvhao,"教室编号"=>$jiaoshibianhao,"周起始日期"=>$zhou_first),false)) {
                if($jiaoshi_d = $jiaoshi_d->fetch_assoc()) {
                    $result["应到人数"] = $jiaoshi_d["应到人数"];
                    $result["学院"] = $jiaoshi_d["学院"];
                }
            }
            return json_encode(array_merge($data,$result),JSON_UNESCAPED_UNICODE); // 配套JavaScript时可改为echo
        }
        else return false;
    }
    else return false;
}

$xy = $_GET ['xy'];
$date = $_GET ['date'];
echo temp_getdata_xueyuan($xy,$date);
//response.write(temp_getdata_xueyuan($xy,$date));
?>

