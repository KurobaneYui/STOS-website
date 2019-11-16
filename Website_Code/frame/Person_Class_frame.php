<?php
require("SQL_connect_frame.php"); // 本脚本中所有类均涉及和MySQL数据库连接，故引入连接所需的类
require("tools.php"); // 本脚本中涉及日期计算函数，故引入包含所需小工具的函数文件

class person_all_info{ // 个人信息
    //状态量
    private $connection; // 数据库连接
    private $personal_isexist = array
        (
            "info"=>false,
            "work"=>false,
            "mima"=>false
        ); // 个人信息是否已经存在于数据库：bool
    private $can_upload = array
        (
            "info"=>false,
            "work"=>false,
            "mima"=>false
        ); // 个人信息&个人岗位-是否可以提交至数据库：{info:bool, work:bool}
    private $upload_method = array
        (
            "info"=>"insert",
            "work"=>"insert",
            "mima"=>"insert"
        ); // 个人信息&个人岗位-数据库提交方式：{info:update-insert-delete, work:update-insert-delete}
    //固定信息
    private $suoshuzu; // 所属组
    private $gangwei; // 岗位
    private $gongzi=0; // 工资
    private $quanxian; // 权限
    private $guanlizu = array(); // 管理组（一位副队同时管理早餐组，故设此变量记录除职位外的管理身份）
    private $zhouyikongke; // 周一空课
    private $zhouerkongke; // 周二空课
    private $zhousankongke; // 周三空课
    private $zhousikongke; // 周四空课
    private $zhouwukongke; // 周五空课
    private $beizhu=""; // 备注
    //个人信息
    public $xinming; // 姓名
    public $xueyuan; // 学院
    public $xuehao; // 学号
    public $xinbie; // 性别
    public $minzu; // 民族
    public $jiguan; // 籍贯
    public $dianhua; // 电话
    public $QQ; // QQ号
    public $qinshi_yuan; // 寝室_苑
    public $qinshi_lou; // 寝室_楼
    public $qinshi_hao; // 寝室_号
    public $gongzishenqingshiyinhangkahao; // 工资申请时银行卡号
    public $gongzishenqingshixingming; // 工资申请时姓名
    public $gongzishenqingshixuehao; // 工资申请时学号
    public $recorder; // 建档立卡
    public $mima; // 密码

    function __construct($xuehao) { // 构造函数
        $this->xuehao = $xuehao;
        $this->connection = new STOS_MySQL();
        $this->get_personal_info();
        $this->get_personal_work_info();
        $this->get_mima();
        $this->get_authentic_and_groupManage();
    }

    function __destruct() { // 析构函数
        $this->connection->__destruct();
    }
    
    function get_personal_info() { // 获取个人信息
        $result = $this->connection->search("成员信息", false, array("学号"=>$this->xuehao), false)->fetch_assoc();
        if($result)
        {
            $this->personal_isexist["info"] = true;
            $this->upload_method["info"] = "update";
            $this->can_upload["info"] = true;
            
            $this->xinming = $result["姓名"];
            $this->xinbie = $result["性别"];
            $this->xueyuan = $result["学院"];
            $this->minzu = $result["民族"];
            $this->jiguan = $result["籍贯"];
            $this->dianhua = $result["电话"];
            $this->QQ = $result["QQ"];
            $this->gongzishenqingshiyinhangkahao = $result["工资申请时银行卡号"];
            $this->gongzishenqingshixingming = $result["工资申请时姓名"];
            $this->gongzishenqingshixuehao = $result["工资申请时学号"];
            $this->recorder = $result["建档立卡"];
            $this->qinshi_yuan = explode("-", $result["寝室号"])[0];
            $this->qinshi_lou = explode("-", $result["寝室号"])[1];
            $this->qinshi_hao = explode("-", $result["寝室号"])[2];
        }
        else
        {
            $this->personal_isexist["info"] = false;
            $this->upload_method["info"] = "insert";
            $this->can_upload["info"] = false;
        }
    }
    
    function get_personal_work_info() { // 获取个人岗位信息
        $result = $this->connection->search("成员岗位", false, array("学号"=>$this->xuehao), false)->fetch_assoc();
        if($result)
        {
            $this->personal_isexist["work"] = true;
            $this->upload_method["work"] = "update";
            $this->can_upload["work"] = true;
            
            $this->zhouyikongke = $result["周一空课"];
            $this->zhouerkongke = $result["周二空课"];
            $this->zhousankongke = $result["周三空课"];
            $this->zhousikongke = $result["周四空课"];
            $this->zhouwukongke = $result["周五空课"];
            $this->suoshuzu = $result["所属组"];
            $this->gangwei = $result["岗位"];
            $this->gongzi = $result["工资"];
            $this->beizhu = $result["备注"];
        }
        else
        {
            $this->personal_isexist["work"] = false;
            $this->upload_method["work"] = "insert";
            $this->can_upload["work"] = false;
        }
    }
    
    function get_authentic_and_groupManage() { // 获取权限信息&管理组
        $result = $this->connection->search("权限信息", false, array("学号"=>$this->xuehao), false)->fetch_assoc();
        if($result)
            $this->quanxian = $result["权限"];
        
        $result = $this->connection->search("部门信息", false, array("组长"=>$this->xuehao), false)->fetch_assoc();
        if($result)
            array_push($this->guanlizu, $result["部门"]);
        if($this->suoshuzu=="队长" and $result["部门"]!="队长")
            array_push($this->guanlizu, "队长");
    }
    
    function get_mima() { // 获取登陆密码
        $result = $this->connection->search("登陆信息", false, array("学号"=>$this->xuehao), false)->fetch_assoc();
        if($result)
        {
            $this->personal_isexist["mima"] = true;
            $this->upload_method["mima"] = "update";
            $this->can_upload["mima"] = true;
            
            $this->mima = $result["密码"];
        }
        else
        {
            $this->personal_isexist["mima"] = false;
            $this->upload_method["mima"] = "insert";
            $this->can_upload["mima"] = false;
        }
    }
    
    function upload_personal_info() { // 个人信息提交数据库
        $this->check_data(); // 检查数据合法性
        if($this->can_upload["info"]) // 确认数据合法
        {
            $data = array
                (
                    "姓名"=>$this->xinming, // 姓名
                    "学院"=>$this->xueyuan, // 学院
                    "学号"=>$this->xuehao, // 学号
                    "性别"=>$this->xinbie, // 性别
                    "民族"=>$this->minzu, // 民族
                    "籍贯"=>$this->jiguan, // 籍贯
                    "电话"=>$this->dianhua, // 电话
                    "QQ"=>$this->QQ, // QQ号
                    "寝室号"=>join
                        ('-',array
                         (
                             $this->qinshi_yuan, // 寝室_苑
                             $this->qinshi_lou, // 寝室_楼
                             $this->qinshi_hao // 寝室_号
                         )
                        ),
                    "工资申请时银行卡号"=>$this->gongzishenqingshiyinhangkahao, // 工资申请时银行卡号
                    "工资申请时姓名"=>$this->gongzishenqingshixingming,
                    "工资申请时学号"=>$this->gongzishenqingshixuehao,
                    "建档立卡"=>$this->recorder
                );
            if($this->upload_method["info"]=="insert") // 判断提交方式
            {
                $this->connection->insert("成员信息", $data);
            }
            elseif($this->upload_method["info"]=="update") // 判断提交方式
            {
                $this->connection->update("成员信息", $data, array("学号"=>$this->xuehao), false);
            }
            
            $this->personal_isexist["info"] = true;
            $this->upload_method["info"] = "update";
        }
        else
            return false;
    }
    
    function upload_personal_work_info() { // 个人岗位信息提交数据库
        $this->check_data(); // 检查数据合法性
        if($this->can_upload["work"]) // 确认数据合法
        {
            $data = array
                (
                    "学号"=>$this->xuehao, // 学号
                    "周一空课"=>$this->zhouyikongke, // 周一空课
                    "周二空课"=>$this->zhouerkongke, // 周二空课
                    "周三空课"=>$this->zhousankongke, // 周三空课
                    "周四空课"=>$this->zhousikongke, // 周四空课
                    "周五空课"=>$this->zhouwukongke, // 周五空课
                    "所属组"=>$this->suoshuzu?:"NULL", // 所属组
                    "岗位"=>$this->gangwei?:"NULL", // 岗位
                    "工资"=>$this->gongzi, // 工资
                    "备注"=>$this->beizhu?:"NULL" // 备注
                );
            if($this->upload_method["work"]=="insert") // 判断提交方式
            {
                $this->connection->insert("成员岗位", $data);
            }
            elseif($this->upload_method["work"]=="update") // 判断提交方式
            {
                $this->connection->update("成员岗位", $data, array("学号"=>$this->xuehao), false);
            }
            
            $this->personal_isexist["work"] = true;
            $this->upload_method["work"] = "update";
        }
        else
            return false;
    }
    
    function init_authentic_work_info() { // 权限信息提交数据库
        $this->zhouyikongke=
        $this->zhouerkongke=
        $this->zhousankongke=
        $this->zhousikongke=
        $this->zhouwukongke="0000";
        $this->quanxian='0';
        $this->suoshuzu="";
        $this->gangwei="";
        $this->beizhu="";
        $this->gongzi=0;
    }
    
    function upload_mima() { // 登陆密码提交数据库
        $this->check_data(); // 检查数据合法性
        if($this->can_upload["mima"]) // 确认数据合法
        {
            $data = array
                (
                    "学号"=>$this->xuehao, // 学号
                    "密码"=>$this->mima // 密码
                );
            if($this->upload_method["mima"]=="insert") // 判断提交方式
            {
                $this->connection->insert("登陆信息", $data);
            }
            elseif($this->upload_method["mima"]=="update") // 判断提交方式
            {
                $this->connection->update("登陆信息", $data, array("学号"=>$this->xuehao), false);
            }
            
            $this->personal_isexist["mima"] = true;
            $this->upload_method["mima"] = "update";
        }
        else
            return false;
    }
    
    function insert_authentic() {
        $data = array
            (
                "学号"=>$this->xuehao,
                "权限"=>$this->quanxian
            );
        $this->connection->insert("权限信息", $data);
    }
    
    function update_authentic() {
        $data = array
            (
                "学号"=>$this->xuehao,
                "权限"=>$this->quanxian
            );
        $this->connection->update("权限信息", $data);
    }
    
    function check_data() { // 检查数据合法性
        $result = $this->connection->execute_query
            ("SELECT 部门 FROM 部门信息;");
        $GLOBALS["dudaoduibumen"] = array();
        while($s = $result->fetch_assoc()["部门"])
            array_push($GLOBALS["dudaoduibumen"],$s);
        
        if (!function_exists("gender_filter")){ function gender_filter($gender) // 性别过滤器
        {
            $pattern = "/^男|女$/";
            if(preg_match_all($pattern,$gender))
                return $gender;
            else
                return false;
        }}

        if (!function_exists("recorder_filter")){ function recorder_filter($recorder) // 建档立卡过滤器
        {
            $pattern = "/^是|否$/";
            if(preg_match_all($pattern,$recorder))
                return $recorder;
            else
                return false;
        }}

        if (!function_exists("studentID_filter")){ function studentID_filter($studentID) { // 学号过滤器
            $pattern = "/^20[1-3]\d[a-zA-Z0-9]{8,9}$/";
            if(preg_match_all($pattern,$studentID))
                return $studentID;
            else
                return false;
        }}
        
        if (!function_exists("dormitory_filter")){ function dormitory_filter( $dormitory ) { // 寝室_苑过滤器
            $pattern = "/^(学知苑)|(硕丰苑)$/";
            if(preg_match_all($pattern,$dormitory))
                return $dormitory;
            else
                return false;
        }}
        
        if (!function_exists("dormitory_num_filter")){ function dormitory_num_filter( $dormitory_num ) { // 寝室_号过滤器
            $pattern = "/^[1-6][0-8]\d$/";
            if(preg_match_all($pattern,$dormitory_num)){
                return $dormitory_num;}
            else
                return false;
        }}
        
        if (!function_exists("bank_filter")){ function bank_filter($bank) { // 银行卡号过滤器
            if(strlen($bank) == 19) return filter_var($bank,FILTER_VALIDATE_INT); else return false;
        }}

        if (!function_exists("pw_filter")){ function pw_filter($pw) { // 密码过滤器
            if(strlen($pw)>5 and strlen($pw)<19 and filter_var($pw,FILTER_SANITIZE_EMAIL)==$pw)
                return filter_var($pw,FILTER_SANITIZE_EMAIL);
            else
                return false;
        }}
        
        if (!function_exists("empty_filter")){ function empty_filter($emptytime){ // 空课时间过滤器
            $pattern = "/^[0-3]{4}$/";
            if(preg_match_all($pattern,$emptytime))
            {
                return $emptytime;
            }
            else
                return false;
        }}
        
        if (!function_exists("work_filter")){ function work_filter($work) { // 岗位过滤器
            if($work=="")
                return $work;
            $pattern = "/^(组员)|(组长\/队长)$/";
            if(preg_match_all($pattern,$work))
                return $work;
            else
                return false;
        }}
        
        if (!function_exists("group_filter")){ function group_filter($group) { // 所属组过滤器
            if($group=="")
                return $group;
            else
            {
                foreach($GLOBALS["dudaoduibumen"] as $value)
                {
                    if($value == $group) return $group;
                }
                return false;
            }
        }}
        
        if (!function_exists("money_filter")){ function money_filter($money) { // 工资过滤器
            return filter_var($money,FILTER_VALIDATE_INT,
                       array("min_range"=>1200,"max_range"=>0));
        }}

        $filters_info = array
        (
            "姓名" => FILTER_SANITIZE_STRING,
            "性别" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"gender_filter"
            ),
            "学院" => FILTER_SANITIZE_STRING,
            "民族" => FILTER_SANITIZE_STRING,
            "籍贯" => FILTER_SANITIZE_STRING,
            "QQ" => FILTER_VALIDATE_INT,
            "电话" => array
            (
                "filter"=>FILTER_VALIDATE_INT,
                "options"=>array
                (
                    "min_range"=>10000000000,
                    "max_range"=>19999999999
                )
            ),
            "寝室_苑" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"dormitory_filter"
            ),
            "寝室_楼" => array
            (
                "filter"=>FILTER_VALIDATE_INT,
                "options"=>array
                (
                    "min_range"=>1,
                    "max_range"=>30
                )
            ),
            "寝室_号" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"dormitory_num_filter"
            ),
            "工资申请时银行卡号" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"bank_filter"
            ),
            "工资申请时姓名" => FILTER_SANITIZE_STRING,
            "工资申请时学号" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"studentID_filter"
            ),
            "建档立卡" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"recorder_filter"
            ),
            "学号" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"studentID_filter"
            )
        );
        $filters_mima = array
        (
            "密码" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"pw_filter"
            )
        );
        $filters_work = array
        (
            "周一空课" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"empty_filter"
            ),
            "周二空课" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"empty_filter"
            ),
            "周三空课" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"empty_filter"
            ),
            "周四空课" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"empty_filter"
            ),
            "周五空课" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"empty_filter"
            ),
            "所属组" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"group_filter"
            ),
            "岗位" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"work_filter"
            ),
            "工资" => array
            (
                "filter"=>FILTER_CALLBACK,
                "options"=>"money_filter"
            ),
            "备注" => array
            (
                "filter"=>FILTER_SANITIZE_STRING
            )
        );
        
        $returns = array();
        $data = array
        (
            "姓名"=>$this->xinming, // 姓名
            "学院"=>$this->xueyuan, // 学院
            "学号"=>$this->xuehao, // 学号
            "性别"=>$this->xinbie, // 性别
            "民族"=>$this->minzu, // 民族
            "籍贯"=>$this->jiguan, // 籍贯
            "电话"=>$this->dianhua, // 电话
            "QQ"=>$this->QQ, // QQ号
            "寝室_苑" => $this->qinshi_yuan, // 寝室_苑
            "寝室_楼" => $this->qinshi_lou, // 寝室_楼
            "寝室_号" => $this->qinshi_hao, // 寝室_号
            "工资申请时银行卡号"=>$this->gongzishenqingshiyinhangkahao, // 工资申请时银行卡号
            "工资申请时姓名"=>$this->gongzishenqingshixingming, // 工资申请时姓名
            "工资申请时学号"=>$this->gongzishenqingshixuehao, // 工资申请时学号
            "建档立卡"=>$this->recorder // 建档立卡
        );
        $result = filter_var_array($data, $filters_info);
        $this->can_upload["info"] = true;
        foreach($result as $key=>$value)
            if(!$value) {
                $this->can_upload["info"] = false;
                $returns[$key] = $value;
            }
        
        $data = array
        (
            "周一空课"=>$this->zhouyikongke, // 周一空课
            "周二空课"=>$this->zhouerkongke, // 周二空课
            "周三空课"=>$this->zhousankongke, // 周三空课
            "周四空课"=>$this->zhousikongke, // 周四空课
            "周五空课"=>$this->zhouwukongke, // 周五空课
            "所属组"=>$this->suoshuzu, // 所属组
            "岗位"=>$this->gangwei, // 岗位
            "工资"=>$this->gongzi, // 工资
            "备注"=>$this->beizhu // 备注
        );
        $result = filter_var_array($data, $filters_work);
        $this->can_upload["work"] = true;
        foreach($result as $key=>$value)
            //if($key=="备注")continue;
            if($value===false) {
                $this->can_upload["work"] = false;
                $returns[$key] = $value;
            }
        
        $data = array
        (
            "密码"=>$this->mima // 密码
        );
        $result = filter_var_array($data, $filters_mima);
        $this->can_upload["mima"] = true;
        foreach($result as $key=>$value)
            if(!$value) {
                $this->can_upload["mima"] = false;
                $returns[$key] = $value;
            }
        if(count($returns)==0)return true;
        else return $returns;
    }
    
    function set_upload_method($metiod) { // 设置数据库提交方式
        foreach($metiod as $key=>$value)
        {
            if(isset($this->upload_method[$key]))
                $this->upload_method[$key] = $value;
        }
    }
    
    function already_had() { // 返回是否已经在数据库中存在
        return $this->personal_isexist['info'];
    }
    
    function work_info() { // 返回工作岗位信息
        $data = array
        (
            "周一空课"=>$this->zhouyikongke, // 周一空课
            "周二空课"=>$this->zhouerkongke, // 周二空课
            "周三空课"=>$this->zhousankongke, // 周三空课
            "周四空课"=>$this->zhousikongke, // 周四空课
            "周五空课"=>$this->zhouwukongke, // 周五空课
            "所属组"=>$this->suoshuzu, // 所属组
            "岗位"=>$this->gangwei, // 岗位
            "工资"=>$this->gongzi, // 工资
            "备注"=>$this->beizhu, // 备注
            "管理组"=>$this->guanlizu, // 管理组
            "权限"=>$this->quanxian // 权限
        );
        return ($data);
    }
}

class self_study_data_single { // 早自习数据类（单日）
    //状态量
    private $is_exist = false;
    private $can_upload = false;
    private $upload_method = "insert";
    private $connection;
    //提交者信息
    private $person; // 提交者学号
    //数据部分
    public $riqi; // 数据对应日期，请使用PHP的data函数将格式调整为"Y-m-d"
    public $xueyuan; // 教室对应学院
    public $yingdaorenshu; // 教室应到人数
    public $jiaoxuelou; // 教学楼
    public $qvhao; // 区号
    public $jiaoshibianhao; // 教室编号
    public $diyicichuqin; // 单日第一次出勤
    public $diercichuqin; // 单日第二次出勤
    public $weijirenshu; // 单日违纪人数
    public $chidaorenshu; // 单日迟到人数
    public $zaotuirenshu; // 单日早退人数
	public $qinjiarenshu; // 单日请假人数
    public $beizhu; // 备注
    
    function __construct($riqi,$jiaoxuelou,$qvhao,$jiaoshibianhao,$personID) { // 构造函数
        $this->connection = new STOS_MySQL_data();
        $this->riqi = $riqi;
        $this->jiaoxuelou = $jiaoxuelou;
        $this->qvhao = $qvhao;
        $this->jiaoshibianhao = $jiaoshibianhao;
        $this->get_classroom_data();
        $this->person = $personID;
        
        $re = $this->connection->search("查早排班",array("学院","应到人数"),array("教学楼"=>$this->jiaoxuelou,"区号"=>$this->qvhao,"教室编号"=>$this->jiaoshibianhao,"周起始日期"=>getWeekRange(strtotime($this->riqi),1)[0]),false)->fetch_assoc();
        $this->xueyuan = $re["学院"];
        $this->yingdaorenshu = $re["应到人数"];
        unset($re);
    }
    
    function __destruct() {
        $this->connection->__destruct();
    }
    
    function get_classroom_data() { // 获取教室数据
        $conditions = array
            (
                "日期"=>$this->riqi,
                "教学楼"=>$this->jiaoxuelou,
                "区号"=>$this->qvhao,
                "教室编号"=>$this->jiaoshibianhao
            );
        $result = $this->connection->search("查早数据", false, $conditions, false)->fetch_assoc();
	if($result){
            $this->is_exist = true;
            $this->can_upload = true;
            $this->upload_method = "update";
            
            $this->riqi = $result["日期"];
            $this->jiaoxuelou = $result["教学楼"];
            $this->qvhao = $result["区号"];
            $this->jiaoshibianhao = $result["教室编号"];
            $this->diyicichuqin = json_decode($result["教室数据"], true)["第一次出勤"];
            $this->diercichuqin = json_decode($result["教室数据"], true)["第二次出勤"];
            $this->weijirenshu = json_decode($result["教室数据"], true)["违纪人数"];
            $this->chidaorenshu = json_decode($result["教室数据"], true)["迟到人数"];
            $this->zaotuirenshu = json_decode($result["教室数据"], true)["早退人数"];
			$this->qinjiarenshu = json_decode($result["教室数据"], true)["请假人数"];
            $this->beizhu = json_decode($result["教室数据"], true)["备注"];
        }
        else {
            $this->is_exist = false;
            $this->can_upload = false;
            $this->upload_method = "insert";
        }
    }
    
    function already_had() { // 返回是否已经在数据库中存在
        return $this->is_exist;
    }
    
    function upload_classroom_info() { // 教室数据信息提交数据库
        $this->check_data(); // 检查数据合法性
        if($this->can_upload) { // 确认合法性
            $data = array
                (
                    "日期"=>$this->riqi, // 日期
                    "教学楼"=>$this->jiaoxuelou, // 教学楼
                    "区号"=>$this->qvhao, // 区号
                    "教室编号"=>$this->jiaoshibianhao, // 教室编号
                    "教室数据"=>json_encode(array
                            (
                                "第一次出勤"=>$this->diyicichuqin,
                                "第二次出勤"=>$this->diercichuqin,
                                "迟到人数"=>$this->chidaorenshu,
                                "早退人数"=>$this->zaotuirenshu,
                                "违纪人数"=>$this->weijirenshu,
								"请假人数"=>$this->qinjiarenshu,
                                "备注"=>str_replace("\r","\\r",str_replace("\n","\\n",$this->beizhu))
                            )
                            , JSON_UNESCAPED_UNICODE),
                    "提交者"=>$this->person // 提交者学号
                );
            if($this->upload_method=="insert") { // 判断提交方式
                $this->connection->insert("查早数据", $data);
            }
            elseif($this->upload_method=="update") { // 判断提交方式
                $conditions = array
                    (
                        "日期"=>$this->riqi,
                        "教学楼"=>$this->jiaoxuelou,
                        "区号"=>$this->qvhao,
                        "教室编号"=>$this->jiaoshibianhao
                    );
                $this->connection->update("查早数据", $data, $conditions, false);
            }

            $this->is_exist = true;
            $this->upload_method = "update";
            return true;
        }
        else
            return false;
    }
    
    function check_data() { // 检查数据合法性
	$this->can_upload = true;
        return true;
    }

    function set_upload_method($metiod) { // 设置数据库提交方式
        $this->upload_method = $metiod;
    }
}

class self_study_data_set { // 早自习数据类（单人一周内可录入数据集合）
    // 状态信息
    private $can_change_classroom_info = array(
    "周一"=>true,
    "周二"=>true,
    "周三"=>true,
    "周四"=>true,
    "周五"=>true,
    "周六"=>true,
    "周日"=>true);
    // 固定信息
    private $person; // 设定获取教室数据的成员ID
    private $data_Monday; // 本周周一的公元纪年日期
    private $data_Tuesday; // 本周周二的公元纪年日期
    private $data_Wednesday; // 本周周三的公元纪年日期
    private $data_Thursday; // 本周周四的公元纪年日期
    private $data_Friday; // 本周周五的公元纪年日期
    private $data_Saturday; // 本周周六的公元纪年日期
    private $data_Sunday; // 本周周七的公元纪年日期
    //安排的教室信息
    private $jiaoxuelou;
    private $qvhao;
    private $jiaoshibianhao;
    //录入教室信息
    public $Monday_classroom;
    public $Tuesday_classroom;
    public $Wednesday_classroom;
    public $Thursday_classroom;
    public $Friday_classroom;
    public $Saturday_classroom;
    public $Sunday_classroom;
    public $classroom_addition = array(); // 代查教室数组

    function __construct($personID, $current_time) {
        $this->person = $personID;
        $this->data_Monday = getWeekRange($current_time, 1)[0];
        $this->data_Tuesday = date("Y-m-d",strtotime("{$this->data_Monday}+1day"));
        $this->data_Wednesday = date("Y-m-d",strtotime("{$this->data_Monday}+2day"));
        $this->data_Thursday = date("Y-m-d",strtotime("{$this->data_Monday}+3day"));
        $this->data_Friday = date("Y-m-d",strtotime("{$this->data_Monday}+4day"));
        $this->data_Saturday = date("Y-m-d",strtotime("{$this->data_Monday}+5day"));
        $this->data_Sunday = date("Y-m-d",strtotime("{$this->data_Monday}+6day"));

        $connection_data = new STOS_MySQL_data(); 
        $result = $connection_data->search("查早排班",false,array("查早组员"=>$personID,"周起始日期"=>$this->data_Monday),false)->fetch_assoc();
        $this->jiaoxuelou = $result["教学楼"];
        $this->qvhao = $result["区号"];
        $this->jiaoshibianhao = $result["教室编号"];

        $this->Monday_classroom = new self_study_data_single($this->data_Monday, $this->jiaoxuelou, $this->qvhao, $this->jiaoshibianhao, $this->person);
        $this->Tuesday_classroom = new self_study_data_single($this->data_Tuesday, $this->jiaoxuelou, $this->qvhao, $this->jiaoshibianhao, $this->person);
        $this->Wednesday_classroom = new self_study_data_single($this->data_Wednesday, $this->jiaoxuelou, $this->qvhao, $this->jiaoshibianhao, $this->person);
        $this->Thursday_classroom = new self_study_data_single($this->data_Thursday, $this->jiaoxuelou, $this->qvhao, $this->jiaoshibianhao, $this->person);
        $this->Friday_classroom = new self_study_data_single($this->data_Friday, $this->jiaoxuelou, $this->qvhao, $this->jiaoshibianhao, $this->person);
        $this->Saturday_classroom = new self_study_data_single($this->data_Saturday, $this->jiaoxuelou, $this->qvhao, $this->jiaoshibianhao, $this->person);
        $this->Sunday_classroom = new self_study_data_single($this->data_Sunday, $this->jiaoxuelou, $this->qvhao, $this->jiaoshibianhao, $this->person);

//        $result = $connection_data->search("早自习代查安排", false, array("接受组员"=>$personID),false)->fetch_assoc(); // 统计是否接受代查
//        载入代查教室
//
//        $result = $connection_data->search("早自习代查安排", "代查日期", array("申请组员"=>$personID),false); // 如果某时段申请代查，则不提供数据录入功能
//        while($te = $result->fetch_assoc()["代查日期"]) {
//            if($te == $this->data_Monday) $this->can_change_classroom_info["周一"] = false;
//            elseif($te == $this->data_Tuesday) $this->can_change_classroom_info["周二"] = false;
//            elseif($te == $this->data_Wednesday) $this->can_change_classroom_info["周三"] = false;
//            elseif($te == $this->data_Thursday) $this->can_change_classroom_info["周四"] = false;
//            elseif($te == $this->data_Friday) $this->can_change_classroom_info["周五"] = false;
//            elseif($te == $this->data_Saturday) $this->can_change_classroom_info["周六"] = false;
//            elseif($te == $this->data_Sunday) $this->can_change_classroom_info["周日"] = false;
//        }
        $connection_data->__destruct();
    }
    
    function __destruct() {
        $this->Monday_classroom->__destruct();
        $this->Tuesday_classroom->__destruct();
        $this->Wednesday_classroom->__destruct();
        $this->Thursday_classroom->__destruct();
        $this->Friday_classroom->__destruct();
        $this->Saturday_classroom->__destruct();
        $this->Sunday_classroom->__destruct();
    }
	    
    function change_classroom_data($zhoushu, $data_array) { // 提交指定日期的教室数据
		$returns = true; // 默认插入成功
		
		if($zhoushu=="周一" and $this->can_change_classroom_info["周一"])
		    $current_classroom = &$this->Monday_classroom;
		elseif($zhoushu=="周二" and $this->can_change_classroom_info["周二"])
		    $current_classroom = &$this->Tuesday_classroom;
		elseif($zhoushu=="周三" and $this->can_change_classroom_info["周三"])
		    $current_classroom = &$this->Wednesday_classroom;
		elseif($zhoushu=="周四" and $this->can_change_classroom_info["周四"])
            $current_classroom = &$this->Thursday_classroom;
        elseif($zhoushu=="周五" and $this->can_change_classroom_info["周五"])
            $current_classroom = &$this->Friday_classroom;
        elseif($zhoushu=="周六" and $this->can_change_classroom_info["周六"])
            $current_classroom = &$this->Saturday_classroom;
        elseif($zhoushu=="周日" and $this->can_change_classroom_info["周日"])
            $current_classroom = &$this->Sunday_classroom;
        else
            $returns = "No such weekID";
   	
        if(isset($current_classroom)) {
            $current_classroom->diyicichuqin = $data_array["第一次出勤"];
            $current_classroom->diercichuqin = $data_array["第二次出勤"];
            $current_classroom->weijirenshu = $data_array["违纪人数"];
            $current_classroom->chidaorenshu = $data_array["迟到人数"];
            $current_classroom->zaotuirenshu = $data_array["早退人数"];
			$current_classroom->qinjiarenshu = $data_array["请假人数"];
            $current_classroom->beizhu = $data_array["备注"];
            
            if($current_classroom->upload_classroom_info()===false)
                $returns = $current_classroom->check_data();
            
            unset($current_classroom);
        }
        
	return $returns;
    }
    
    function get_classroom_info_array($zhoushu) { // 获取指定日期的教室学院和应到人数信息
        if($zhoushu=="周一")
            $current_classroom = &$this->Monday_classroom;
        elseif($zhoushu=="周二")
            $current_classroom = &$this->Tuesday_classroom;
        elseif($zhoushu=="周三")
            $current_classroom = &$this->Wednesday_classroom;
        elseif($zhoushu=="周四")
            $current_classroom = &$this->Thursday_classroom;
        elseif($zhoushu=="周五")
            $current_classroom = &$this->Friday_classroom;
        elseif($zhoushu=="周六")
            $current_classroom = &$this->Saturday_classroom;
        elseif($zhoushu=="周日")
            $current_classroom = &$this->Sunday_classroom;
        else
            $returns = "No such weekID";
        
        if(isset($current_classroom)) {
            $returns = array
                (
                    "学院"=>$current_classroom->xueyuan,
                    "应到人数"=>$current_classroom->yingdaorenshu
                );
            
            unset($current_classroom);
        }
        
        return $returns;
    }
    
    function get_data_array($zhoushu) { // 获取指定日期的教室数据
        if($zhoushu=="周一")
            $current_classroom = &$this->Monday_classroom;
        elseif($zhoushu=="周二")
            $current_classroom = &$this->Tuesday_classroom;
        elseif($zhoushu=="周三")
            $current_classroom = &$this->Wednesday_classroom;
        elseif($zhoushu=="周四")
            $current_classroom = &$this->Thursday_classroom;
        elseif($zhoushu=="周五")
            $current_classroom = &$this->Friday_classroom;
        elseif($zhoushu=="周六")
            $current_classroom = &$this->Saturday_classroom;
        elseif($zhoushu=="周日")
            $current_classroom = &$this->Sunday_classroom;
        else
            $returns = "No such weekID";
        
        if(isset($current_classroom)) {
            $returns = array
                (
                    "日期"=>$current_classroom->riqi,
                    "教学楼"=>$current_classroom->jiaoxuelou,
                    "区号"=>$current_classroom->qvhao,
                    "教室编号"=>$current_classroom->jiaoshibianhao,
                    "迟到人数"=>$current_classroom->chidaorenshu,
                    "第一次出勤"=>$current_classroom->diyicichuqin,
                    "违纪人数"=>$current_classroom->weijirenshu,
                    "第二次出勤"=>$current_classroom->diercichuqin,
                    "早退人数"=>$current_classroom->zaotuirenshu,
					"请假人数"=>$current_classroom->qinjiarenshu,
                    "备注"=>$current_classroom->beizhu
                );
            
            unset($current_classroom);
        }
        
        return $returns;
    }
    
    function get_classroomID() { // 获取待检查教室信息
        return array("教学楼"=>$this->jiaoxuelou,"区号"=>$this->qvhao,"教室编号"=>$this->jiaoshibianhao);
    }
}
?>
