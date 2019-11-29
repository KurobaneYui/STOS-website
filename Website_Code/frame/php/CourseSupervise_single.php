<?php
require __DIR__ . '/../../ROOT_PATH.php';
require ROOT_PATH . '/frame/php/Database_connector.php';
require ROOT_PATH . '/frame/php/DateTools.php';

if (!class_exists('CourseSupervise_single')) {
    class CourseSupervise_single
    {
        // 数据库连接
        private $STOS_DATABASE_INFORMATION; // database connection
        private $STOS_DATABASE_COLLECTION_DATA; // database connection

        // 时间工具
        private $DATE_TOOL; // date tool

        // 状态量
        private $informationValid = array(
            'all' => false,
            'basic_info' =>false,
            'uploadPerson' => false,
            'data' => false
        ); // check if personal information is all valid
        private $invalid_infos = array(); // show the information which is invalid
        private $existInDatabase = false; // show if this person already exists in the database

        // 提交者信息
        private $uploadPersonName = ''; // 提交者姓名
        private $uploadPersonStudentID = ''; // 提交者学号
        // 教室信息
        private $date = ''; // 数据对应日期，请使用PHP的data函数将格式调整为"Y-m-d"
        private $timeAndPeriod = ''; // 时段与上课周
        private $campus = ''; // 校区
        private $building = ''; // 教学楼
        private $area = ''; // 区号
        private $room = ''; // 教室编号
        private $order = 1; // 编号
        // 数据部分
        private $school = ''; // 教室对应学院
        private $grade = ''; // 教室对应年级
        private $course = ''; // 教室课程名称
        private $numShouldHave = 0; // 教室应到人数
        private $firstAttendance = 0; // 单日第一次出勤
        private $secondAttendance = 0; // 单日第二次出勤
        private $firstViolationOfDiscipline = 0; // 单日第一次违纪人数
        private $secondViolationOfDiscipline = 0; // 单日第二次违纪人数
        private $remark = ''; // 备注

        /**
         * SelfStudySupervise_single constructor.
         * @param string $date : 数据对应日期，请使用PHP的data函数将格式调整为"Y-m-d"
         * @param string $timeAndPeriod : 时段与上课周
         * @param string $campus : 校区
         * @param string $building : 教学楼
         * @param string $area : 区号
         * @param string $room : 教室编号
         * @param int $order : 编号
         * @throws Exception
         */
        public function __construct(string $date = '', string $timeAndPeriod = '', string $campus = '', string $building = '', string $area = '', string $room = '', int $order = 1)
        {
            $this->STOS_DATABASE_INFORMATION = new Database_connector(ROOT_PATH.'/config/DataBase_Information.conf');
            $this->STOS_DATABASE_COLLECTION_DATA = new Database_connector(ROOT_PATH.'/config/DataBase_CollectionData.conf');
            $this->DATE_TOOL = new DateTools();

            if ($date === '' || $timeAndPeriod==='' || $building === '' || $area === '' || $room === '' || $campus === '' || $order === 1) {
                if ($date !== '' || $timeAndPeriod!=='' || $building !== '' || $area !== '' || $room !== '' || $campus !== '' || $order !== 1) {
                    throw new Exception('Invalid parameter');
                }

                $this->existInDatabase
                    = $this->informationValid['all']
                    = $this->informationValid['basic_info']
                    = $this->informationValid['uploadPerson']
                    = $this->informationValid['data']
                    = false;
            }
            else {
                $this->date = $date;
                $this->timeAndPeriod = $timeAndPeriod;
                $this->campus = $campus;
                $this->building = $building;
                $this->area = $area;
                $this->room = $room;
                $this->order = $order;

                $result = $this->STOS_DATABASE_COLLECTION_DATA->search(
                    '查课数据',
                    array(
                        '校区'=>$this->campus,
                        '教学楼'=>$this->building,
                        '区号'=>$this->area,
                        '教室编号'=>$this->room,
                        '日期'=>$this->date,
                        '时段与上课周'=>$this->timeAndPeriod,
                        '编号'=>$this->order
                    )
                );
                if($result = $result->fetch_assoc()) {
                    $this->uploadPersonName = $result['提交者姓名'];
                    $this->uploadPersonStudentID = $result['提交者学号'];

                    $result = json_decode($result['教室数据'],true);
                    $this->course = $result['课程名称'];
                    $this->school = $result['学院'];
                    $this->grade = $result['年级'];
                    $this->firstAttendance = $result['第一次出勤'];
                    $this->secondAttendance = $result['第二次出勤'];
                    $this->firstViolationOfDiscipline = $result['第一次违纪'];
                    $this->secondViolationOfDiscipline = $result['第二次违纪'];
                    $this->remark = $result['备注'];

//                    $schoolAndNumShouldHave = $this->STOS_DATABASE_COLLECTION_DATA->search(
//                        '查课排班',
//                        array('应到人数', '学院', '年级'),
//                        array(
//                            '日期'=>$this->date,
//                            '校区'=>$this->campus,
//                            '教学楼'=>$this->building,
//                            '区号'=>$this->area,
//                            '教室编号'=>$this->room,
//                            '编号'=>$this->order
//                        ),
//                        array(),
//                        array(),
//                        array(),
//                        array()
//                    )->fetch_assoc();
//                    $this->numShouldHave = $result['应到人数'] ?? $schoolAndNumShouldHave['应到人数'];
//                    $this->school = $result['学院'] ?? $schoolAndNumShouldHave['学院'];

                    $this->existInDatabase = true;
                    $this->informationValid['all']
                        = $this->informationValid['basic_info']
                        = $this->informationValid['uploadPerson']
                        = $this->informationValid['data']
                        =true;
                    $this->invalid_infos = array();

                    return;
                }

                $this->existInDatabase = false;
                $this->informationValid['all']
                    = $this->informationValid['basic_info']
                    = $this->informationValid['uploadPerson']
                    = $this->informationValid['data']
                    =false;
                $this->invalid_infos = array();
            }
        }

        public function __destruct()
        {
            $this->STOS_DATABASE_INFORMATION->__destruct();
            $this->STOS_DATABASE_COLLECTION_DATA->__destruct();
            $this->DATE_TOOL->__destruct();
        }

        // public function: check if this classroom data already in database
        /**
         * @return bool
         */
        public function exist(): bool { // 返回是否已经在数据库中存在
            return $this->existInDatabase;
        }

        // private function: check if classroom data is valid
        /**
         * @return void
         */
        private function check_data(): void
        { // 检查数据合法性
            if (!function_exists('studentID_filter')) {
                function studentID_filter($studentID) { // 学号过滤器
                    $pattern = "/^20[1-3]\d[a-zA-Z0-9]{8,9}$/";
                    if(preg_match($pattern,$studentID)) {
                        return $studentID;
                    }

                    return false;
                }
            }

            if (!function_exists('campus_filter')){
                function campus_filter($campus) { // 校区过滤器
                    $pattern = '/^(清水河)|(沙河)$/u';
                    if(preg_match($pattern,$campus)) {
                        return $campus;
                    }

                    return false;
                }
            }

            if (!function_exists('building_filter')){
                function building_filter($building) { // 教学楼过滤器
                    $pattern = '/^(品学楼)|(立人楼)|(第一教学楼)|(第二教学楼)$/u';
                    if(preg_match($pattern,$building)) {
                        return $building;
                    }

                    return false;
                }
            }

            if (!function_exists('area_filter')){
                function area_filter($area) { // 区号过滤器
                    $pattern = '/^[ABC-]$/u';
                    if(preg_match($pattern,$area)) {
                        return $area;
                    }

                    return false;
                }
            }

            if (!function_exists('room_filter')){
                function room_filter($room) { // 教室编号过滤器
                    $pattern = '/^[1-6][0-2]\d$/u';
                    if(preg_match($pattern,$room)) {
                        return $room;
                    }

                    return false;
                }
            }

            if (!function_exists('date_filter')){
                function date_filter($date) { // 日期过滤器
                    $test = new DateTools($date);
                    if ($test->is_correct()===true) {
                        return $test->based_datetime()->format('Y-m-d');
                    }

                    return false;
                }
            }

            $filters_basic_info = array
            (
                '校区' => array
                (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'campus_filter'
                ),
                '教学楼' => array
                (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'building_filter'
                ),
                '区号' => array
                (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'area_filter'
                ),
                '教室编号' => array
                (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'room_filter'
                ),
                '日期' => array
                (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'date_filter'
                ),
                '时段与上课周' => FILTER_SANITIZE_STRING,
                '编号' => array
                (
                    'filter' =>FILTER_VALIDATE_INT,
                    'options' =>array (
                        'min_range' =>1,
                        'max_range' =>150
                    )
                )
            );
            $filters_uploader = array
            (
                '提交者姓名' => FILTER_SANITIZE_STRING,
                '提交者学号' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'studentID_filter'
                )
            );
            $filters_data = array
            (
                '第一次出勤' => array
                (
                    'filter' =>FILTER_VALIDATE_INT,
                    'options' =>array (
                        'min_range' =>0,
                        'max_range' =>500
                    )
                ),
                '第二次出勤' => array
                (
                    'filter' =>FILTER_VALIDATE_INT,
                    'options' =>array (
                        'min_range' =>0,
                        'max_range' =>500
                    )
                ),
                '第一次违纪' => array
                (
                    'filter' =>FILTER_VALIDATE_INT,
                    'options' =>array (
                        'min_range' =>0,
                        'max_range' =>500
                    )
                ),
                '第二次违纪' => array
                (
                    'filter' =>FILTER_VALIDATE_INT,
                    'options' =>array (
                        'min_range' =>0,
                        'max_range' =>500
                    )
                ),
                '应到人数' => array
                (
                    'filter' =>FILTER_VALIDATE_INT,
                    'options' =>array (
                        'min_range' =>0,
                        'max_range' =>500
                    )
                ),
                '学院' => FILTER_SANITIZE_STRING,
                '年级' => array
                (
                    'filter' =>FILTER_VALIDATE_INT,
                    'options' =>array (
                        'min_range' =>2015,
                        'max_range' =>2116
                    )
                ),
                '课程名称' => FILTER_SANITIZE_STRING,
                '备注' => array
                (
                    'filter' =>FILTER_SANITIZE_STRING
                )
            );

            $data_basic_info = array
            (
                '校区' => $this->campus, // 校区
                '教学楼' => $this->building, // 教学楼
                '区号' => $this->area, // 区号
                '教室编号' => $this->room, // 教室编号
                '日期' => $this->date, // 日期
                '时段与上课周' =>$this->timeAndPeriod, // 时段与上课周
                '编号' => $this->order // 编号
            );
            $data_uploader = array
            (
                '提交者姓名' => $this->uploadPersonName, // 提交者姓名
                '提交者学号' => $this->uploadPersonStudentID // 提交者学号
            );
            $data_data = array
            (
                '学院' => $this->school, // 学院
                '年级' => $this->grade, // 年级
                '课程名称' => $this->course, // 课程名称
                '应到人数' => $this->numShouldHave, // 应到人数
                '第一次出勤' => $this->firstAttendance, // 第一次出勤
                '第二次出勤' => $this->secondAttendance, // 第二次出勤
                '第一次违纪' => $this->firstViolationOfDiscipline, // 第一次违纪人数
                '第二次违纪' => $this->secondViolationOfDiscipline, // 第二次违纪人数
                '备注' => $this->remark // 备注
            );

            $filter_result_basic_info = filter_var_array($data_basic_info, $filters_basic_info);
            $filter_result_uploader = filter_var_array($data_uploader, $filters_uploader);
            $filter_result_data = filter_var_array($data_data, $filters_data);

            foreach($filter_result_basic_info as $key=>$value) {
                $this->informationValid['basic_info'] = true;
                if ($value===false) {
                    $this->informationValid['basic_info'] = false;
                    $this->invalid_infos[$key] = $value;
                }
            }
            foreach($filter_result_uploader as $key=>$value) {
                $this->informationValid['uploadPerson'] = true;
                if ($value===false) {
                    $this->informationValid['uploadPerson'] = false;
                    $this->invalid_infos[$key] = $value;
                }
            }
            foreach($filter_result_data as $key=>$value) {
                $this->informationValid['data'] = true;
                if ($value===false) {
                    $this->informationValid['data'] = false;
                    $this->invalid_infos[$key] = $value;
                }
            }
            // 品学楼、立人楼必须要有区域编号
            if ($this->building==='品学楼' && $this->building==='立人楼' && $this->area==='-') {
                $this->informationValid['basic_info'] = false;
                $this->invalid_infos['区号'] = $this->area;
            }
            // 校区和教学楼名称对应
            if ($this->campus==='沙河') {
                if ($this->building==='品学楼' || $this->building==='立人楼') {
                    $this->informationValid['basic_info'] = false;
                    $this->invalid_infos['教学楼'] = $this->building;
                }
            }

            $this->informationValid['all'] = (
                $this->informationValid['basic_info']
                and $this->informationValid['data']
                and $this->informationValid['uploadPerson']
            );
        }

        // public function: commit classroom all information include uploader information and classroom data
        /**
         * @return bool
         */
        public function commit_classroom_all(): bool {
            $this->check_data();

            if($this->informationValid['all']) {
                if ($this->existInDatabase) {
                    return $this->commit_classroom_data() and $this->commit_classroom_uploader();
                }

                $data = array
                (
                    '校区' => $this->campus, // 校区
                    '教学楼' => $this->building, // 教学楼
                    '区号' => $this->area, // 区号
                    '教室编号' => $this->room, // 教室编号
                    '日期' => $this->date, // 日期
                    '时段与上课周' =>$this->timeAndPeriod, // 时段与上课周
                    '编号' => $this->order, // 编号

                    '提交者姓名' => $this->uploadPersonName, // 提交者姓名
                    '提交者学号' => $this->uploadPersonStudentID, // 提交者学号

                    '学院' => $this->school, // 学院
                    '年级' => $this->grade, // 年级
                    '课程名称' => $this->course, // 课程名称
                    '应到人数' => $this->numShouldHave, // 应到人数
                    '第一次出勤' => $this->firstAttendance, // 第一次出勤
                    '第二次出勤' => $this->secondAttendance, // 第二次出勤
                    '第一次违纪' => $this->firstViolationOfDiscipline, // 第一次违纪人数
                    '第二次违纪' => $this->secondViolationOfDiscipline, // 第二次违纪人数
                    '备注' => $this->remark // 备注
                );

                $this->STOS_DATABASE_INFORMATION->insert(
                    '查课数据',
                    $data
                );

                return true;
            }

            return false;
        }

        // public function: commit classroom data
        /**
         * @return bool
         */
        public function commit_classroom_data(): bool {
            $this->check_data();

            if($this->informationValid['data'] && $this->informationValid['basic_info']) {
                $data = array
                (
                    '学院' => $this->school, // 学院
                    '年级' => $this->grade, // 年级
                    '课程名称' => $this->course, // 课程名称
                    '应到人数' => $this->numShouldHave, // 应到人数
                    '第一次出勤' => $this->firstAttendance, // 第一次出勤
                    '第二次出勤' => $this->secondAttendance, // 第二次出勤
                    '第一次违纪' => $this->firstViolationOfDiscipline, // 第一次违纪人数
                    '第二次违纪' => $this->secondViolationOfDiscipline, // 第二次违纪人数
                    '备注' => $this->remark // 备注
                );
                if ($this->existInDatabase) {
                    $this->STOS_DATABASE_INFORMATION->update(
                        '查课数据',
                        $data,
                        array(
                            '校区' => $this->campus,
                            '教学楼' => $this->building,
                            '区号' => $this->area,
                            '教室编号' => $this->room,
                            '日期' => $this->date,
                            '时段与上课周' =>$this->timeAndPeriod,
                            '编号' => $this->order
                        )
                    );

                    return true;
                }

                return $this->commit_classroom_all();
            }

            return false;
        }

        // public function: commit classroom uploader information
        /**
         * @return bool
         */
        public function commit_classroom_uploader(): bool {
            $this->check_data();

            if($this->informationValid['uploadPerson'] && $this->informationValid['basic_info']) {
                $data = array
                (
                    '提交者姓名' => $this->uploadPersonName, // 提交者姓名
                    '提交者学号' => $this->uploadPersonStudentID // 提交者学号
                );
                if ($this->existInDatabase) {
                    $this->STOS_DATABASE_INFORMATION->update(
                        '查课数据',
                        $data,
                        array(
                            '校区' => $this->campus,
                            '教学楼' => $this->building,
                            '区号' => $this->area,
                            '教室编号' => $this->room,
                            '日期' => $this->date,
                            '时段与上课周' =>$this->timeAndPeriod,
                            '编号' => $this->order
                        )
                    );

                    return true;
                }

                return $this->commit_classroom_all();
            }

            return false;
        }

        // provide classroom data
        /**
         * @return array
         */
        public function classroom_data(): array {
            $data = array();

            if ($this->informationValid['data']) {
                $data = array
                (
                    '第一次出勤' =>$this->firstAttendance, // 第一次出勤
                    '第二次出勤' =>$this->secondAttendance, // 第二次出勤
                    '第一次违纪' =>$this->firstViolationOfDiscipline, // 第一次违纪人数
                    '第二次违纪' =>$this->secondViolationOfDiscipline, // 第二次违纪人数
                    '学院' =>$this->school, // 学院
                    '年级' =>$this->grade, // 年级
                    '课程名称' =>$this->course, // 课程名称
                    '应到人数' =>$this->numShouldHave, // 应到人数
                    '备注' =>$this->remark // 备注
                );
            }

            return ($data);
        }

        // provide classroom uploader information
        /**
         * @return array
         */
        public function classroom_uploader(): array {
            $data = array();

            if ($this->informationValid['uploadPerson']) {
                $data = array
                (
                    '提交者姓名' =>$this->uploadPersonName, // 提交者姓名
                    '提交者学号' =>$this->uploadPersonStudentID, // 提交者学号
                );
            }

            return ($data);
        }

        // provide classroom information
        /**
         * @return array
         */
        public function classroom_info(): array {
            $data = array
            (
                '校区' =>$this->campus, // 校区
                '教学楼' =>$this->building, // 教学楼
                '区号' =>$this->area, // 区号
                '教室编号' =>$this->room, // 教室编号
                '日期' =>$this->date, // 日期
                '时段与上课周' =>$this->timeAndPeriod, // 时段与上课周
                '编号' => $this->order // 编号
            );

            return ($data);
        }

        // public function: set classroom data or uploader information
        /**
         * @param array $info_pairs: the information need to set, please give array of key-value pairs of information
         * @return bool
         */
        public function change_info(array $info_pairs): bool {
            foreach ($info_pairs as $key=>$value) {
                switch ($key) {
                    // 提交者信息
                    case '提交者姓名': $this->uploadPersonName = $value;break; // 提交者姓名
                    case '提交者学号': $this->uploadPersonStudentID = $value;break; // 提交者学号
                    // 教室数据
                    case '学院': $this->school = $value;break; // 学院
                    case '年级': $this->grade = $value;break; // 年级
                    case '课程名称': $this->course = $value;break; // 课程名称
                    case '应到人数': $this->numShouldHave = $value;break; // 应到人数
                    case '第一次出勤': $this->firstAttendance = $value;break; // 第一次出勤
                    case '第二次出勤': $this->secondAttendance = $value;break; // 第二次出勤
                    case '第一次违纪': $this->firstViolationOfDiscipline = $value;break; // 第一次违纪人数
                    case '第二次违纪': $this->secondViolationOfDiscipline = $value;break; // 第二次违纪人数
                    case '备注': $this->remark = $value;break; // 备注
                    default: return false;
                }
            }
            return true;
        }
    }
}