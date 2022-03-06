<?php

// 选择某个人自己的查早安排与数据
$sql = "SELECT * FROM (查早排班,学院早自习安排) LEFT JOIN 查早数据 on 学院早自习安排.学院早自习安排编号 = 早自习排班编号
            WHERE 学院早自习安排.学院早自习安排编号 IN (
                SELECT 学院早自习安排编号 FROM 学院早自习安排 WHERE 自习日期 BETWEEN '{xxx}' and '{xxx}'
            ) and 学院早自习安排.学院早自习安排编号=查早排班.学院早自习安排编号 and 最终检查者学号='{$personID}';";
// 选择某个人自己的缺勤表安排与数据
$sql = "SELECT * FROM 查早排班 LEFT JOIN 查早缺勤表 on 学院早自习安排编号 = 早自习排班编号
            WHERE 学院早自习安排编号 IN (
                SELECT 学院早自习安排编号 FROM 学院早自习安排 WHERE 自习日期 BETWEEN '{xxx}' and '{xxx}'
            ) and 最终检查者学号='{$personID}';";
// 选择某个人（组长）所在组的所有组员查早安排与数据
$sql = "SELECT * FROM 查早排班 LEFT JOIN 查早缺勤表 on 学院早自习安排编号 = 早自习排班编号
            WHERE 学院早自习安排编号 IN (
                SELECT 学院早自习安排编号 FROM 学院早自习安排 WHERE 自习日期 BETWEEN '{xxx}' and '{xxx}'
            ) and 最终检查者学号='{$personID}';";
// 选择某个人（组长）所在组的所有组员缺勤表安排与数据
// 选择全队所有人的查早安排与数据
// 选择全队所有人的缺勤表安排与数据

//// private function: check if classroom data is valid
///**
// * @return void
// */
//private function check_data(): void
//{ // 检查数据合法性
//    if (!function_exists('studentID_filter')) {
//        function studentID_filter($studentID) { // 学号过滤器
//            $pattern = "/^20[1-3]\d[a-zA-Z0-9]{8,9}$/";
//            if(preg_match($pattern,$studentID)) {
//                return $studentID;
//            }
//
//            return false;
//        }
//    }
//
//    if (!function_exists('campus_filter')){
//        function campus_filter($campus) { // 校区过滤器
//            $pattern = '/^(清水河)|(沙河)$/u';
//            if(preg_match($pattern,$campus)) {
//                return $campus;
//            }
//
//            return false;
//        }
//    }
//
//    if (!function_exists('building_filter')){
//        function building_filter($building) { // 教学楼过滤器
//            $pattern = '/^(品学楼)|(立人楼)|(第一教学楼)|(第二教学楼)$/u';
//            if(preg_match($pattern,$building)) {
//                return $building;
//            }
//
//            return false;
//        }
//    }
//
//    if (!function_exists('area_filter')){
//        function area_filter($area) { // 区号过滤器
//            $pattern = '/^[ABC-]$/u';
//            if(preg_match($pattern,$area)) {
//                return $area;
//            }
//
//            return false;
//        }
//    }
//
//    if (!function_exists('room_filter')){
//        function room_filter($room) { // 教室编号过滤器
//            $pattern = '/^[1-6][0-2]\d$/u';
//            if(preg_match($pattern,$room)) {
//                return $room;
//            }
//
//            return false;
//        }
//    }
//
//    if (!function_exists('date_filter')){
//        function date_filter($date) { // 日期过滤器
//            $test = new DateTools($date);
//            if ($test->is_correct()===true) {
//                return $test->based_datetime()->format('Y-m-d');
//            }
//
//            return false;
//        }
//    }
//
//    $filters_basic_info = array
//    (
//        '校区' => array
//        (
//            'filter' =>FILTER_CALLBACK,
//            'options' => 'campus_filter'
//        ),
//        '教学楼' => array
//        (
//            'filter' =>FILTER_CALLBACK,
//            'options' => 'building_filter'
//        ),
//        '区号' => array
//        (
//            'filter' =>FILTER_CALLBACK,
//            'options' => 'area_filter'
//        ),
//        '教室编号' => array
//        (
//            'filter' =>FILTER_CALLBACK,
//            'options' => 'room_filter'
//        ),
//        '日期' => array
//        (
//            'filter' =>FILTER_CALLBACK,
//            'options' => 'date_filter'
//        ),
//        '时段与上课周' => FILTER_SANITIZE_STRING,
//        '编号' => array
//        (
//            'filter' =>FILTER_VALIDATE_INT,
//            'options' =>array (
//                'min_range' =>1,
//                'max_range' =>150
//            )
//        )
//    );
//    $filters_uploader = array
//    (
//        '提交者姓名' => FILTER_SANITIZE_STRING,
//        '提交者学号' => array (
//            'filter' =>FILTER_CALLBACK,
//            'options' => 'studentID_filter'
//        )
//    );
//    $filters_data = array
//    (
//        '第一次出勤' => array
//        (
//            'filter' =>FILTER_VALIDATE_INT,
//            'options' =>array (
//                'min_range' =>0,
//                'max_range' =>500
//            )
//        ),
//        '第二次出勤' => array
//        (
//            'filter' =>FILTER_VALIDATE_INT,
//            'options' =>array (
//                'min_range' =>0,
//                'max_range' =>500
//            )
//        ),
//        '第一次违纪' => array
//        (
//            'filter' =>FILTER_VALIDATE_INT,
//            'options' =>array (
//                'min_range' =>0,
//                'max_range' =>500
//            )
//        ),
//        '第二次违纪' => array
//        (
//            'filter' =>FILTER_VALIDATE_INT,
//            'options' =>array (
//                'min_range' =>0,
//                'max_range' =>500
//            )
//        ),
//        '应到人数' => array
//        (
//            'filter' =>FILTER_VALIDATE_INT,
//            'options' =>array (
//                'min_range' =>0,
//                'max_range' =>500
//            )
//        ),
//        '学院' => FILTER_SANITIZE_STRING,
//        '年级' => array
//        (
//            'filter' =>FILTER_VALIDATE_INT,
//            'options' =>array (
//                'min_range' =>2015,
//                'max_range' =>2116
//            )
//        ),
//        '课程名称' => FILTER_SANITIZE_STRING,
//        '备注' => array
//        (
//            'filter' =>FILTER_SANITIZE_STRING
//        )
//    );
//
//    $data_basic_info = array
//    (
//        '校区' => $this->campus, // 校区
//        '教学楼' => $this->building, // 教学楼
//        '区号' => $this->area, // 区号
//        '教室编号' => $this->room, // 教室编号
//        '日期' => $this->date, // 日期
//        '时段与上课周' =>$this->timeAndPeriod, // 时段与上课周
//        '编号' => $this->order // 编号
//    );
//    $data_uploader = array
//    (
//        '提交者姓名' => $this->uploadPersonName, // 提交者姓名
//        '提交者学号' => $this->uploadPersonStudentID // 提交者学号
//    );
//    $data_data = array
//    (
//        '学院' => $this->school, // 学院
//        '年级' => $this->grade, // 年级
//        '课程名称' => $this->course, // 课程名称
//        '应到人数' => $this->numShouldHave, // 应到人数
//        '第一次出勤' => $this->firstAttendance, // 第一次出勤
//        '第二次出勤' => $this->secondAttendance, // 第二次出勤
//        '第一次违纪' => $this->firstViolationOfDiscipline, // 第一次违纪人数
//        '第二次违纪' => $this->secondViolationOfDiscipline, // 第二次违纪人数
//        '备注' => $this->remark // 备注
//    );
//
//    $filter_result_basic_info = filter_var_array($data_basic_info, $filters_basic_info);
//    $filter_result_uploader = filter_var_array($data_uploader, $filters_uploader);
//    $filter_result_data = filter_var_array($data_data, $filters_data);
//
//    foreach($filter_result_basic_info as $key=>$value) {
//        $this->informationValid['basic_info'] = true;
//        if ($value===false) {
//            $this->informationValid['basic_info'] = false;
//            $this->invalid_infos[$key] = $value;
//        }
//    }
//    foreach($filter_result_uploader as $key=>$value) {
//        $this->informationValid['uploadPerson'] = true;
//        if ($value===false) {
//            $this->informationValid['uploadPerson'] = false;
//            $this->invalid_infos[$key] = $value;
//        }
//    }
//    foreach($filter_result_data as $key=>$value) {
//        $this->informationValid['data'] = true;
//        if ($value===false) {
//            $this->informationValid['data'] = false;
//            $this->invalid_infos[$key] = $value;
//        }
//    }
//    // 品学楼、立人楼必须要有区域编号
//    if ($this->building==='品学楼' && $this->building==='立人楼' && $this->area==='-') {
//        $this->informationValid['basic_info'] = false;
//        $this->invalid_infos['区号'] = $this->area;
//    }
//    // 校区和教学楼名称对应
//    if ($this->campus==='沙河') {
//        if ($this->building==='品学楼' || $this->building==='立人楼') {
//            $this->informationValid['basic_info'] = false;
//            $this->invalid_infos['教学楼'] = $this->building;
//        }
//    }
//
//    $this->informationValid['all'] = (
//        $this->informationValid['basic_info']
//        and $this->informationValid['data']
//        and $this->informationValid['uploadPerson']
//    );
//}