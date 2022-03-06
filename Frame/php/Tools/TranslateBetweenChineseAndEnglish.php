<?php


if (!isset($__TranslateBetweenChineseAndEnglish__)) {
    $__TranslateBetweenChineseAndEnglish__ = true;

    require_once __DIR__ . '/../../../ROOT_PATH.php';
    require_once ROOT_PATH . '/Frame/php/CustomPackAndLogger/STSAException.php';

    /**
     * Class TranslateBetweenChineseAndEnglish
     * This class is used to simplify translation between English ID and Chinese Name
     * @author LuoYinsong
     * @package php\Tools
     */
    class TranslateBetweenChineseAndEnglish
    {
        private static array $E2CArray = array(
            'name'=>'姓名',
            'studentID'=>'学号',
            'gender'=>'性别',
            'school'=>'学院名称',
            'nation'=>'民族',
            'hometown'=>'籍贯',
            'phone'=>'电话',
            'QQ'=>'QQ',
            'campus'=>'校区',
            'dormitory-yuan'=>'寝室_苑',
            'dormitory-dong'=>'寝室_栋',
            'dormitory-hao'=>'寝室_号',
            'applicant-name'=>'工资用姓名',
            'applicant-studentID'=>'工资用学号',
            'applicant-credit-card'=>'工资用银行卡',
            'subsidyDossier'=>'建档立卡',
            'password'=>'密码',
            'remark'=>'备注',
            'date'=>'日期',
            'building'=>'教学楼',
            'area'=>'区域',
            'classroom'=>'教室编号',
            'expect'=>'应到人数',
            'No'=>'表编号',
            'late'=>'迟到人数',
            'absent'=>'缺勤人数',
            'first-attendance'=>'第一次出勤',
            'discipline'=>'违纪人数',
            'second-attendance'=>'第二次出勤',
            'leave-early'=>'早退人数'
        );
        private static array $C2EArray = array(
            '姓名'=>'name',
            '学号'=>'studentID',
            '性别'=>'gender',
            '学院名称'=>'school',
            '民族'=>'nation',
            '籍贯'=>'hometown',
            '电话'=>'phone',
            'QQ'=>'QQ',
            '校区'=>'campus',
            '寝室_苑'=>'dormitory-yuan',
            '寝室_栋'=>'dormitory-dong',
            '寝室_号'=>'dormitory-hao',
            '工资用姓名'=>'applicant-name',
            '工资用学号'=>'applicant-studentID',
            '工资用银行卡'=>'applicant-credit-card',
            '建档立卡'=>'subsidyDossier',
            '密码'=>'password',
            '备注'=>'remark',
            '日期'=>'date',
            '教学楼'=>'building',
            '区域'=>'area',
            '教室编号'=>'classroom',
            '应到人数'=>'expect',
            '表编号'=>'No',
            '迟到人数'=>'late',
            '缺勤人数'=>'absent',
            '第一次出勤'=>'first-attendance',
            '违纪人数'=>'discipline',
            '第二次出勤'=>'second-attendance',
            '早退人数'=>'leave-early'
        );

        /**
         * used to translate from English ID to Chinese name
         * @param $E string input English ID
         * @return false|string
         */
        public static function E2C(string $E): false|string {
            if(array_key_exists($E, self::$E2CArray)){
                return self::$E2CArray[$E];
            }
            return false;
        }

        /**
         * used to translate from to English ID
         * @param string $C input Chinese name
         * @return false|string
         */
        public static function C2E(string $C): false|string {
            if(array_key_exists($C, self::$C2EArray)){
                return self::$C2EArray[$C];
            }
            return false;
        }
    }
}