<?php
session_start();
require_once __DIR__ . "/../../../ROOT_PATH.php";
require_once ROOT_PATH . "/Frame/php/Tools/DateTools.php";
require_once ROOT_PATH . "/Frame/php/Tools/DeviceAndIPDetector.php";


if (!isset($__STSA_LOG__)) {
    $__STSA_LOG__ = true;

    /**
     * Class STSA_log
     * This is a custom class for STSA PHP service log
     * @author LuoYinsong
     * @package php\CustomPackAndLogger
     */
    class STSA_log
    {
        private string $LogMode = "Warning"; // LogMode分为Error、Warning、Log。如果为Log，则记录一切日志，如果为Warning则记录警告和错误级别的日志，如果为Error则只记录错误日志
        private string $LogDir = ROOT_PATH."/log";

        /**
         * STSA_log constructor.
         */
        public function __construct()
        {
            try{
                $conf = json_decode(file_get_contents(ROOT_PATH."/config/Log.conf"), true, 1024, JSON_THROW_ON_ERROR);
                // set LogMode
                if (isset($conf["LogMode"])) {
                    if ($conf["LogMode"]==="Error") {
                        $this->LogMode = "Error";
                    }
                    elseif ($conf["LogMode"]==="Warning") {
                        $this->LogMode = "Warning";
                    }
                    elseif ($conf["LogMode"]==="Log") {
                        $this->LogMode = "Log";
                    }
                    else {
                        $this->LogMode = "Warning";
                        $this->add_log(__FILE__.":".__LINE__, "When init STSA_log, LogMode in json config file is mistype.\nLogMode set as 'Log'.", "Warning");
                    }
                } else {
                    $this->LogMode = "Warning";
                    $this->add_log(__FILE__.":".__LINE__, "When init STSA_log, LogMode in json config file is missing.\nLogMode set as 'Log'.", "Warning");
                }
                // set LogDir
                if (isset($conf["LogDir"]) && is_dir($conf["LogDir"])) {
                    $this->LogDir = $conf["LogDir"];
                } else {
                    $this->LogDir = ROOT_PATH."/log";
                    $this->add_log(__FILE__.":".__LINE__, "When init STSA_log, LogDir in json config file is missing or not a directory.\nLogDir set as WEBROOT/log.", "Warning");
                }
            } catch (JsonException $e) {
                $this->LogMode="Warning";
                $this->LogDir = ROOT_PATH."/log";
                $this->add_log(__FILE__.":".__LINE__, "When init STSA_log, json config file cannot open correctly.\nLogMode set as 'Log' and LogDir set as WEBROOT/log.", "Warning");
            }
        }

        /**
         * This function add log to file in WEBROOT/log/
         * @param string $pos
         * @param string $log
         * @param string $type
         * @return void
         */
        public function add_log(string $pos, string $log, string $type="Log"): void{
            $addTypeWarning = false;
            $canLog = false;
            // 规范记录类型
            if ($type!=="Log" && $type!=="Error" && $type!=="Warning") {
                $type="Log";
                $addTypeWarning = true;
            }
            // 判断是否需要记录
            if($this->LogMode==="Error" && $type==="Error") {
                $canLog = true;
            }
            elseif ($this->LogMode==="Warning" && $type!=="Log") {
                $canLog = true;
            }
            elseif ($this->LogMode==="Log") {
                $canLog = true;
            }
            // 记录日志
            if ($canLog===true) {
                // 获取时间，获取设备信息，登录信息
                $timeForFileName = DateTools::getCurrentDatetime(mode: "string");
                $timeForLog = DateTools::getCurrentDatetime(mode: "database");
                try {
                    $deviceInfo = (new DeviceAndIPDetector())->getClientInfo(forLog: true);
                } catch (JsonException $e) {
                    $deviceInfo = "";
                }
                $UserIDName = $_SESSION["userID"]." ".$_SESSION["userName"]." ".$_SESSION["isLogin"];
                // 根据时间尝试打开或创建文件，追加写入
                $f = fopen($this->LogDir."/".$timeForFileName.".log", 'a');
                // 第一行写入换行符
                fwrite($f,"\n");
                // 第二行写入[记录级别:警告级别]，日志时间，创建日志的登录id
                fwrite($f,"[".$this->LogMode.":".$type."]".$timeForLog." \t ".$UserIDName."\n");
                // 第三行写入登录设备等
                fwrite($f,"[DEVICE]".$deviceInfo."\n");
                // 第四行写入警告信息（如果有）
                if ($addTypeWarning===true) {
                    fwrite($f, "[Ex-INFO]Log type is wrong and set as Log\n");
                }
                // 第五行写入=========
                fwrite($f,"==============="."\n");
                // 第六行写入脚本定位
                fwrite($f,"[POSITION]".$pos."\n");
                // 第七行写入日志信息
                fwrite($f,"[CONTENT]".$log."\n");
                // 第八行写入换行符
                fwrite($f,"\n");
                fclose($f);
            }
        }
    }
}