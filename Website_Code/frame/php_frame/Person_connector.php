<?php
require ('/var/www/html/ROOT_PATH.php');
require (ROOT_PATH.'/frame/php_frame/Database_connector.php');
require (ROOT_PATH.'/frame/php_frame/DateTools.php');
require (ROOT_PATH.'/frame/php_frame/AuthorizationTools.php');

if (!class_exists('Person_connector')) {
    class Person_connector
    {
        private $STOS_DATABASE_INFORMATION; // database connection
        private $STOS_DATABASE_COLLECTION_DATA; // database connection

        private $informationValid = array(
            'all' => false,
            'basic' => false,
            'work' => false,
            'password' => false,
            '$authorization' => false
        ); // check if personal information is all valid
        private $invalid_infos = array(); // show the information which is invalid
        private $existInDatabase = false; // show if this person already exists in the database

        // 岗位信息
        private $groupBelonging; // 所属组
        private $work; // 岗位
        private $wage = 0; // 工资
        private $groupManagement = array(); // 管理组（一位副队同时管理早餐组，故设此变量记录除职位外的管理身份）
        private $MondayEmptyTime; // 周一空课
        private $TuesdayEmptyTime; // 周二空课
        private $WednesdayEmptyTime; // 周三空课
        private $ThursdayEmptyTime; // 周四空课
        private $FridayEmptyTime; // 周五空课
        private $remark = array(); // 备注
        // 个人信息
        private $name; // 姓名
        private $campus; // 校区
        private $school; // 学院
        private $studentID; // 学号
        private $gender; // 性别
        private $peoples; // 民族
        private $hometown; // 籍贯
        private $phoneNumber; // 电话
        private $QQ; // QQ号
        private $dormitory_yuan; // 寝室_苑
        private $dormitory_lou; // 寝室_楼
        private $dormitory_hao; // 寝室_号
        // 工资申报信息
        private $bankIDForWage; // 工资申请时银行卡号
        private $nameForWage; // 工资申请时姓名
        private $studentIDForWage; // 工资申请时学号
        private $subsidyDossier; // 建档立卡
        // 密码与权限
        private $password; // 密码
        private $authorization; // 权限

        /**
         * Person_connector constructor.
         * @param string $studentID: Automatically get personal information if given studentID
         */
        public function __construct(string $studentID='') {
            $this->STOS_DATABASE_INFORMATION = new Database_connector(ROOT_PATH.'/config/DataBase_Information.conf');
            $this->STOS_DATABASE_COLLECTION_DATA = new Database_connector(ROOT_PATH.'/config/DataBase_CollectionData.conf');

            if($studentID!==''){
                $this->existInDatabase = $this->InitWithStudentID($studentID);
                $this->informationValid['all'] = $this->informationValid['basic'] = $this->informationValid['work'] =
                $this->informationValid['password'] = $this->informationValid['authorization'] = $this->existInDatabase;
            }
        }

        public function __destruct() {
            unset($this->STOS_DATABASE_COLLECTION_DATA, $this->STOS_DATABASE_INFORMATION);
        }

        // public function: Initialize personal information with given studentID
        /**
         * @param string $studentID
         * @return bool
         */
        public function InitWithStudentID(string $studentID): bool {
            $this->studentID = $studentID;
            $this->existInDatabase = $this->fetch_all_personal_info();
            $this->informationValid['all'] = $this->informationValid['basic'] = $this->informationValid['work'] =
            $this->informationValid['password'] = $this->informationValid['authorization'] = $this->existInDatabase;
            return $this->existInDatabase;
        }

        // public function: use name prop to get all personal information
        /**
         * @return bool
         */
        public function fetch_all_personal_info(): bool {
            $this->informationValid['all']
                = $this->fetch_basic_info()
                and $this->fetch_work_info()
                and $this->fetch_password_info()
                and $this->fetch_authorization_info();

            $this->existInDatabase = $this->informationValid['all'];
            return $this->informationValid['all'];
        }

        // public function: use name prop to get personal basic information
        /**
         * @return bool
         */
        public function fetch_basic_info(): bool {
            $this->informationValid['basic'] = false;

            $result = $this->STOS_DATABASE_INFORMATION->search('成员信息',array(),array('学号'=>$this->studentID));
            if($result = $result->fetch_assoc()) {
                $this->name = $result['姓名'];
                $this->gender = $result['性别'];
                $this->campus = $result['校区'];
                $this->school = $result['学院'];
                $this->peoples = $result['民族'];
                $this->hometown = $result['籍贯'];
                $this->phoneNumber = $result['电话'];
                $this->QQ = $result['QQ'];
                $this->bankIDForWage = $result['工资申请时银行卡号'];
                $this->nameForWage = $result['工资申请时姓名'];
                $this->studentIDForWage = $result['工资申请时学号'];
                $this->dormitory_yuan = explode('-', $result['寝室号'])[0];
                $this->dormitory_lou = explode('-', $result['寝室号'])[1];
                $this->dormitory_hao = explode('-', $result['寝室号'])[2];

                $this->informationValid['basic'] = true;
                return true;
            }

            $this->informationValid['all'] = false;
            return false;
        }

        // public function: use name prop to get personal work information
        /**
         * @return bool
         */
        public function fetch_work_info(): bool {
            $this->informationValid['work'] = false;

            $result = $this->STOS_DATABASE_INFORMATION->search('成员岗位',array(),array('学号'=>$this->studentID));
            if ($result = $result->fetch_assoc()) {
                $this->MondayEmptyTime = $result['周一空课'];
                $this->TuesdayEmptyTime = $result['周二空课'];
                $this->WednesdayEmptyTime = $result['周三空课'];
                $this->ThursdayEmptyTime = $result['周四空课'];
                $this->FridayEmptyTime = $result['周五空课'];
                $this->groupBelonging = $result['所属组'];
                $this->work = $result['岗位'];
                $this->wage = $result['工资'];
                $this->remark = json_decode($result['备注'], true);

                $result = $this->STOS_DATABASE_INFORMATION->search('部门信息', array('部门','组长'), array('组长' =>$this->studentID));
                $this->groupManagement = array();
                while($result = $result->fetch_assoc()) {
                    $this->groupManagement[] = $result['部门'];
                    if($this->groupBelonging === '队长' && $result['部门'] !== '队长') {
                        $this->groupManagement[] = '队长';
                    }
                }

                $this->informationValid['work'] = true;
                return true;
            }

            $this->informationValid['all'] = false;
            return false;
        }

        // public function: use name prop to get personal password information
        /**
         * @return bool
         */
        public function fetch_password_info(): bool {
            $this->informationValid['password'] = false;

            $result = $this->STOS_DATABASE_INFORMATION->search('登录信息',array('密码'),array('学号'=>$this->studentID));
            if ($result = $result->fetch_assoc()) {
                $this->password = $result['密码'];

                $this->informationValid['password'] = true;
                return true;
            }

            $this->informationValid['all'] = false;
            return false;
        }

        // public function: use name prop to get personal authorization information
        /**
         * @return bool
         */
        public function fetch_authorization_info(): bool {
            $this->informationValid['authorization'] = false;

            $result = $this->STOS_DATABASE_INFORMATION->search('权限信息', array('权限'), array('学号'=>$this->studentID));
            if($result = $result->fetch_assoc()) {
                $this->authorization = $result['权限'];

                $this->informationValid['authorization'] = true;
                return true;
            }

            $this->informationValid['all'] = false;
            return false;
        }

        // private function: check if personal information are valid
        // ！！！！ “学院、民族、籍贯、备注” 只去除特殊标签和字符； “权限” 未检测！！！
        /**
         * @param string $range: only "all","basic","password","work","authorization" are available
         * @return void
         */
        private function check_data(string $range='all'): void { // check all information
            $result = $this->STOS_DATABASE_INFORMATION->search('部门信息',array('部门'));
            $GLOBALS['DEPARTMENTS'] = array();
            while($s = $result->fetch_assoc()['部门']) {
                $GLOBALS['DEPARTMENTS'][] = $s;
            }

            if (!function_exists('gender_filter')) {
                function gender_filter($gender) // 性别过滤器
                {
                    $pattern = '/^男|女$/u';
                    if(preg_match($pattern,$gender)) {
                        return $gender;
                    }

                    return false;
                }
            }

            if (!function_exists('studentID_filter')) {
                function studentID_filter($studentID) { // 学号过滤器
                    $pattern = "/^20[1-3]\d[a-zA-Z0-9]{8,9}$/";
                    if(preg_match($pattern,$studentID)) {
                        return $studentID;
                    }

                    return false;
                }
            }

            if (!function_exists('dormitory_filter')){
                function dormitory_filter($dormitory ) { // 寝室_苑过滤器
                    $pattern = '/^(学知苑)|(硕丰苑)|(博瀚苑)|(欣苑)|(校内)$/u';
                    if(preg_match($pattern,$dormitory)) {
                        return $dormitory;
                    }

                    return false;
                }
            }

            if (!function_exists('dormitory_num_filter')){
                function dormitory_num_filter( $dormitory_num ) { // 寝室_号过滤器
                    $pattern = "/^[1-6][0-8]\d$/";
                    if(preg_match($pattern,$dormitory_num)){
                        return $dormitory_num;
                    }

                    return false;
                }
            }

            if (!function_exists('bank_filter')){
                function bank_filter($bank) { // 银行卡号过滤器
                    if(strlen($bank) === 19) {
                        return filter_var($bank, FILTER_VALIDATE_INT);
                    }

                    return false;
                }
            }

            if (!function_exists('pw_filter')){
                function pw_filter($pw) { // 密码过滤器
                    $length = strlen($pw);
                    if($length>5 && $length<19 && filter_var($pw,FILTER_SANITIZE_EMAIL)===$pw) {
                        return filter_var($pw, FILTER_SANITIZE_EMAIL);
                    }

                    return false;
                }
            }

            if (!function_exists('empty_filter')){
                function empty_filter($empty_time){ // 空课时间过滤器
                $pattern = '/^[0-3]{4}$/';
                if(preg_match($pattern,$empty_time))
                {
                    return $empty_time;
                }

                    return false;
                }
            }

            if (!function_exists('work_filter')){
                function work_filter($work) { // 岗位过滤器
                    $pattern = '/^(预备队员)|(离队)|(组员)|(组长)|(队长)$/u';
                    if(preg_match($pattern,$work)) {
                        return $work;
                    }

                    return false;
                }
            }

            if (!function_exists('group_filter')){
                function group_filter($group) { // 所属组过滤器
                    if($group=== '') {
                        return $group;
                    }

                    foreach($GLOBALS['DEPARTMENTS'] as $value)
                    {
                        if($value === $group) {
                            return $group;
                        }
                    }

                    return false;
                }
            }

            if (!function_exists('group_m_filter')){
                function group_filter($group) { // 管理组过滤器
                    foreach ($group as $item) {
                        $if_valid = false;
                        foreach($GLOBALS['DEPARTMENTS'] as $value)
                        {
                            if($value === $item) {
                                $if_valid = true;
                            }
                        }
                        if(!$if_valid) {
                            return false;
                        }
                    }

                    return $group;
                }
            }

            if (!function_exists('money_filter')){
                function money_filter($money) { // 工资过滤器
                    return filter_var($money,FILTER_VALIDATE_INT,array('min_range' =>2000, 'max_range' =>0));
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

            if (!function_exists('subsidy_filter')) { // 建档立卡项过滤器
                function gender_filter($gender) // 性别过滤器
                {
                    $pattern = '/^是|否$/u';
                    if(preg_match($pattern,$gender)) {
                        return $gender;
                    }

                    return false;
                }
            }

            $filters = array
            (
                '姓名' => FILTER_SANITIZE_STRING,
                '性别' => array
                (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'gender_filter'
                ),
                '校区' => array
                (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'campus_filter'
                ),
                '学院' => FILTER_SANITIZE_STRING,
                '民族' => FILTER_SANITIZE_STRING,
                '籍贯' => FILTER_SANITIZE_STRING,
                'QQ' => FILTER_VALIDATE_INT,
                '电话' => array
                (
                    'filter' =>FILTER_VALIDATE_INT,
                    'options' =>array (
                        'min_range' =>10000000000,
                        'max_range' =>19999999999
                    )
                ),
                '寝室_苑' => array
                (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'dormitory_filter'
                ),
                '寝室_楼' => array (
                    'filter' =>FILTER_VALIDATE_INT,
                    'options' => array (
                        'min_range' =>1,
                        'max_range' =>30
                    )
                ),
                '寝室_号' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'dormitory_num_filter'
                ),
                '工资申请时银行卡号' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'bank_filter'
                ),
                '工资申请时姓名' => FILTER_SANITIZE_STRING,
                '工资申请时学号' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'studentID_filter'
                ),
                '学号' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'studentID_filter'
                ),
                '密码' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'pw_filter'
                ),
                '周一空课' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'empty_filter'
                ),
                '周二空课' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'empty_filter'
                ),
                '周三空课' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'empty_filter'
                ),
                '周四空课' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'empty_filter'
                ),
                '周五空课' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'empty_filter'
                ),
                '所属组' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'group_filter'
                ),
                '管理组' => array (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'group_m_filter'
                ),
                '岗位' => [
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'work_filter'
                ],
                '工资' => array
                (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'money_filter'
                ),
                '备注' => array
                (
                    'filter' =>FILTER_SANITIZE_STRING
                ),
                '建档立卡' => array
                (
                    'filter' =>FILTER_CALLBACK,
                    'options' => 'subsidy_filter'
                )
            );

            $invalid_infos = array();
            $data = array(
                '姓名' =>$this->name, // 姓名
                '校区' =>$this->campus, // 校区
                '学院' =>$this->school, // 学院
                '学号' =>$this->studentID, // 学号
                '性别' =>$this->gender, // 性别
                '民族' =>$this->peoples, // 民族
                '籍贯' =>$this->hometown, // 籍贯
                '电话' =>$this->phoneNumber, // 电话
                'QQ' =>$this->QQ, // QQ号
                '寝室_苑' => $this->dormitory_yuan, // 寝室_苑
                '寝室_楼' => $this->dormitory_lou, // 寝室_楼
                '寝室_号' => $this->dormitory_hao, // 寝室_号
                '工资申请时银行卡号' =>$this->bankIDForWage, // 工资申请时银行卡号
                '工资申请时姓名' =>$this->nameForWage, // 工资申请时姓名
                '工资申请时学号' =>$this->studentIDForWage, // 工资申请时学号
                '建档立卡' =>$this->subsidyDossier, // 建档立卡
                '密码' =>$this->password, // 密码
                '周一空课' =>$this->MondayEmptyTime, // 周一空课
                '周二空课' =>$this->TuesdayEmptyTime, // 周二空课
                '周三空课' =>$this->WednesdayEmptyTime, // 周三空课
                '周四空课' =>$this->ThursdayEmptyTime, // 周四空课
                '周五空课' =>$this->FridayEmptyTime, // 周五空课
                '所属组' =>$this->groupBelonging, // 所属组
                '管理组' =>$this->groupManagement, // 管理组
                '岗位' =>$this->work, // 岗位
                '工资' =>$this->wage, // 工资
                '备注' =>$this->remark // 备注
                );
            $result = filter_var_array($data, $filters);
            $this->invalid_infos = array();
            foreach($result as $key=>$value) {
                if (!$value) {
                    $this->informationValid = false;
                    $this->invalid_infos[$key] = $value;
                }
            }
            if(empty($invalid_infos)) {
                $this->informationValid = true;
                $this->invalid_infos = array();
            }
            else{
                $this->informationValid = false;
            }
        }

        // public function: commit personal information to database
        /**
         * @return bool
         */
        public function commit_all_personal_information(): bool {
            if($this->informationValid['all']) {
                return $this->commit_basic_information()
                    and $this->commit_work_information()
                    and $this->commit_authorization_information()
                    and $this->commit_password_information();
            }

            return false;
        }

        // public function: commit personal basic information to database
        /**
         * @return bool
         */
        public function commit_basic_information(): bool {
            $this->check_data('basic');

            if($this->informationValid['basic']) {
                $data = array
                (
                    '姓名' =>$this->name, // 姓名
                    '校区' =>$this->campus, // 校区
                    '学院' =>$this->school, // 学院
                    '性别' =>$this->gender, // 性别
                    '民族' =>$this->peoples, // 民族
                    '籍贯' =>$this->hometown, // 籍贯
                    '电话' =>$this->phoneNumber, // 电话
                    'QQ' =>$this->QQ, // QQ号
                    '寝室_苑' => $this->dormitory_yuan, // 寝室_苑
                    '寝室_楼' => $this->dormitory_lou, // 寝室_楼
                    '寝室_号' => $this->dormitory_hao, // 寝室_号
                    '工资申请时银行卡号' =>$this->bankIDForWage, // 工资申请时银行卡号
                    '工资申请时姓名' =>$this->nameForWage, // 工资申请时姓名
                    '工资申请时学号' =>$this->studentIDForWage, // 工资申请时学号
                    '建档立卡' =>$this->subsidyDossier // 建档立卡
                );
                if($this->existInDatabase) {
                    $this->STOS_DATABASE_INFORMATION->update('成员信息', $data,array('学号' =>$this->studentID));
                }
                else{
                    $data['学号'] = $this->studentID;
                    $this->STOS_DATABASE_INFORMATION->insert('成员信息', $data);
                }

                return true;
            }

            return false;
        }

        // public function: commit personal work information to database
        /**
         * @return bool
         */
        public function commit_work_information(): bool {
            $this->check_data('work');

            if($this->informationValid['work']) {
                $data = array
                (
                    '周一空课' =>$this->MondayEmptyTime, // 周一空课
                    '周二空课' =>$this->TuesdayEmptyTime, // 周二空课
                    '周三空课' =>$this->WednesdayEmptyTime, // 周三空课
                    '周四空课' =>$this->ThursdayEmptyTime, // 周四空课
                    '周五空课' =>$this->FridayEmptyTime, // 周五空课
                    '所属组' =>$this->groupBelonging, // 所属组
                    '管理组' =>$this->groupManagement, // 管理组
                    '岗位' =>$this->work, // 岗位
                    '工资' =>$this->wage, // 工资
                    '备注' =>json_encode( // 备注
                        str_replace(array("\n", "\r"), array("\\n", "\\r"), $this->remark),
                        JSON_UNESCAPED_UNICODE
                    )
                );
                if($this->existInDatabase) {
                    $this->STOS_DATABASE_INFORMATION->update('成员岗位', $data,array('学号' =>$this->studentID));
                }
                else{
                    $data['学号'] = $this->studentID;
                    $this->STOS_DATABASE_INFORMATION->insert('成员岗位', $data);
                }

                return true;
            }

            return false;
        }

        // public function: commit personal password information to database
        /**
         * @return bool
         */
        public function commit_password_information(): bool {
            $this->check_data('password');

            if($this->informationValid['password']) {
                $data = array
                (
                    '密码' =>$this->password // 密码
                );
                if($this->existInDatabase) {
                    $this->STOS_DATABASE_INFORMATION->update('登录信息', $data,array('学号' =>$this->studentID));
                }
                else{
                    $data['学号'] = $this->studentID;
                    $this->STOS_DATABASE_INFORMATION->insert('登录信息', $data);
                }

                return true;
            }

            return false;
        }

        // public function: commit personal authorization information to database
        /**
         * @return bool
         */
        public function commit_authorization_information(): bool {
            $this->check_data('authorization');

            if($this->informationValid['authorization']) {
                $data = array
                (
                    '权限' =>$this->authorization // 权限
                );
                if($this->existInDatabase) {
                    $this->STOS_DATABASE_INFORMATION->update('权限信息', $data,array('学号' =>$this->studentID));
                }
                else{
                    $data['学号'] = $this->studentID;
                    $this->STOS_DATABASE_INFORMATION->insert('权限信息', $data);
                }

                return true;
            }

            return false;
        }

        // public function: check if this person already in database
        /**
         * @return bool
         */
        public function exist(): bool { // 返回是否已经在数据库中存在
            return $this->existInDatabase;
        }

        // public function: provide personal work information
        /**
         * @return array
         */
        public function work_info(): array { // 返回工作岗位信息
            $data = array();

            if ($this->informationValid)['work'] {
                $data = array
                (
                    '周一空课' =>$this->MondayEmptyTime, // 周一空课
                    '周二空课' =>$this->TuesdayEmptyTime, // 周二空课
                    '周三空课' =>$this->WednesdayEmptyTime, // 周三空课
                    '周四空课' =>$this->ThursdayEmptyTime, // 周四空课
                    '周五空课' =>$this->FridayEmptyTime, // 周五空课
                    '所属组' =>$this->groupBelonging, // 所属组
                    '岗位' =>$this->work, // 岗位
                    '工资' =>$this->wage, // 工资
                    '备注' =>$this->remark, // 备注
                    '管理组' =>$this->groupManagement // 管理组
                );
            }

            return ($data);
        }

        // public function: provide personal basic information
        /**
         * @return array
         */
        public function basic_info(): array { // 返回基本信息
            $data = array();

            if ($this->informationValid['basic']) {
                $data = array
                (
                    '姓名' =>$this->name, // 姓名
                    '校区' =>$this->campus, // 校区
                    '学院' =>$this->school, // 学院
                    '性别' =>$this->gender, // 性别
                    '民族' =>$this->peoples, // 民族
                    '籍贯' =>$this->hometown, // 籍贯
                    '电话' =>$this->phoneNumber, // 电话
                    'QQ' =>$this->QQ, // QQ号
                    '寝室_苑' => $this->dormitory_yuan, // 寝室_苑
                    '寝室_楼' => $this->dormitory_lou, // 寝室_楼
                    '寝室_号' => $this->dormitory_hao, // 寝室_号
                    '工资申请时银行卡号' =>$this->bankIDForWage, // 工资申请时银行卡号
                    '工资申请时姓名' =>$this->nameForWage, // 工资申请时姓名
                    '工资申请时学号' =>$this->studentIDForWage, // 工资申请时学号
                    '建档立卡' =>$this->subsidyDossier // 建档立卡
                );
            }

            return ($data);
        }

        // public function: provide personal authorization
        /**
         * @return array
         */
        public function authorization_info(): array { // 返回权限信息
            $data = array();

            if ($this->informationValid['authorization']) {
                $data = array
                (
                    '权限' =>$this->authorization // 权限
                );
            }

            return ($data);
        }

        // public function: provide personal password
        /**
         * @return array
         */
        public function password_info(): array { // 返回密码
            $data = array();

            if ($this->informationValid['password']) {
                $data = array
                (
                    '密码' =>$this->password // 密码
                );
            }

            return ($data);
        }

        // public function: check user password
        /**
         * @param string $test_password: the password which need correction test
         * @return bool
         */
        public function password_check(string $test_password): bool { // 测试密码是否为数据库中的本人密码
            return $test_password===$this->password;
        }

        // public function: set personal information
        /**
         * @param array $info_pairs: the information need to set, please give array of key-value pairs of information
         * @return bool
         */
        public function change_info(array $info_pairs): bool {
            foreach ($info_pairs as $key=>$value) {
                switch ($key) {
                    // 岗位信息
                    case '所属组': $this->groupBelonging = $value;break; // 所属组
                    case '岗位': $this->work = $value;break; // 岗位
                    case '工资': $this->wage = $value;break; // 工资
                    case '管理组': $this->groupManagement = $value;break; // 管理组（一位副队同时管理早餐组，故设此变量记录除职位外的管理身份）
                    case '周一空课': $this->MondayEmptyTime = $value;break; // 周一空课
                    case '周二空课': $this->TuesdayEmptyTime = $value;break; // 周二空课
                    case '周三空课': $this->WednesdayEmptyTime = $value;break; // 周三空课
                    case '周四空课': $this->ThursdayEmptyTime = $value;break; // 周四空课
                    case '周五空课': $this->FridayEmptyTime = $value;break; // 周五空课
                    case '备注': $this->remark = $value;break; // 备注
                    // 个人信息
                    case '姓名': $this->name = $value;break; // 姓名
                    case '校区': $this->campus = $value;break; // 校区
                    case '学院': $this->school = $value;break; // 学院
                    case '性别': $this->gender = $value;break; // 性别
                    case '民族': $this->peoples = $value;break; // 民族
                    case '籍贯': $this->hometown = $value;break; // 籍贯
                    case '电话': $this->phoneNumber = $value;break; // 电话
                    case 'QQ': $this->QQ = $value;break; // QQ
                    case '寝室_苑': $this->dormitory_yuan = $value;break; // 寝室_苑
                    case '寝室_楼': $this->dormitory_lou = $value;break; // 寝室_楼
                    case '寝室_号': $this->dormitory_hao = $value;break; // 寝室_号
                    // 工资申报信息
                    case '工资申请时银行卡号': $this->bankIDForWage = $value;break; // 工资申请时银行卡号
                    case '工资申请时姓名': $this->nameForWage = $value;break; // 工资申请时姓名
                    case '工资申请时学号': $this->studentIDForWage = $value;break; // 工资申请时学号
                    case '建档立卡': $this->subsidyDossier = $value;break; // 建档立卡
                    // 密码与权限
                    case '密码': $this->password = $value;break; // 密码
                    case '权限': $this->authorization = $value;break; // 权限
                    default: return false;
                }
            }
            return true;
        }
    }
}