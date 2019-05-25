<?php
if(!function_exists("getWeekRange")){
function getWeekRange($date, $start=0){

    // 获取日期是周几
    $day = date('w',$date);

    // 计算开始日期
    $currenttime_string = date('Y-m-d',$date);
    if($day>=$start){
        $startdate = date("Y-m-d",strtotime($currenttime_string."-".($day-$start)."day"));
    }elseif($day<$start){
        $startdate = date("Y-m-d",strtotime($currenttime_string."-".(+7-$start+$day)."day"));
    }

    // 结束日期=开始日期+6
    $enddate = date("Y-m-d",strtotime($startdate."+6day"));
	
	//开始日期到结束日期的所有日期
	$all_date = array(
		$startdate,
		date("Y-m-d",strtotime($startdate."+1day")),
		date("Y-m-d",strtotime($startdate."+2day")),
		date("Y-m-d",strtotime($startdate."+3day")),
		date("Y-m-d",strtotime($startdate."+4day")),
		date("Y-m-d",strtotime($startdate."+5day")),
		$enddate
	);

    return array($startdate, $enddate, $all_date);
}}
?>