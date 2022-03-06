<?php
if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/Tools/DateTools.php";


if (!function_exists("changeMorningData")) {
    /**
     * @param array $neededData : include date, campus, building, area, classroom, expect, No, late, absent, first-attendance, discipline, second-attendance, leave-early, remark
     * @return array
     * @throws JsonException|STSAException
     */
    function changeMorningData(array $neededData): array{ // FIXME: 没有检查对应排班是否已经归档，如归档则不应允许修改、新增数据
        // 准备环境
        $logger = new STSA_log();
        $session = new DatabaseConnector();
        // 检查权限：1.个人学号是否有对应missionID的任务，有则允许；2.个人学号是否有现场组组员权限；3.是否有数据处理权限，有则不管前两个条件是否满足均可
        require_once ROOT_PATH."/Frame/php/Users/getGroupCode.php";
        $groupsCodesResult = getGroupsCodes("现场组%");
        if (!check_authorization(["team_leader"=>false,"group_leader"=>false,"member"=>true], ["check"=>true,"change"=>true,"input"=>false,"output"=>false])) {
            $logger->add_log(__FILE__.':'.__LINE__, "Change Morning Data, no rights to change morning data, reject", "Log");
            throw new STSAException("无权修改早自习数据", 401);
        }
        // 检查输入，检查neededData是否包含需要的内容，检查输入类型
        if (!isset($neededData["date"],$neededData["campus"],$neededData["building"],$neededData["area"],$neededData["classroom"],
                $neededData["expect"],$neededData["No"],
                $neededData["late"],$neededData["absent"],$neededData["first-attendance"],$neededData["discipline"],
                $neededData["second-attendance"],$neededData["leave-early"],$neededData["remark"])
            || !is_string($neededData["date"]) || !is_string($neededData["campus"]) || !is_string($neededData["building"])
            || !is_string($neededData["area"]) || !is_string($neededData["classroom"]) || !is_string($neededData['remark'])
            || !is_int($neededData["expect"]) || !is_int($neededData["No"]) || !is_int($neededData["late"])
            || !is_int($neededData["absent"]) || !is_int($neededData["first-attendance"]) || !is_int($neededData["discipline"])
            || !is_int($neededData["second-attendance"]) || !is_int($neededData["leave-early"]))
        {
            $logger->add_log(__FILE__.':'.__LINE__, "Change Morning Data, parameters given are not expected, reject", "Log");
            throw new STSAException("输入参数不正确", 400);
        }
        // 操作：判断数据合法性，操作数据库
        $neededData = check_data_morning($neededData,0);
        $sql = "SELECT 查早排班.学院早自习安排编号 as 学院早自习安排编号 FROM 学院早自习安排
                LEFT JOIN 查早排班 on 学院早自习安排.学院早自习安排编号 = 查早排班.学院早自习安排编号
                LEFT JOIN 教室信息 on 学院早自习安排.教室编号 = 教室信息.教室编号
                WHERE 查早排班.学院早自习安排编号={$neededData['No']} and 校区='{$neededData['campus']}'
                    and 教学楼='{$neededData['building']}' and 区域='{$neededData['area']}'
                    and 牌号='{$neededData['classroom']}' and 最终检查者学号='{$_SESSION['userID']}';";
        $checkResult = $session->query($sql);
        if ($checkResult===false) {
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__.':'.__LINE__, "Change Morning Data, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误",417);
        }
        $rows = $checkResult->num_rows;
        $checkResult->fetch_all(MYSQLI_ASSOC);
        if ($rows!==1) {
            $logger->add_log(__FILE__.':'.__LINE__,"Change Morning Data, 无法唯一确定任务或任务不存在",'Log');
            throw new STSAException("无法唯一确定任务或任务不存在", 400);
        }

        $data_for_json = json_encode(array (
            'expect'=>$neededData['expect'],
            'late'=>$neededData['late'],
            'absent'=>$neededData['absent'],
            'first-attendance'=>$neededData['first-attendance'],
            'discipline'=>$neededData['discipline'],
            'second-attendance'=>$neededData['second-attendance'],
            'leave-early'=>$neededData['leave-early']
        ),JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        $sql = "INSERT INTO 查早数据 (早自习排班编号,检查结果,提交者学号,备注)
                VALUES ({$neededData['No']},'{$data_for_json}','{$_SESSION['userID']}','{$neededData['remark']}');";
        $insertResult = $session->query($sql);
        if ($insertResult===false) {
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__.':'.__LINE__, "Change Morning Data, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误",417);
        }
        $session->commit();

        return [true];
    }
}

if (!function_exists("changeMorningAbsent")) {
    /**
     * @param array $neededData
     * @return array
     * @throws JsonException|STSAException
     */
    function changeMorningAbsent(array $neededData): array{ // FIXME: 没有检查对应排班是否已经归档，如归档则不应允许修改、新增数据
        // 准备环境
        $logger = new STSA_log();
        $session = new DatabaseConnector();
        // 检查权限：1.个人学号是否有对应missionID的任务，有则允许；2.个人学号是否有现场组组员权限；3.是否有数据处理权限，有则不管前两个条件是否满足均可
        require_once ROOT_PATH."/Frame/php/Users/getGroupCode.php";
        $groupsCodesResult = getGroupsCodes("现场组%");
        if (!check_authorization(["team_leader"=>false,"group_leader"=>false,"member"=>true], ["check"=>true,"change"=>true,"input"=>false,"output"=>false])) {
            $logger->add_log(__FILE__.':'.__LINE__, "Change Morning Absent, no rights to change morning data, reject", "Log");
            throw new STSAException("无权修改早自习数据", 401);
        }
        // 检查输入，检查neededData是否包含需要的内容，检查neededData["name-ID"]数组的name与studentID数量是否一致，检查输入类型
        if (!isset($neededData["date"],$neededData["campus"],$neededData["building"],$neededData["area"],$neededData["classroom"],
                $neededData["No"],$neededData["name-ID"],$neededData['remark'])
            || !is_array($neededData["name-ID"]) || !is_string($neededData["date"]) || !is_string($neededData["No"])
            || !is_string($neededData["campus"]) || !is_string($neededData["building"]) || !is_string($neededData["area"]) || !is_string($neededData["classroom"])
            || !is_string($neededData['remark'])
            || ($tempCount=count(array_column($neededData["name-ID"], "name")))!==count($neededData["name-ID"])
            || $tempCount!==count(array_column($neededData["name-ID"], "studentID")))
        {
            $logger->add_log(__FILE__.':'.__LINE__, "Change Morning Absent, parameters given are not expected, reject", "Log");
            throw new STSAException("输入参数不正确", 400);
        }
        // 操作：判断数据合法性，操作数据库
        $neededData = check_data_morning($neededData,1);
        $sql = "SELECT 查早排班.学院早自习安排编号 as 学院早自习安排编号 FROM 学院早自习安排
                LEFT JOIN 查早排班 on 学院早自习安排.学院早自习安排编号 = 查早排班.学院早自习安排编号
                LEFT JOIN 教室信息 on 学院早自习安排.教室编号 = 教室信息.教室编号
                WHERE 查早排班.学院早自习安排编号={$neededData['No']} and 校区='{$neededData['campus']}'
                    and 教学楼='{$neededData['building']}' and 区域='{$neededData['area']}'
                    and 牌号='{$neededData['classroom']}' and 最终检查者学号='{$_SESSION['userID']}';";
        $checkResult = $session->query($sql);
        if ($checkResult===false) {
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__.':'.__LINE__, "Change Morning Data, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误",417);
        }
        $rows = $checkResult->num_rows;
        $checkResult->fetch_all(MYSQLI_ASSOC);
        if ($rows!==1) {
            $logger->add_log(__FILE__.':'.__LINE__,"Change Morning Data, 无法唯一确定任务或任务不存在",'Log');
            throw new STSAException("无法唯一确定任务或任务不存在", 400);
        }

        $data_for_json = json_encode($neededData['name-ID'],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
        $sql = "INSERT INTO 查早缺勤表 (早自习排班编号,检查结果,提交者学号,备注)
                VALUES ({$neededData['No']},'{$data_for_json}','{$_SESSION['userID']}','{$neededData['remark']}');";
        $insertResult = $session->query($sql);
        if ($insertResult===false) {
            $errorList2string = mysqli_error($session->getSession());
            $logger->add_log(__FILE__.':'.__LINE__, "Change Morning Data, 数据库查询错误：{$errorList2string}", "Error");
            throw new STSAException("数据库查询错误",417);
        }
        $session->commit();

        return [true];
    }
}

if (!function_exists("showMorningDataAndAbsentForSelf")) {
    /**
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endData
     * @param int $cateType : 0(default) category by classroom; 1 category by time
     * @return array
     * @throws STSAException
     */
    function showMorningDataAndAbsentForSelf(DateTimeImmutable $startDate, DateTimeImmutable $endData, int $cateType=0): array {
        // prepare environment
        $logger = new STSA_log();
        $session = new DatabaseConnector();
        // check authority
        require_once ROOT_PATH."/Frame/php/Users/getGroupCode.php";
        $groupsCodesResult = getGroupsCodes("现场组%");
        if (!check_authorization(["team_leader"=>false,"group_leader"=>false,"member"=>true], ["check"=>true,"change"=>false,"input"=>false,"output"=>false])) {
            $logger->add_log(__FILE__.':'.__LINE__, "Change Morning Absent, no rights to change morning data, reject", "Log");
            throw new STSAException("无权修改早自习数据", 401);
        }
        // check parameters
        // manipulate
    }
}

if (!function_exists("showMorningDataAndAbsentForGroup")) {
    /**
     * @param DateTimeImmutable $startData
     * @param DateTimeImmutable $endData
     * @param array $groupsForLook : indicate group codes
     * @param int $cateType : 0(default) category by time first and by member; 1 category by member first and by time
     * @return array
     */
    function showMorningDataAndAbsentForGroup(DateTimeImmutable $startData, DateTimeImmutable $endData, array $groupsForLook, int $cateType=0): array {
    }
}

if (!function_exists("check_data_morning")) {
    /**
     * function: check if classroom data is valid
     * 检查数据合法性
     * @param array $data_input
     * @param int $data_type : 0表示输入为早自习数据; 1表示输入为早自习缺勤表
     * @return array
     * @throws JsonException|STSAException
     */
    function check_data_morning(array $data_input, int $data_type): array
    {
        $logger = new STSA_log();

        if (!function_exists('date_filter')) {
            /**
             * 日期过滤器
             * @param string $date
             * @return bool
             */
            function date_filter(string $date): bool
            {
                $logger_inner = new STSA_log();
                try {
                    return (new DateTools($date))->getBaseDatetime();
                } catch (Exception $err) {
                    $logger_inner->add_log(__FILE__ . ':' . __LINE__, "Date filter, input datetime is illegal datetime: {$err}", "Log");
                    return false;
                }
            }
        }

        if (!function_exists('campus_filter')) {
            /**
             * 校区过滤器
             * @param string $campus
             * @return false|string
             */
            function campus_filter(string $campus): false|string
            {
                $pattern = '/^(清水河)|(沙河)$/u';
                if (preg_match($pattern, $campus)) {
                    return $campus;
                }

                return false;
            }
        }

        if (!function_exists('building_filter')) {
            /**
             * 教学楼过滤器
             * @param string $building
             * @return false|string
             */
            function building_filter(string $building): false|string
            {
                $pattern = '/^(品学楼)|(立人楼)|(第一教学楼)|(第二教学楼)$/u';
                if (preg_match($pattern, $building)) {
                    return $building;
                }

                return false;
            }
        }

        if (!function_exists('area_filter')) {
            /**
             * 区号过滤器
             * @param string $area
             * @return false|string
             */
            function area_filter(string $area): false|string
            {
                $pattern = '/^[ABC-]$/u';
                if (preg_match($pattern, $area)) {
                    return $area;
                }

                return false;
            }
        }

        if (!function_exists('classroom_filter')) {
            /**
             * 教室编号过滤器
             * @param string $room
             * @return false|string
             */
            function classroom_filter(string $room): false|string
            {
                $pattern = '/^[1-6][0-2]\d$/u';
                if (preg_match($pattern, $room)) {
                    return $room;
                }

                return false;
            }
        }

        if (!function_exists('name_ID_filter')) {
            /**
             * 姓名-学号对过滤器
             * @param array $name_ID
             * @return false|array
             */
            function name_ID_filter(array $name_ID): false|array
            {
                $filters_inner = array(
                    'studentID' => array(
                        'filter' => FILTER_CALLBACK,
                        'options' => 'studentID_filter'
                    ),
                    'name' => array(
                        'filter' => FILTER_SANITIZE_STRING
                    )
                );

                foreach ($name_ID as $studentID => $name) {
                    $result_inner = filter_var_array(array('studentID' => $studentID, 'name' => $name), $filters_inner);
                    foreach ($result_inner as $value) {
                        if ($value === false) {
                            return false;
                        }
                    }
                    $name_ID[$result_inner['studentID']] = $result_inner['name'];
                }

                return $name_ID;
            }
        }

        if (!function_exists('studentID_filter')) {
            /**
             * 学号过滤器
             * @param string $studentID
             * @return false|string
             */
            function studentID_filter(string $studentID): false|string
            {
                $pattern = "/^20[1-3]\d[a-zA-Z0-9]{8,9}$/";
                if (preg_match($pattern, $studentID)) {
                    return $studentID;
                }

                return false;
            }
        }

        $filters_morning_data = array
        (
            'date' => array
            (
                'filter' => FILTER_CALLBACK,
                'options' => 'date_filter'
            ),
            'campus' => array
            (
                'filter' => FILTER_CALLBACK,
                'options' => 'campus_filter'
            ),
            'building' => array
            (
                'filter' => FILTER_CALLBACK,
                'options' => 'building_filter'
            ),
            'area' => array
            (
                'filter' => FILTER_CALLBACK,
                'options' => 'area_filter'
            ),
            'classroom' => array
            (
                'filter' => FILTER_CALLBACK,
                'options' => 'classroom_filter'
            ),
            'expect' => array
            (
                'filter' => FILTER_VALIDATE_INT,
                'options' => array(
                    'min_range' => 0,
                    'max_range' => 500
                )
            ),
            'late' => array
            (
                'filter' => FILTER_VALIDATE_INT,
                'options' => array(
                    'min_range' => 0,
                    'max_range' => 500
                )
            ),
            'absent' => array
            (
                'filter' => FILTER_VALIDATE_INT,
                'options' => array(
                    'min_range' => 0,
                    'max_range' => 500
                )
            ),
            'first-attendance' => array
            (
                'filter' => FILTER_VALIDATE_INT,
                'options' => array(
                    'min_range' => 0,
                    'max_range' => 500
                )
            ),
            'discipline' => array
            (
                'filter' => FILTER_VALIDATE_INT,
                'options' => array(
                    'min_range' => 0,
                    'max_range' => 500
                )
            ),
            'second-attendance' => array
            (
                'filter' => FILTER_VALIDATE_INT,
                'options' => array(
                    'min_range' => 0,
                    'max_range' => 500
                )
            ),
            'leave-early' => array
            (
                'filter' => FILTER_VALIDATE_INT,
                'options' => array(
                    'min_range' => 0,
                    'max_range' => 500
                )
            ),
            'remark' => array(
                'filter' => FILTER_SANITIZE_STRING
            )
        );

        $filters_morning_absent = array
        (
            'date' => array
            (
                'filter' => FILTER_CALLBACK,
                'options' => 'date_filter'
            ),
            'campus' => array
            (
                'filter' => FILTER_CALLBACK,
                'options' => 'campus_filter'
            ),
            'building' => array
            (
                'filter' => FILTER_CALLBACK,
                'options' => 'building_filter'
            ),
            'area' => array
            (
                'filter' => FILTER_CALLBACK,
                'options' => 'area_filter'
            ),
            'classroom' => array
            (
                'filter' => FILTER_CALLBACK,
                'options' => 'classroom_filter'
            ),
            'name-ID' => array(
                'filter' => FILTER_CALLBACK,
                'options' => 'name_ID_filter'
            )
        );

        if ($data_type === 0) {
            $filter_result_morning_data = filter_var_array($data_input, $filters_morning_data);
            foreach ($filter_result_morning_data as $key => $value) {
                if ($value === false) {
                    $showMessage = json_encode(['title' => '数据有误', 'body' => $key], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                    $logger->add_log(__FILE__ . ':' . __LINE__, "Check morning data, input data(s) is/are illegal, please check: {$key}", "Log");
                    throw new STSAException("Check morning data, input data(s) is/are illegal", 400, show: $showMessage);
                }
            }
        } elseif ($data_type === 1) {
            $filter_result_morning_data = filter_var_array($data_input, $filters_morning_absent);
            foreach ($filter_result_morning_data as $key => $value) {
                if ($value === false) {
                    $showMessage = json_encode(['title' => '数据有误', 'body' => $key], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                    $logger->add_log(__FILE__ . ':' . __LINE__, "Check morning absent, input data(s) is/are illegal, please check: {$key}", "Log");
                    throw new STSAException("Check morning absent, input data(s) is/are illegal", 400, show: $showMessage);
                }
            }
        }

        // 品学楼、立人楼必须要有区域编号，为第一教学楼、第二教学楼时区域编号必须为短横杠-
        if (($data_input['building'] === '品学楼' || $data_input['building'] === '立人楼') && $data_input['area'] === '-') {
            $showMessage = json_encode(['title' => '数据有误', 'body' => '教学楼为品学楼或立人楼时，必须有区域编号'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $logger->add_log(__FILE__ . ':' . __LINE__, "Check morning absent, 教学楼为品学楼或立人楼时必须有区域编号", "Log");
            throw new STSAException("Check morning absent, 教学楼为品学楼或立人楼时必须有区域编号", 400, show: $showMessage);
        }
        if ($data_input['building'] !== '品学楼' && $data_input['building'] !== '立人楼' && $data_input['area'] !== '-') {
            $showMessage = json_encode(['title' => '数据有误', 'body' => '教学楼不为品学楼或立人楼时，区域编号必须为短横杠-'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $logger->add_log(__FILE__ . ':' . __LINE__, "Check morning absent, 教学楼不为品学楼或立人楼时区域编号必须为短横杠-", "Log");
            throw new STSAException("Check morning absent, 教学楼不为品学楼或立人楼时区域编号必须为短横杠-", 400, show: $showMessage);
        }

        // 校区和教学楼名称对应
        if ($data_input['campus'] === '沙河' && ($data_input['building'] === '品学楼' || $data_input['building'] === '立人楼')) {
            $showMessage = json_encode(['title' => '数据有误', 'body' => '沙河校区教学楼不应为品学楼或立人楼'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $logger->add_log(__FILE__ . ':' . __LINE__, "Check morning data/absent, 沙河校区教学楼不应为品学楼或立人楼", "Log");
            throw new STSAException("Check morning data/absent, 沙河校区教学楼不应为品学楼或立人楼", 400, show: $showMessage);
        }
        if (($data_input['campus'] === '清水河') && $data_input['building'] !== '品学楼' && $data_input['building'] !== '立人楼') {
            $showMessage = json_encode(['title' => '数据有误', 'body' => '清水河校区教学楼应为品学楼或立人楼'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $logger->add_log(__FILE__ . ':' . __LINE__, "Check morning data/absent, 清水河校区教学楼应为品学楼或立人楼", "Log");
            throw new STSAException("Check morning data/absent, 清水河校区教学楼应为品学楼或立人楼", 400, show: $showMessage);
        }

        return $data_input;
    }
}