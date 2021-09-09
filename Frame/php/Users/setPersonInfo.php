<?php
session_start();
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/Tools/TranslateBetweenChineseAndEnglish.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

if(!function_exists("setOnePersonAllInfo")) {
    function setOnePersonAllInfo(array $personalInfo, bool $isFirst=false): array{
        $logger = new STSA_log();
        // 初始化过滤器
        function studentID_filter($studentID) { // 学号过滤器
            $pattern = "/^20[1-5]\d[a-zA-Z0-9]{8,9}$/";
            if(preg_match($pattern,$studentID)) {
                return $studentID;
            }
            return false;
        }
        function gender_filter($gender) { // 性别过滤器
            $pattern = '/^[男女]$/u';
            if(preg_match($pattern,$gender)) {
                return $gender;
            }
            return false;
        }
        function school_filter($school) { // 学院过滤器
            $session = new DatabaseConnector();
            $sql = "select 学院名称 from 学院信息;";
            $schoolInfos = $session->query($sql);
            if ($schoolInfos===false) {
                throw new STSAException("数据库查询错误",417);
            }
            $schoolInfos = array_column($schoolInfos->fetch_all(MYSQLI_ASSOC),"学院名称");
            foreach ($schoolInfos as $value) {
                if($value===$school) {
                    return $school;
                }
            }
            return false;
        }
        function QQ_filter($QQ) { // QQ过滤器
            $pattern = "/^[1-9]\d{4,}$/";
            if(preg_match($pattern,$QQ)) {
                return $QQ;
            }
            return false;
        }
        function campus_filter($campus) { // 校区过滤器
            $pattern = '/^((清水河)|(沙河))$/u';
            if(preg_match($pattern,$campus)) {
                return $campus;
            }
            return false;
        }
        function dormitory_filter($dormitory ) { // 寝室_苑过滤器
            $pattern = '/^((学知苑)|(硕丰苑)|(校外)|(校内))$/u';
            if(preg_match($pattern,$dormitory)) {
                return $dormitory;
            }
            return false;
        }
        function dormitory_num_filter( $dormitory_num ) { // 寝室_号过滤器
            $pattern = "/^[1-6][0-8]\d$/";
            if(preg_match($pattern,$dormitory_num)){
                return $dormitory_num;
            }
            return false;
        }
        function bank_filter($bank) { // 银行卡号过滤器
            if(strlen($bank) === 19) {
                return filter_var($bank, FILTER_VALIDATE_INT);
            }
            return false;
        }
        function subsidy_filter($subsidy_filter) { // 建档立卡项过滤器
            $pattern = '/^[是否]$/u';
            if(preg_match($pattern,$subsidy_filter)) {
                return $subsidy_filter;
            }
            return false;
        }
        function remark_filter($remark) { // 备注过滤器
            return filter_var($remark, FILTER_SANITIZE_STRING,['flags'=>FILTER_FLAG_ENCODE_LOW]);
        }
        function password_filter($pw) { // 密码过滤器
            $length = strlen($pw);
            if($length>5 && $length<19 && filter_var($pw,FILTER_SANITIZE_EMAIL)===$pw) {
                return addslashes(filter_var($pw, FILTER_SANITIZE_EMAIL));
            }
            return false;
        }
        $filters = array (
            '姓名' => FILTER_SANITIZE_STRING,
            '学号' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'studentID_filter'
            ),
            '性别' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'gender_filter'
            ),
            '学院名称' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'school_filter'
            ),
            '民族' => FILTER_SANITIZE_STRING,
            '籍贯' => FILTER_SANITIZE_STRING,
            'QQ' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'QQ_filter'
            ),
            '电话' => array (
                'filter' =>FILTER_VALIDATE_INT,
                'options' =>array (
                    'min_range' =>10000000000,
                    'max_range' =>19999999999
                )
            ),
            '校区' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'campus_filter'
            ),
            '寝室_苑' => array (
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
            '建档立卡' => array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'subsidy_filter'
            ),
            '备注' => array(
                'filter' =>FILTER_CALLBACK,
                'options' => 'remark_filter'
            )
        );
        if(isset($personalInfo['密码'])) {
            $filters['密码'] = array (
                'filter' =>FILTER_CALLBACK,
                'options' => 'password_filter'
            );
        }

        // 对数据过滤，确定数据合法性，打包整理非法信息
        $filter_result = filter_var_array($personalInfo, $filters);
        $errorInput = array();
        foreach ($filter_result as $key=>$value) {
            if($value===false) {
                $errorInput[$key] = $personalInfo[$key];
            }
        }
        // 如果存在非法输入，打包信息，抛出异常
        if(!empty($errorInput)) {
            $showMessage = array();
            foreach ($errorInput as $key=>$value) {
                $translation = TranslateBetweenChineseAndEnglish::C2E($key);
                $showMessage[$translation] = $value;
            }
            $showMessage = json_encode($showMessage, JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
            $logger->add_log(__FILE__.":".__LINE__, "setOnePersonAllInfo, some input info is illegal", "Log");
            throw new STSAException("输入数据不合法",400,show: $showMessage);
        }
        // 如果全部合法，则继续下方的提交
        if($isFirst) { // 初次注册
            // 检查数据库是否已有数据
            $session = new DatabaseConnector();
            $sql = "select * from 成员信息 where 学号='{$personalInfo['学号']}';";
            $info = $session->query($sql);
            if ($info===false) {
                $logger->add_log(__FILE__.":".__LINE__, "setOnePersonAllInfo, 数据库查询错误", "Error");
                throw new STSAException("数据库查询错误",417);
            }
            // 已有数据返回错误
            if($info->num_rows!==0) {
                $showMessage = json_encode(["信息" => "账号已存在"], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                $logger->add_log(__FILE__.":".__LINE__, "setOnePersonAllInfo, account wanted to register has already exist", "Log");
                throw new STSAException("输入数据不合法",400,show: $showMessage);
            }
            // 没有数据则进行录入
            $sql = "select 学院编号 from 学院信息 where 学院名称='{$personalInfo['学院名称']}';";
            $schoolID = $session->query($sql)->fetch_assoc()["学院编号"];
            $sql = "delete from 成员基本信息 where 学号='{$personalInfo['学号']}';";
            $session->query($sql);
            $sql = "insert into 成员基本信息 (学号, 姓名, 性别) VALUE ('{$personalInfo['学号']}','{$personalInfo['姓名']}','{$personalInfo['性别']}');";
            $session->query($sql);
            $sql = "insert into 成员信息 (学号, 民族, 籍贯, 电话, QQ, 学院编号, 寝室_苑, 寝室_栋, 寝室_号, 工资用姓名, 工资用学号, 工资用银行卡, 建档立卡, 备注)
                    VALUE ('{$personalInfo['学号']}','{$personalInfo['民族']}','{$personalInfo['籍贯']}','{$personalInfo['电话']}',
                           '{$personalInfo['QQ']}',$schoolID,'{$personalInfo['寝室_苑']}','{$personalInfo['寝室_栋']}',
                           '{$personalInfo['寝室_号']}','{$personalInfo['工资用姓名']}','{$personalInfo['工资用学号']}',
                           '{$personalInfo['工资用银行卡']}','{$personalInfo['建档立卡']}','';";
            $session->query($sql);
            $sql = "insert into 账户密码 (学号, 密码, 密码备份) VALUE ('{$personalInfo['学号']}',AES_ENCRYPT('{$personalInfo['密码']}','{$personalInfo['密码']}'),'{$personalInfo['密码']}');";
            $session->query($sql);
            $auth = json_encode([
                'super' => false,
                'team_leader' => false,
                'groups' => [],
                "data" => [
                    'check' => false,
                    'change' => false
                ]
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $sql = "insert into 权限信息 (学号, 权限) VALUE ('{$personalInfo['学号']}',$auth);";
            $session->query($sql);
            $session->commit();
            $logger->add_log(__FILE__.":".__LINE__, "setOnePersonAllInfo, register new account", "Log");
        } else { // 不是初次注册
            // 确定权限信息
            if ($personalInfo['学号'] === $_SESSION["userID"]) { // 修改本人信息
                // check authorization 属于个人级别的本人保密信息
                if (!check_authorization()) {
                    $logger->add_log(__FILE__.":".__LINE__, "setOnePersonAllInfo, 修改本人信息失败, 权限错误", "Log");
                    throw new STSAException("无权限修改本人信息，可能由于未登录或登录信息已过期", 401);
                }
            } else {
                // check authorization 属于个人级别的他人保密信息
                if (!check_authorization(['team_leader' => false, 'group_leader' => false, 'member' => false])) {
                    $logger->add_log(__FILE__.":".__LINE__, "setOnePersonAllInfo, 修改他人信息失败, 权限错误", "Log");
                    throw new STSAException("无权限修改他人信息", 401);
                }
            }
            // 检查数据库是否已有数据
            $session = new DatabaseConnector();
            $sql = "select * from 成员信息 where 学号='{$personalInfo['学号']}';";
            $info = $session->query($sql);
            if ($info===false) {
                $logger->add_log(__FILE__.":".__LINE__, "setOnePersonAllInfo, 数据库查询错误", "Error");
                throw new STSAException("数据库查询错误",417);
            }
            // 没有数据则返回错误
            if($info->num_rows===0) {
                $showMessage = json_encode(["信息" => "账号不存在"], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                $logger->add_log(__FILE__.":".__LINE__, "setOnePersonAllInfo, account wanted to change is not exist", "Log");
                throw new STSAException("输入数据不合法",400,show: $showMessage);
            }
            // 已有数据则进行更新
            $sql = "select 学院编号 from 学院信息 where 学院名称='{$personalInfo['学院名称']}';";
            $schoolID = $session->query($sql)->fetch_assoc()["学院编号"];
            $sql = "update 成员基本信息 set 姓名='{$personalInfo['姓名']}',性别='{$personalInfo['性别']}' where 学号='{$personalInfo['学号']}';";
            $session->query($sql);
            $sql = "update 成员信息 set 民族='{$personalInfo['民族']}', 籍贯='{$personalInfo['籍贯']}', 电话='{$personalInfo['电话']}',
                QQ='{$personalInfo['QQ']}', 学院编号=$schoolID, 寝室_苑='{$personalInfo['寝室_苑']}',
                寝室_栋='{$personalInfo['寝室_栋']}', 寝室_号='{$personalInfo['寝室_号']}', 工资用姓名='{$personalInfo['工资用姓名']}', 工资用学号='{$personalInfo['工资用学号']}',
                工资用银行卡='{$personalInfo['工资用银行卡']}', 建档立卡='{$personalInfo['建档立卡']}', 备注='{$personalInfo['备注']}' where 学号='{$personalInfo['学号']}';";
            $session->query($sql);
            if(isset($personalInfo['密码'])) { // 有密码则更新密码
                $sql = "update 账户密码 set 密码=AES_ENCRYPT('{$personalInfo['密码']}','{$personalInfo['密码']}'), 密码备份='{$personalInfo['密码']}' where 学号='{$personalInfo['学号']}';";
                $session->query($sql);
            }
            $session->commit();
            $logger->add_log(__FILE__.":".__LINE__, "setOnePersonAllInfo, change account", "Log");
        }
        $_SESSION["userName"] = $personalInfo["姓名"];
        return [true];
    }
}