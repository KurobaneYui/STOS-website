<?php
require __DIR__.'/../../ROOT_PATH.php';
require ROOT_PATH . '/Frame/php/DateTools.php';
require ROOT_PATH . '/Frame/php/Database_connector.php';
require ROOT_PATH . '/Frame/php/Person.php';

if (!class_exists('DeviceAndIPDetector')) {
    // 作用取得客户端的ip、地理位置、浏览器、以及访问设备
    class DeviceAndIPDetector
    {
        public $browser;
        public $address;
        public $language;
        public $system;
        public $ip;
        public $datetime;

        public function __construct()
        {
            $this->Getaddress();
            $this->GetBrowser();
            $this->Getip();
            $this->GetLang();
            $this->GetOs();
            $this->GetDatetime();
        }

        private function GetDatetime() {
            $this->datetime = (new DateTools())->based_datetime()->format('Y-m-d D H:i:s');
        }

        // 获得访客浏览器类型
        private function GetBrowser()
        {
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

            $this->browser = 'Error';
        }

        // 获得访客浏览器语言
        private function GetLang()
        {
            if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
                $lang = substr($lang, 0, 5);
                if (false !== stripos($lang, 'zh-cn')) {
                    $lang = '简体中文';
                } elseif (false !== stripos($lang, 'zh')) {
                    $lang = '繁体中文';
                } else {
                    $lang = 'English';
                }
                $this->language = $lang;
                return;
            }

            $this->language = 'Error';
        }

        // 获取客户端操作系统信息包括win10
        private function GetOs()
        {
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
            else if (false !== stripos($agent, 'os')) {
                if (false !== stripos($agent, 'sun')) {
                    $os = 'SunOS';
                }
                if (false !== stripos($agent, 'ibm')) {
                    $os = 'IBM OS/2';
                }
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
            else {
                $os = 'Unknown';
            }
            $this->system = $os;
        }

        // 获得访客真实ip
        private function Getip()
        {
            $ip = '';
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
                if ($ip) {
                    $ips = array_unshift($ips, $ip);
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
//                return $this->get_onlineip();
//            }

            $this->ip = $tip;
        }

        // 根据ip获得访客所在地地名
        private function Getaddress()
        {
            if (empty($this->ip)) {
                $this->Getip();
            }
            $ip_add = file_get_contents('https://whois.pconline.com.cn/ipJson.jsp?json=true&ip=' . $this->ip); // 根据新浪api接口获取
            if ($ip_add) {
                $charset = iconv('gbk', 'utf-8', $ip_add);
                $charset = json_decode($charset,true);
                $this->address = $charset['addr']; // 返回一个二维数组
                return;
            }

            $this->address='';
        }

        // 获得本地真实IP
        private function get_onlineip()
        {
           $mip = file_get_contents("http://city.ip138.com/city0.asp");
           if ($mip) {
             preg_match("/\[.*\]/", $mip, $sip);
             $p = array(
               "/\[/",
               "/\]/"
             );
             return preg_replace($p, "", $sip[0]);
           } else {
             return "获取本地IP失败！";
           }
        }

        public function get_Device_IP_info() {
            $returns = array(
                'browser' => $this->browser,
                'language' => $this->language,
                'system' => $this->system,
                'IP' => $this->ip,
                'address' => $this->address,
                'datetime' => $this->datetime
            );
            return json_encode($returns, JSON_UNESCAPED_UNICODE);
        }

        public  function uploadDeviceInfo($studentID) {
            if ((new Person($studentID))->exist()) {
                $device_info = $this->get_Device_IP_info();
                $con = new Database_connector(ROOT_PATH.'/config/DataBase_STSA.conf');
                $con->query("update 权限密码登录信息 set 上一次登录信息=本次登录信息, 本次登录信息='{$device_info}' where 学号='{$studentID}'");
            }
            return false;
        }
    }
}