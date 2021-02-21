<?php
require_once __DIR__.'/../../ROOT_PATH.php';
require_once ROOT_PATH.'/frame/php/Database_connector.php';
require_once ROOT_PATH.'/frame/php/DateTools.php';
//require ROOT_PATH.'/frame/php/AuthorizationTools.php';

class Person
{
    // 数据库连接
    private $STSA_DATABASE; // database connection
    // 状态量
    private $informationValid = array(
        'basic' => false,
        'work' => false,
        'password' => false,
        'authorization' => false
    ); // check if personal information is all legal
    public $invalid_infos = array(
        'basic'=>array(),
        'work'=>array(),
        'password'=>array(),
        'authorization'=>array(),
        'changeInfo'=>array()
    ); // show the information which is illegal
    private $existInDatabase = false; // show if this person already exists in the database

    // 个人信息
    private $name = ''; // 姓名
    private $campus = ''; // 校区
    private $school = ''; // 学院
    private $studentID; // 学号
    private $gender = ''; // 性别
    private $ethnicity = ''; // 民族
    private $hometown = ''; // 籍贯
    private $phoneNumber = ''; // 电话
    private $QQ = ''; // QQ号
    private $dormitory_yuan = ''; // 寝室_苑
    private $dormitory_dong = ''; // 寝室_栋
    private $dormitory_hao = ''; // 寝室_号
    // 工资申报信息
    private $bankIDForWage = ''; // 工资用银行卡号
    private $nameForWage = ''; // 工资用姓名
    private $studentIDForWage = ''; // 工资用学号
    private $subsidyDossier = ''; // 建档立卡
    // 岗位信息
    private $groupBelonging; // 所属组
    private $work; // 岗位
    private $wage = 0; // 基础工资
    private $MondayEmptyTime = ''; // 周一空课
    private $TuesdayEmptyTime = ''; // 周二空课
    private $WednesdayEmptyTime = ''; // 周三空课
    private $ThursdayEmptyTime = ''; // 周四空课
    private $FridayEmptyTime = ''; // 周五空课
    private $score; // 计分，用于以计分方式进行岗位考核的情况
    private $assessment; // 考评，用于以考评方式进行岗位考核的情况
    private $remark = array(); // 备注
    // 密码与权限
    private $password = ''; // 密码
    private $authorization = ''; // 权限


    /**
     * Person constructor.
     * @param string $studentID: Automatically get personal information if given studentID
     */
    public function __construct(string $studentID) {
        $this->STSA_DATABASE = new Database_connector(ROOT_PATH.'/config/DataBase_STSA.conf');

        $this->studentID = $studentID;
        $this->fetch_all_personal_info();
    }

    public function __destruct() {
        unset($this->STSA_DATABASE);
    }

    // public function: use name prop to get all personal information
    /**
     * @return bool
     */
    public function fetch_all_personal_info(): bool {
        $this->existInDatabase = (
            $this->fetch_basic_info()
            and $this->fetch_work_info()
            and $this->fetch_authorization_info()
        );
        return $this->existInDatabase;
    }

    // public function: use name prop to get personal basic information
    /**
     * @return bool
     */
    public function fetch_basic_info(): bool {
        try {
            $result = $this->STSA_DATABASE->search('成员基本信息', array(), array('学号' => $this->studentID));
            if ($result = $result->fetch_assoc()) {
                $this->name = $result['姓名'];
                $this->gender = $result['性别'];
                $this->campus = $result['校区'];
                $this->school = $result['学院'];
                $this->ethnicity = $result['民族'];
                $this->hometown = $result['籍贯'];
                $this->phoneNumber = $result['电话'];
                $this->QQ = $result['QQ'];
                $this->bankIDForWage = $result['工资用银行卡号'];
                $this->nameForWage = $result['工资用姓名'];
                $this->studentIDForWage = $result['工资用学号'];
                $this->subsidyDossier = $result['建档立卡'];
                $this->dormitory_yuan = $result['寝室_苑'];
                $this->dormitory_dong = $result['寝室_栋'];
                $this->dormitory_hao = $result['寝室_号'];

                $this->informationValid['basic'] = true;
            }
            else {
                throw new UnexpectedValueException('不存在此人信息');
            }
        } catch (Exception $e) {
            $this->informationValid['basic'] = false;
        }
        return $this->informationValid['basic'];
    }

    // public function: use name prop to get personal work information
    /**
     * @return bool
     */
    public function fetch_work_info(): bool {
        try {
            $result = $this->STSA_DATABASE->search('成员工作信息', array(), array('学号' => $this->studentID));
            if ($result = $result->fetch_assoc()) {
                $this->MondayEmptyTime = $result['周一空课'];
                $this->TuesdayEmptyTime = $result['周二空课'];
                $this->WednesdayEmptyTime = $result['周三空课'];
                $this->ThursdayEmptyTime = $result['周四空课'];
                $this->FridayEmptyTime = $result['周五空课'];
                $this->groupBelonging = $result['所属组'];
                $this->work = $result['岗位'];
                $this->wage = $result['基础工资'];
                $this->score = $result['计分'];
                $this->assessment = $result['考评'];
                $this->remark = json_decode($result['备注'], true);

                $this->informationValid['work'] = true;
            }
            else {
                throw new UnexpectedValueException('不存在此人信息');
            }
        } catch (Exception $e) {
            $this->informationValid['work'] = false;
        }

        return $this->informationValid['work'];
    }

    // public function: use name prop to get personal password information
    /**
     * @return bool
     */
    public function fetch_authorization_info(): bool {
        try {
            $result = $this->STSA_DATABASE->search('权限信息',array('权限'),array('学号'=>$this->studentID));
            if ($result = $result->fetch_assoc()) {
                $this->authorization = $result['权限'];

                $this->informationValid['authorization'] = true;
            }
            else {
                throw new UnexpectedValueException('不存在此人信息');
            }
        } catch (Exception $e) {
            $this->informationValid['authorization'] = false;
        }
        return $this->informationValid['authorization'];
    }

    // private function: check if personal basic information is valid
    // ！！！！ “学院、民族、籍贯” 只去除特殊标签和字符！！！
    /**
     * @return void
     */
    private function check_basic_data(): void { // check basic information
        if (!function_exists('gender_filter')) {
            function gender_filter($gender) // 性别过滤器
            {
                $pattern = '/^(男|女)$/u';
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
        if (!function_exists('QQ_filter')) {
            function QQ_filter($QQ) { // QQ过滤器
                $pattern = "/^[1-9]\d{4,}$/";
                if(preg_match($pattern,$QQ)) {
                    return $QQ;
                }

                return false;
            }
        }
        if (!function_exists('campus_filter')){
            function campus_filter($campus) { // 校区过滤器
                $pattern = '/^((清水河)|(沙河))$/u';
                if(preg_match($pattern,$campus)) {
                    return $campus;
                }

                return false;
            }
        }
        if (!function_exists('dormitory_filter')){
            function dormitory_filter($dormitory ) { // 寝室_苑过滤器
                $pattern = '/^((学知苑)|(硕丰苑)|(博瀚苑)|(欣苑)|(校内))$/u';
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
        if (!function_exists('subsidy_filter')) { // 建档立卡项过滤器
            function subsidy_filter($subsidy_filter) // 建档立卡项过滤器
            {
                $pattern = '/^(是|否)$/u';
                if(preg_match($pattern,$subsidy_filter)) {
                    return $subsidy_filter;
                }

                return false;
            }
        }

        $filters_basic = array
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
            'QQ' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'QQ_filter'
            ),
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
            '寝室_栋' => array (
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
            '工资用银行卡号' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'bank_filter'
            ),
            '工资用姓名' => FILTER_SANITIZE_STRING,
            '工资用学号' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'studentID_filter'
            ),
            '学号' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'studentID_filter'
            ),
            '建档立卡' => array
            (
                'filter' =>FILTER_CALLBACK,
                'options' => 'subsidy_filter'
            )
        );
        $data_basic = array(
            '姓名' =>$this->name, // 姓名
            '校区' =>$this->campus, // 校区
            '学院' =>$this->school, // 学院
            '学号' =>$this->studentID, // 学号
            '性别' =>$this->gender, // 性别
            '民族' =>$this->ethnicity, // 民族
            '籍贯' =>$this->hometown, // 籍贯
            '电话' =>$this->phoneNumber, // 电话
            'QQ' =>$this->QQ, // QQ号
            '寝室_苑' => $this->dormitory_yuan, // 寝室_苑
            '寝室_栋' => $this->dormitory_dong, // 寝室_栋
            '寝室_号' => $this->dormitory_hao, // 寝室_号
            '工资用银行卡号' =>$this->bankIDForWage, // 工资申请时银行卡号
            '工资用姓名' =>$this->nameForWage, // 工资申请时姓名
            '工资用学号' =>$this->studentIDForWage, // 工资申请时学号
            '建档立卡' =>$this->subsidyDossier // 建档立卡
        );
        $basic_filter_result = filter_var_array($data_basic, $filters_basic);
        $this->invalid_infos['basic'] = array();
        $this->informationValid['basic'] = true;
        foreach($basic_filter_result as $key=>$value) {
            if ($value===false) {
                $this->informationValid['basic'] = false;
                $this->invalid_infos['basic'][$key] = $data_basic[$key];
            }
        }
    }

    // private function: check if personal work information is valid
    // ！！！！ “备注” 只去除特殊标签和字符！！！
    /**
     * @return void
     */
    private function check_work_data(): void {
        $group_list = $this->STSA_DATABASE->search('部门信息',array('部门'));
        $GLOBALS['GROUPS'] = array();
        while($s = $group_list->fetch_assoc()['部门']) {
            $GLOBALS['GROUPS'][] = $s;
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
            function work_filter($work=null) { // 岗位过滤器
                if ($work === null) {
                    return $work;
                }
                $pattern = '/^((组员)|(组长)|(队长))$/u';
                if(preg_match($pattern,$work)) {
                    return $work;
                }

                return false;
            }
        }
        if (!function_exists('group_filter')){
            function group_filter($group=null) { // 所属组过滤器
                if($group === null) {
                    return $group;
                }

                foreach($GLOBALS['GROUPS'] as $value)
                {
                    if($value === $group) {
                        return $group;
                    }
                }

                return false;
            }
        }
        if (!function_exists('money_filter')){
            function money_filter($money) { // 工资过滤器
                return filter_var($money,FILTER_VALIDATE_FLOAT,array('min_range' =>0, 'max_range' =>2000));
            }
        }
        if (!function_exists('score_filter')){
            function score_filter($score) { // 计分过滤器
                return filter_var($score,FILTER_VALIDATE_FLOAT,array('min_range' =>0, 'max_range' =>5));
            }
        }
        if (!function_exists('assessment_filter')){
            function assessment_filter($assessment) { // 考评过滤器
                $pattern = '/^((合格)|(警告)|(惩罚))$/u';
                if(preg_match($pattern,$assessment)) {
                    return $assessment;
                }

                return false;
            }
        }
        if (!function_exists('remark_filter')) { // 备注滤器
            function remark_filter($remark) // 备注过滤器
            {
                if (is_array($remark)) {
                    foreach ($remark as $key=>$value) {
                        $remark[$key] = filter_var($value, FILTER_SANITIZE_STRING);
                    }
                    return $remark;
                }
                return false;
            }
        }

        $filters_work = array
        (
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
            '岗位' => [
                'filter' =>FILTER_CALLBACK,
                'options' => 'work_filter'
            ],
            '基础工资' => array
            (
                'filter' =>FILTER_CALLBACK,
                'options' => 'money_filter'
            ),
            '计分' => array
            (
                'filter' =>FILTER_CALLBACK,
                'options' => 'score_filter'
            ),
            '考评' => array
            (
                'filter' =>FILTER_CALLBACK,
                'options' => 'assessment_filter'
            ),
            '备注' => array
            (
                'filter' =>FILTER_CALLBACK,
                'options' => 'remark_filter'
            )
        );
        $data_work = array(
            '周一空课' =>$this->MondayEmptyTime, // 周一空课
            '周二空课' =>$this->TuesdayEmptyTime, // 周二空课
            '周三空课' =>$this->WednesdayEmptyTime, // 周三空课
            '周四空课' =>$this->ThursdayEmptyTime, // 周四空课
            '周五空课' =>$this->FridayEmptyTime, // 周五空课
            '基础工资' =>$this->wage, // 基础工资
            '计分' =>$this->score, // 计分
            '考评' =>$this->assessment, // 考评
            '备注' =>$this->remark // 备注
        );
        if ($this->groupBelonging!==null) {$data_work['所属组']=$this->groupBelonging;}
        if ($this->work!==null) {$data_work['岗位']=$this->work;}
        // 以上两行是由于null值在引入php filter时会自动转化为空字符串''，为了避免由此导致的问题，只有不为null时才进行检查

        $work_filter_result = filter_var_array($data_work, $filters_work);
        $this->invalid_infos['work'] = array();
        $this->informationValid['work'] = true;
        foreach($work_filter_result as $key=>$value) {
            if ($value===false) {
                $this->informationValid['work'] = false;
                $this->invalid_infos['work'][$key] = $data_work[$key];
            }
        }
    }

    // private function: check if personal authorization information is valid
    // ！！！！ “权限” 没有检查！！！
    /**
     * @return void
     */
    private function check_authorization_data(): void {
        if (!function_exists('authorization_filter')){
            function authorization_filter($authorization) { // 权限过滤器
//                    $pattern = "/^[0-2]{6}\.[0-2]{10}\.[0-2]{3}$/";
//                    if(preg_match($pattern,$authorization)){
//                        return $authorization;
//                    }
//
//                    return false;
                return $authorization;
            }
        }

        $filters_authorization = array
        (
            '权限' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'authorization_filter'
            )
        );
        $data_authorization = array(
            '权限' =>$this->authorization // 权限
        );
        $authorization_filter_result = filter_var_array($data_authorization, $filters_authorization);
        $this->invalid_infos['authorization'] = array();
        $this->informationValid['authorization'] = true;
        foreach($authorization_filter_result as $key=>$value) {
            if ($value===false) {
                $this->informationValid['authorization'] = false;
                $this->invalid_infos['authorization'][$key] = $data_authorization[$key];
            }
        }
    }

    // private function: check if personal password information is valid
    /**
     * @return void
     */
    private function check_password_data():void {
        if (!function_exists('pw_filter')){
            function pw_filter($pw) { // 密码过滤器
                $length = strlen($pw);
                if($length>5 && $length<19 && filter_var($pw,FILTER_SANITIZE_EMAIL)===$pw) {
                    return addslashes(filter_var($pw, FILTER_SANITIZE_EMAIL));
                }

                return false;
            }
        }

        $filters_password = array
        (
            '密码' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'pw_filter'
            )
        );
        $data_password = array(
            '密码' =>$this->password // 密码
        );
        $password_filter_result = filter_var_array($data_password, $filters_password);
        $this->invalid_infos['password'] = array();
        $this->informationValid['password'] = true;
        foreach($password_filter_result as $key=>$value) {
            if ($value===false) {
                $this->invalid_infos['password'] = false;
                $this->invalid_infos['password'][$key] = $data_password[$key];
            }
        }
    }

    // public function: commit personal information to database
    /**
     * @return bool
     */
    public function commit_all_personal_information(): bool {
        $s = $this->commit_basic_information()
            and $this->commit_work_information()
            and $this->commit_authorization_information();
        $this->existInDatabase = $s ? true : $this->existInDatabase;
        return $s;
    }

    // public function: commit personal basic information to database
    /**
     * @return bool
     */
    public function commit_basic_information(): bool {
        $this->check_basic_data();

        if($this->informationValid['basic']) {
            $data = array
            (
                '姓名' =>$this->name, // 姓名
                '校区' =>$this->campus, // 校区
                '学院' =>$this->school, // 学院
                '性别' =>$this->gender, // 性别
                '民族' =>$this->ethnicity, // 民族
                '籍贯' =>$this->hometown, // 籍贯
                '电话' =>$this->phoneNumber, // 电话
                'QQ' =>$this->QQ, // QQ号
                '寝室_苑' => $this->dormitory_yuan, // 寝室_苑
                '寝室_栋' => $this->dormitory_dong, // 寝室_栋
                '寝室_号' => $this->dormitory_hao, // 寝室_号
                '工资用银行卡号' =>$this->bankIDForWage, // 工资用银行卡号
                '工资用姓名' =>$this->nameForWage, // 工资用姓名
                '工资用学号' =>$this->studentIDForWage, // 工资用学号
                '建档立卡' =>$this->subsidyDossier // 建档立卡
            );
            if($this->existInDatabase) {
                $this->STSA_DATABASE->update('成员基本信息', $data,array('学号' =>$this->studentID));
            }
            else{
                $data['学号'] = $this->studentID;
                $this->STSA_DATABASE->insert('成员基本信息', $data);
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
        $this->check_work_data();

        if($this->informationValid['work']) {
            $data = array
            (
                '周一空课' =>$this->MondayEmptyTime, // 周一空课
                '周二空课' =>$this->TuesdayEmptyTime, // 周二空课
                '周三空课' =>$this->WednesdayEmptyTime, // 周三空课
                '周四空课' =>$this->ThursdayEmptyTime, // 周四空课
                '周五空课' =>$this->FridayEmptyTime, // 周五空课
                '所属组' =>$this->groupBelonging, // 所属组
                '岗位' =>$this->work, // 岗位
                '基础工资' =>$this->wage, // 基础工资
                '计分' =>$this->score, // 计分
                '考评' =>$this->assessment, // 考评
                '备注' =>json_encode( // 备注
                    str_replace(array("\n", "\r"), array("\\n", "\\r"), $this->remark),
                    JSON_UNESCAPED_UNICODE
                )
            );
            if($this->existInDatabase) {
                $this->STSA_DATABASE->update('成员工作信息', $data,array('学号' =>$this->studentID));
            }
            else{
                $data['学号'] = $this->studentID;
                $this->STSA_DATABASE->insert('成员工作信息', $data);
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
        $this->check_authorization_data();

        if($this->informationValid['authorization']) {
            $data = array
            (
                '权限' =>$this->authorization // 权限
            );
            if(!$this->existInDatabase && !$this->commit_password_information()) {
                return false;
            }
            $this->STSA_DATABASE->update('权限信息', $data,array('学号' =>$this->studentID));

            return true;
        }

        return false;
    }

    // public function: commit personal password information to database
    /**
     * @return bool
     */
    public function commit_password_information(): bool {
        $this->check_password_data();

        if($this->informationValid['password']) {
            $data = array
            (
                '密码' =>$this->password // 密码
            );
            if($this->existInDatabase) {
                $this->STSA_DATABASE->update('登录信息', $data,array('学号' =>$this->studentID));
            }
            else {
                $data['学号'] = $this->studentID;
                $data['本次登录信息'] = $data['上一次登录信息'] = json_encode(array(), JSON_UNESCAPED_UNICODE);
                $this->STSA_DATABASE->insert('登录信息', $data);
            }

            return true;
        }

        return false;
    }

    public function delete_all_info() { // 数据库自动在删除基本信息表时，会自动删除其余相关表的信息
        $this->STSA_DATABASE->delete('成员基本信息',array('学号'=>$this->studentID));
        $this->existInDatabase = false;
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

        if ($this->informationValid['work']) {
            $data = array
            (
                '周一空课' =>$this->MondayEmptyTime, // 周一空课
                '周二空课' =>$this->TuesdayEmptyTime, // 周二空课
                '周三空课' =>$this->WednesdayEmptyTime, // 周三空课
                '周四空课' =>$this->ThursdayEmptyTime, // 周四空课
                '周五空课' =>$this->FridayEmptyTime, // 周五空课
                '所属组' =>$this->groupBelonging, // 所属组
                '岗位' =>$this->work, // 岗位
                '基础工资' =>$this->wage, // 基础工资
                '计分' =>$this->score, // 计分
                '考评' =>$this->assessment, // 考评
                '备注' =>$this->remark, // 备注
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
                '民族' =>$this->ethnicity, // 民族
                '籍贯' =>$this->hometown, // 籍贯
                '电话' =>$this->phoneNumber, // 电话
                'QQ' =>$this->QQ, // QQ号
                '寝室_苑' => $this->dormitory_yuan, // 寝室_苑
                '寝室_栋' => $this->dormitory_dong, // 寝室_栋
                '寝室_号' => $this->dormitory_hao, // 寝室_号
                '工资用银行卡号' =>$this->bankIDForWage, // 工资申请时银行卡号
                '工资用姓名' =>$this->nameForWage, // 工资申请时姓名
                '工资用学号' =>$this->studentIDForWage, // 工资申请时学号
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

    // public function: check user password
    /**
     * @param string $test_password: the password which need correction test
     * @return bool
     */
    public function password_check(string $test_password): bool { // 测试密码是否为数据库中的本人密码
        try {
            if ($this->STSA_DATABASE->search('登录信息',array(),array('学号'=>$this->studentID,'密码'=>$test_password))->fetch_assoc())
            {
                return true;
            }
            throw new UnexpectedValueException('密码错误');
        } catch (Exception $e) {
            return false;
        }
    }

    // public function: return selected error_info in one array
    /**
     * @param array $error_type
     * @return array
     */
    public function error_array(array $error_type): array {
        $error_array = array();
        foreach ($error_type as $type) {
            switch ($type) {
                case 'basic': $error_array=array_merge($error_array,$this->invalid_infos['basic']);break;
                case 'work': $error_array=array_merge($error_array,$this->invalid_infos['work']);break;
                case 'password': $error_array=array_merge($error_array,$this->invalid_infos['password']);break;
                case 'authorization': $error_array=array_merge($error_array,$this->invalid_infos['authorization']);break;
                case 'changeInfo': $error_array=array_merge($error_array,$this->invalid_infos['changeInfo']);break;
                default:
            }
        }
        return $error_array;
    }

    // public function: set personal information
    /**
     * @param array $info_pairs: the information need to set, please give array of key-value pairs of information
     * @return bool
     */
    public function change_info(array $info_pairs): bool {
        $this->invalid_infos['changeInfo'] = array();

        foreach ($info_pairs as $key=>$value) {
            switch ($key) {
                // 岗位信息
                case '所属组': $this->groupBelonging = ($value===''?null:$value);break; // 所属组
                case '岗位': $this->work = ($value===''?null:$value);break; // 岗位
                case '基础工资': $this->wage = $value;break; // 基础工资
                case '周一空课': $this->MondayEmptyTime = $value;break; // 周一空课
                case '周二空课': $this->TuesdayEmptyTime = $value;break; // 周二空课
                case '周三空课': $this->WednesdayEmptyTime = $value;break; // 周三空课
                case '周四空课': $this->ThursdayEmptyTime = $value;break; // 周四空课
                case '周五空课': $this->FridayEmptyTime = $value;break; // 周五空课
                case '计分': $this->score = $value;break; // 计分
                case '考评': $this->assessment = $value;break; // 考评
                case '备注': $this->remark = $value;break; // 备注
                // 个人信息
                case '姓名': $this->name = $value;break; // 姓名
                case '校区': $this->campus = $value;break; // 校区
                case '学院': $this->school = $value;break; // 学院
                case '性别': $this->gender = $value;break; // 性别
                case '民族': $this->ethnicity = $value;break; // 民族
                case '籍贯': $this->hometown = $value;break; // 籍贯
                case '电话': $this->phoneNumber = $value;break; // 电话
                case 'QQ': $this->QQ = $value;break; // QQ
                case '寝室_苑': $this->dormitory_yuan = $value;break; // 寝室_苑
                case '寝室_栋': $this->dormitory_dong = $value;break; // 寝室_栋
                case '寝室_号': $this->dormitory_hao = $value;break; // 寝室_号
                // 工资申报信息
                case '工资用银行卡号': $this->bankIDForWage = $value;break; // 工资用银行卡号
                case '工资用姓名': $this->nameForWage = $value;break; // 工资用姓名
                case '工资用学号': $this->studentIDForWage = $value;break; // 工资用学号
                case '建档立卡': $this->subsidyDossier = $value;break; // 建档立卡
                // 密码与权限
                case '密码': $this->password = $value;break; // 密码
                case '权限': $this->authorization = $value;break; // 权限
                default: $this->invalid_infos['changeInfo'][] = $key;
            }
        }
        if (empty($this->invalid_infos['changeInfo'])) {
            return true;
        }
        return false;
    }
}