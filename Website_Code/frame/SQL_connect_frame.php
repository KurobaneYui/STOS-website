<?php
class STOS_MySQL {
    //数据库账号信息
    private $servername = "172.27.0.9";
    private $username = "daily_user";
    private $password = "Daily_User1";
    //private $dbname = "phpConnectTest_info";
    private $dbname = "学风督导队_信息库";
    private $conn;

    function __construct() { // 创建连接
        $this->conn = new mysqli( $this->servername, $this->username, $this->password, $this->dbname );
        // Check connection
        if ( $this->conn->connect_errno ) {
            echo "Failed to connect to MySQL: (" .  $this->conn->connect_errno . ") " .  $this->conn->connect_error;
        }
    }

    function __destruct() { // 关闭连接
        $this->conn->close();
    }

    function get_conn() { // 获取连接变量
        return $this->conn;
    }

    function execute_query( $sql ) { // 执行SQL语句
        return $this->conn->query( $sql );
    }
    
    function search($table_name, $keyNames=false, $conditions_yes=false, $conditions_no=false) { //查询数据
        if ($keyNames)
        {
            if(is_array($keyNames))
                $sql_key = join(',', array_values($keyNames));
            else
                $sql_key = $keyNames;
        }
        else
            $sql_key = "*";

        if($conditions_yes)
        {
            $sql_condition_yes = array();
            foreach ($conditions_yes as $key=>$value)
            {
                array_push($sql_condition_yes, "{$key}='{$value}'");
            }
            $sql_condition_yes = join(' and ', $sql_condition_yes);
        }
        else
            $sql_condition_yes = "1";

        if($conditions_no)
        {
            $sql_condition_no = array();
            foreach ($conditions_no as $key=>$value)
            {
                array_push($sql_condition_no, "{$key}!='{$value}'");
            }
            $sql_condition_no = join(' and ', $sql_condition_no);
        }
        else $sql_condition_no = "1";

        $sql = "SELECT {$sql_key} FROM {$table_name} WHERE {$sql_condition_yes} and {$sql_condition_no};";
        return $this->conn->query($sql);
    }
    
    function insert($table_name, $key_value_names) { // 插入数据
        foreach($key_value_names as $key=>$value) {
            if($value!=="NULL") $key_value_names[$key]="'{$value}'";
        }
        $sql_key = join(',', array_keys($key_value_names));
        $sql_value = join(",", array_values($key_value_names));
        
        $sql = "INSERT INTO {$table_name} ({$sql_key}) VALUES ({$sql_value});";
		$returns = $this->conn->query($sql);
		$this->conn->commit();
        return $returns;
    }
    
    function update($table_name, $key_value_names, $conditions_yes=false, $conditions_no=false) { // 更新数据
        $sql_updateDATA = array();
        foreach($key_value_names as $key=>$value)
        {
            if($value!=="NULL")
                array_push($sql_updateDATA, "{$key}='{$value}'");
            else
                array_push($sql_updateDATA, "{$key}={$value}");
        }
        $sql_updateDATA = join(',', $sql_updateDATA);
        
        if($conditions_yes)
        {
            $sql_condition_yes = array();
            foreach ($conditions_yes as $key=>$value)
            {
                array_push($sql_condition_yes, "{$key}='{$value}'");
            }
            $sql_condition_yes = join(' and ', $sql_condition_yes);
        }
        else
            $sql_condition_yes = "1";

        if($conditions_no)
        {
            $sql_condition_no = array();
            foreach ($conditions_no as $key=>$value)
            {
                array_push($sql_condition_no, "{$key}!='{$value}'");
            }
            $sql_condition_no = join(' and ', $sql_condition_no);
        }
        else $sql_condition_no = "1";
        
        $sql = "UPDATE {$table_name} SET {$sql_updateDATA} WHERE {$sql_condition_yes} and {$sql_condition_no};";
		$returns = $this->conn->query($sql);
		$this->conn->commit();
        return $returns;
    }

    function personal_info_sigle($username) //获取特定个人信息-------------------------
    {
        $sql = "SELECT * FROM 成员信息 WHERE 学号={$username}";
        $result = $this->conn->query( $sql );
        return $result->fetch_assoc();
    }

    function personal_work_sigle($username) //获取特定岗位信息-------------------------
    {
        $sql = "SELECT * FROM 成员岗位 WHERE 学号={$username}";
        $result = $this->conn->query( $sql );
        return $result->fetch_assoc();
    }

    function depart_info() //获取部门信息-----------------------------
    {
        if ( isset( $this->conn ) ) {
            $sql = "SELECT * FROM 部门信息";
            return $this->conn->query( $sql );
        }
    }

    function personal_info() //获取成员信息-----------------------------
    {
        if ( isset( $this->conn ) ) {
            $sql = "SELECT * FROM 成员信息";
            return $this->conn->query( $sql );
        }
    }
    
    function personal_group($group)
    {
        if ( isset( $this->conn ) ) {
            $sql = "SELECT 学号 FROM 成员岗位 WHERE 所属组='{$group}' and 岗位<>'组长/队长';";
            return $this->conn->query($sql);
        }
    }

    function personal_work() //获取成员岗位信息-----------------------------
    {
        if ( isset( $this->conn ) ) {
            $sql = "SELECT * FROM 成员岗位";
            return $this->conn->query( $sql );
        }
    }
}

class STOS_MySQL_data {
    //数据库账号信息
    private $servername = "172.27.0.9";
    private $username = "daily_user";
    private $password = "Daily_User1";
    //private $dbname = "phpConnectTest_data";
    private $dbname = "学风督导队_数据库";
    private $conn;

    function __construct() { // 创建连接
        $this->conn = new mysqli( $this->servername, $this->username, $this->password, $this->dbname );
        // Check connection
        if ( $this->conn->connect_errno ) {
            echo "Failed to connect to MySQL: (" .  $this->conn->connect_errno . ") " .  $this->conn->connect_error;
        }
    }

    function __destruct() { // 关闭连接
        $this->conn->close();
    }

    function get_conn() { // 获取连接变量
        return $this->conn;
    }

    function execute_query( $sql ) { // 执行SQL语句
        return $this->conn->query( $sql );
    }
    
    function search($table_name, $keyNames=false, $conditions_yes=false, $conditions_no=false) { //查询数据
        if ($keyNames)
        {
            if(is_array($keyNames))
                $sql_key = join(',', array_values($keyNames));
            else
                $sql_key = $keyNames;
        }
        else
            $sql_key = "*";

        if($conditions_yes)
        {
            $sql_condition_yes = array();
            foreach ($conditions_yes as $key=>$value)
            {
                array_push($sql_condition_yes, "{$key}='{$value}'");
            }
            $sql_condition_yes = join(' and ', $sql_condition_yes);
        }
        else
            $sql_condition_yes = "1";

        if($conditions_no)
        {
            $sql_condition_no = array();
            foreach ($conditions_no as $key=>$value)
            {
                array_push($sql_condition_no, "{$key}!='{$value}'");
            }
            $sql_condition_no = join(' and ', $sql_condition_no);
        }
        else $sql_condition_no = "1";

        $sql = "SELECT {$sql_key} FROM {$table_name} WHERE {$sql_condition_yes} and {$sql_condition_no};";
        return $this->conn->query($sql);
    }
    
    function insert($table_name, $key_value_names) { // 插入数据
        foreach($key_value_names as $key=>$value) {
            if($value!=="NULL") $key_value_names[$key]="'{$value}'";
        }
        $sql_key = join(',', array_keys($key_value_names));
        $sql_value = join(",", array_values($key_value_names));
        
        $sql = "INSERT INTO {$table_name} ({$sql_key}) VALUES ({$sql_value});";
		$returns = $this->conn->query($sql);
		$this->conn->commit();
        return $returns;
    }
    
    function update($table_name, $key_value_names, $conditions_yes=false, $conditions_no=false) { // 更新数据
        $sql_updateDATA = array();
        foreach($key_value_names as $key=>$value)
        {
            if($value!=="NULL")
                array_push($sql_updateDATA, "{$key}='{$value}'");
            else
                array_push($sql_updateDATA, "{$key}={$value}");
        }
        $sql_updateDATA = join(',', $sql_updateDATA);
        
        if($conditions_yes)
        {
            $sql_condition_yes = array();
            foreach ($conditions_yes as $key=>$value)
            {
                array_push($sql_condition_yes, "{$key}='{$value}'");
            }
            $sql_condition_yes = join(' and ', $sql_condition_yes);
        }
        else
            $sql_condition_yes = "1";

        if($conditions_no)
        {
            $sql_condition_no = array();
            foreach ($conditions_no as $key=>$value)
            {
                array_push($sql_condition_no, "{$key}!='{$value}'");
            }
            $sql_condition_no = join(' and ', $sql_condition_no);
        }
        else $sql_condition_no = "1";
        
        $sql = "UPDATE {$table_name} SET {$sql_updateDATA} WHERE {$sql_condition_yes} and {$sql_condition_no};";
		$returns = $this->conn->query($sql);
		$this->conn->commit();
        return $returns;
    }
}
?>