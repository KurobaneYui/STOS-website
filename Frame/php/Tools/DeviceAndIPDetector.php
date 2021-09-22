<?php


if (!isset($__DeviceAndIPDetector__)) {
    $__DeviceAndIPDetector__ = true;

    require_once __DIR__ . '/../../../ROOT_PATH.php';
    require_once ROOT_PATH . '/Frame/php/CustomPackAndLogger/STSAException.php';
    require_once ROOT_PATH . '/Frame/php/Tools/DateTools.php';
    require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

    /**
     * Class DeviceAndIPDetector
     * This class detect info of client, including address, browser, IP, language and OS
     * @author LuoYinsong
     * @package php\Tools
     */
    class DeviceAndIPDetector
    {
        public string $address;
        public string $browser;
        public string $IP;
        public string $language;
        public string $OS;
        public string $datetime;
        private STSA_log $logger;
        // in development
        private string $onlineIP;

        /**
         * DeviceAndIPDetector constructor.
         * This function will construct class and try to get
         * real address, browser type, IP, Language setting, OS, datetime
         * @throws STSAException
         */
        public function __construct()
        {
            $this->logger = new STSA_log();
            try {
                $this->detectAddress();
                $this->detectBrowser();
                $this->detectIP();
                $this->detectLanguage();
                $this->detectOS();
                $this->detectDatetime();
            } catch (JsonException $err) {
                $this->logger->add_log(__FILE__.":".__LINE__, "Init DeviceAndDetector, json相关错误, 错误信息:\n{$err}", "Error", true);
                throw new STSAException('When construct DeviceAndIPDetector, we meet an json error in detectAddress method.', 21, $err);
            }
        }

        /**
         * This function detect client address
         * @return void
         * @throws JsonException
         */
        private function detectAddress(): void{
            if (empty($this->IP)) {
                $this->detectIP();
            }
            $firstFunction = true; // 用于判断使用了哪个API
            $ip_add = file_get_contents('https://whois.pconline.com.cn/ipJson.jsp?json=true&ip=' . $this->IP,
                false,stream_context_create(['http'=>['method'=>'GET','timeout'=>2]])); // 根据新浪api接口获取，对香港服务器不适用，太慢
            if ($ip_add===false) {
                $firstFunction = false;
                $ip_add = file_get_contents("http://ip-api.com/json/{$this->IP}?lang=zh-CN",
                    false,stream_context_create(['http'=>['method'=>'GET','timeout'=>2]])); // 设置2秒超时
            }
            if ($ip_add) {
                $charset = $firstFunction ? iconv('gbk', 'utf-8', $ip_add) : $ip_add; // 使用ip-api则不需要编码转换
                try {
                    $charset = json_decode($charset, true, 1024, JSON_THROW_ON_ERROR);
                }catch (JsonException $err) {
                    $this->logger->add_log(__FILE__.":".__LINE__, "DeviceAndDetector detectAddress, json解包错误, 错误信息:\n{$err}", "Error", true);
                    throw $err;
                }
                $this->address = $firstFunction ? $charset['addr'] : $charset["country"].$charset["regionName"].$charset["city"].'-'.$charset["isp"].'-'.$charset["org"].'-'.$charset["as"]; // 如果用新浪api，则返回一个二维数组
                return;
            }

            $this->address='';
        }

        /**
         * This function detect type of browser of client.
         *
         * Please notice that function does not detect version of browser,
         * and can just detect five types of browser: MSIE, Firefox, Chrome, Safari, Opera.
         * Any other browser type will be classified in "Other"
         * @return void
         */
        private function detectBrowser(): void{
            if (!empty($_SERVER['HTTP_USER_AGENT'])) {
                $br = $_SERVER['HTTP_USER_AGENT'];
                if (false !== stripos($br, 'MSIE')) {
                    $br = 'MSIE';
                } elseif (false !== stripos($br, 'Firefox')) {
                    $br = 'Firefox';
                } elseif (false !== stripos($br, 'Chrome')) {
                    $br = 'Chrome';
                } elseif (false !== stripos($br, 'Safari')) {
                    $br = 'Safari';
                } elseif (false !== stripos($br, 'Opera')) {
                    $br = 'Opera';
                } else {
                    $br = 'Other';
                }
                $this->browser = $br;
                return;
            }
            $this->logger->add_log(__FILE__.":".__LINE__, "DeviceAndDetector detectBrowser, 未匹配到已知浏览器", "Log", true);
            $this->browser = 'Error';
        }

        /**
         * This function detect IP of client.
         *
         * Please noticed that this function isn't ensure correct result when the client using local-internet
         * @return void
         */
        private function detectIP(): void{
            $ip = '';
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
                if ($ip) {
                    $ip = array_unshift($ip, $ip);
                }
            }
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { // 获取代理ip
                $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($ips as $i => $iValue) {
                    if (!preg_match("/^(10|172\.16|192\.168)\./", $iValue)) { // 排除局域网ip
                        $ip = $iValue;
                        break;
                    }
                }
            }
            $tip = empty($_SERVER['REMOTE_ADDR']) ? $ip : $_SERVER['REMOTE_ADDR'];
//            if ($tip === '127.0.0.1') { // 获得本地真实IP
//                return $this->detectOnlineIP();
//            }

            $this->IP = $tip;
        }

        /**
         * This function detect language that client used.
         *
         * Please noticed that function only detect Simplified Chinese, Traditional Chinese and English.
         * Unfortunately, function will conclude all other language in English so far.
         * @return void
         */
        private function detectLanguage(): void{
            if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $langOri = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
                $lang = '';
//                $lang = substr($lang, 0, 5);
                if (false !== stripos($langOri, 'zh-CN')) {
                    $lang .= '简体中文';
                } elseif (false !== stripos($langOri, 'zh')) {
                    $lang .= '繁体中文';
                }
                if (false !== stripos($langOri, 'en-US')){
                    $lang .= ' English US';
                } elseif (false !== stripos($langOri, 'en')) {
                    $lang .= ' English';
                }
                if ($lang==="") {
                    $lang = "Other Language";
                }
                $this->language = $lang;
                return;
            }
            $this->logger->add_log(__FILE__.":".__LINE__, "DeviceAndDetector detectLanguage, 未匹配到语言信息", "Log", true);
            $this->language = 'Error';
        }

        /**
         * This function detect OS of client.
         * @return void
         */
        private function detectOS():void{
            $agent = $_SERVER['HTTP_USER_AGENT'];
            $os = false;
            // find 'win' tag
            if (false !== stripos($agent, 'win')) {
                if (strpos($agent, '95')) {
                    $os = 'Windows 95';
                }
                else if (false !== strpos($agent, '4.90')) {
                    $os = 'Windows ME';
                }
                else if (false !== strpos($agent,'98')) {
                    $os = 'Windows 98';
                }
                else if (preg_match('/nt 6.0/i', $agent)) {
                    $os = 'Windows Vista';
                }
                else if (preg_match('/nt 6.1/i', $agent)) {
                    $os = 'Windows 7';
                }
                else if (preg_match('/nt 6.2/i', $agent)) {
                    $os = 'Windows 8';
                }
                else if (preg_match('/nt 10.0/i', $agent)) {
                    $os = 'Windows 10';#添加win10判断
                }
                else if (preg_match('/nt 5.1/i', $agent)) {
                    $os = 'Windows XP';
                }
                else if (preg_match('/nt 5/i', $agent)) {
                    $os = 'Windows 2000';
                }
                else if (false !== stripos($agent, 'nt')) {
                    $os = 'Windows NT';
                }
                else if (false !== strpos($agent, '32')) {
                    $os = 'Windows 32';
                }
            }
            else if (false !== stripos($agent, 'linux')) {
                $os = 'Linux';
            }
            else if (false !== stripos($agent, 'unix')) {
                $os = 'Unix';
            }
            else if (false !== stripos($agent, 'Mac') && false !== stripos($agent, 'PC')) {
                $os = 'Macintosh';
            }
            else if (false !== stripos($agent, 'iPhone')) {
                $os = 'iPhone';
            }
            else if (false !== stripos($agent, 'iPad')) {
                $os = 'iPad';
            }
            else if (false !== stripos($agent, 'PowerPC')) {
                $os = 'PowerPC';
            }
            else if (false !== stripos($agent, 'AIX')) {
                $os = 'AIX';
            }
            else if (false !== stripos($agent, 'HPUX')) {
                $os = 'HPUX';
            }
            else if (false !== stripos($agent, 'NetBSD')) {
                $os = 'NetBSD';
            }
            else if (false !== stripos($agent, 'BSD')) {
                $os = 'BSD';
            }
            else if (false !== stripos($agent, 'OSF1')) {
                $os = 'OSF1';
            }
            else if (false !== stripos($agent, 'IRIX')) {
                $os = 'IRIX';
            }
            else if (false !== stripos($agent, 'FreeBSD')) {
                $os = 'FreeBSD';
            }
            else if (false !== stripos($agent, 'teleport')) {
                $os = 'teleport';
            }
            else if (false !== stripos($agent, 'flashget')) {
                $os = 'flashget';
            }
            else if (false !== stripos($agent, 'webzip')) {
                $os = 'webzip';
            }
            else if (false !== stripos($agent, 'offline')) {
                $os = 'offline';
            }
            else if (false !== stripos($agent, 'os')) {
                if (false !== stripos($agent, 'sun')) {
                    $os = 'SunOS';
                }
                if (false !== stripos($agent, 'ibm')) {
                    $os = 'IBM OS/2';
                }
            }
            else {
                $os = 'Unknown';
            }
            $this->OS = $os;
        }

        /**
         * This function get current date and time
         * @return void
         */
        private function detectDatetime(): void{
            $this->datetime = DateTools::getCurrentDatetime('datetime')->format('Y-m-d D H:i:s');
        }

        /**
         * This function encoding all the info of client into json and return
         * @param bool $forLog if is true, return empty string instead of write LOG when meet JsonException Error
         * @return string
         * @throws JsonException
         */
        public function getClientInfo(bool $forLog=false): string{
            $returns = array(
                'address' => $this->address,
                'browser' => $this->browser,
                'IP' => $this->IP,
                /*'IP' => $this->OnlineIP*/
                'language' => $this->language,
                'OS' => $this->OS,
                'datetime' => $this->datetime
            );
            try {
                return json_encode($returns, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            } catch (JsonException $err) {
                if ($forLog!==true)  {
                    $this->logger->add_log(__FILE__.":".__LINE__, "DeviceAndDetector getClientInfo, json打包错误, 错误信息:\n{$err}", "Error", true);
                }
                throw $err;
            }
        }

        // In development

        /**
         * This function detect real IP of client.
         * @return void
         */
        private function detectOnlineIP(): void{ // 获得本地真实IP
            $mip = file_get_contents("http://city.ip138.com/city0.asp");
            if ($mip) {
                preg_match("/\[.*\]/", $mip, $sip);
                $p = array(
                    "/\[/",
                    "/\]/"
                );
                $this->onlineIP = preg_replace($p, "", $sip[0])??'获取本地IP失败';
            } else {
                $this->onlineIP = "获取本地IP失败！";
            }
        }
    }
}