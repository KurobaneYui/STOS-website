<?php

if (!class_exists('Database_connector')) {
    class Database_connector
    {
        private $CONNECTION_STATUS = false; // whether connection is established
        private $CONNECTION_SESSION; // connector
        private $CONNECTION_SERVER; // the host for connecting
        private $CONNECTION_USERNAME; // the username for connecting
        private $CONNECTION_PASSWORD; // the password for connecting
        private $CONNECTION_DATABASE; // the database name for connecting

        public function __construct($config_file ) {
            // Get config from file
            $conf = json_decode(file_get_contents($config_file), true);
            // Setting
            $this->CONNECTION_SERVER = $conf['host'];
            $this->CONNECTION_DATABASE = $conf['database'];
            $this->CONNECTION_USERNAME = $conf['user'];
            $this->CONNECTION_PASSWORD = $conf['password'];
            $this->CONNECTION_PORT = $conf['port'];
            $this->CONNECTION_SESSION = new mysqli( $this->CONNECTION_SERVER, $this->CONNECTION_USERNAME, $this->CONNECTION_PASSWORD, $this->CONNECTION_DATABASE, $this->CONNECTION_PORT );
        }

        public function __destruct() {
            $this->DISCONNECT();
        }

        private function CONNECT(): void { // try to establish connection
            // Check if already have connection
            if ($this->CONNECTION_STATUS){
                $this->DISCONNECT();
            }

            $this->CONNECTION_SESSION = new mysqli( $this->CONNECTION_SERVER, $this->CONNECTION_USERNAME, $this->CONNECTION_PASSWORD, $this->CONNECTION_DATABASE, $this->CONNECTION_PORT );

            // Check whether connection established
            if ($this->CONNECTION_SESSION->connect_errno){
                $this->CONNECTION_STATUS = false;
            }
            else{
                $this->CONNECTION_STATUS = true;
            }
        }

        private function DISCONNECT(): void { // try to terminate connection
            if ($this->CONNECTION_STATUS){ // check whether connection have already end
                $this->CONNECTION_SESSION->close();
                $this->CONNECTION_STATUS = false;
            }
        }

        public function status(): bool { // return the connection status
            return $this->CONNECTION_STATUS;
        }

        /**
         * @return array: an array contains error list, if none then return empty array
         */
        public function error_list(): array { // return error list, if none return an empty array
            if ($this->CONNECTION_STATUS) {
                return $this->CONNECTION_SESSION->error_list;
            }

            return array();
        }

        // public function: search database

        /**
         * @param string $table_name : database table name
         * @param array $keyNames : the keys need for search
         * @param array $conditions_yes : positive search conditions
         * @param array $conditions_no : negative search conditions
         * @param array $conditions_between : conditions between two value
         * @param array $conditions_in : conditions in an array
         * @param array $order : order the results, key is the name need order, and value is asc or desc
         * @param string $other_conditions : give other conditions for "where" in string type
         * @return bool|mysqli_result: return mysql->query($sql) result
         */
        public function search(string $table_name, array $keyNames=array(),
                               array $conditions_yes=array(), array $conditions_no=array(),
                               array $conditions_between=array(), array $conditions_in=array(),
                               array $order=array(),
                               string $other_conditions='')
        {
            if (!empty($keyNames)) // if not empty, then implode elements with ',', otherwise use '*' instead
            {
                    $sql_key = implode(',', array_values($keyNames));
            }
            else {
                $sql_key = '*';
            }

            if(!empty($conditions_yes))
            {
                $sql_condition_yes = array(); // transform from key-value groups to "key=value" string groups
                foreach ($conditions_yes as $key=>$value)
                {
                    $sql_condition_yes[] = "{$key}='{$value}'";
                }
                $sql_condition_yes = implode(' and ', $sql_condition_yes); // implode string array with 'and'
            }
            else {
                $sql_condition_yes = '1';
            }

            if(!empty($conditions_no))
            {
                $sql_condition_no = array(); // transform from key-value groups to "key=value" string groups
                foreach ($conditions_no as $key=>$value)
                {
                    $sql_condition_no[] = "{$key}!='{$value}'";
                }
                $sql_condition_no = implode(' and ', $sql_condition_no); // implode string array with 'and'
            }
            else {
                $sql_condition_no = '1';
            }

            if(!empty($conditions_between))
            {
                $sql_condition_between = array(); // transform to "key between value1 and value2" string groups
                foreach ($conditions_between as $key=>$value)
                {
                    $sql_condition_between[] = "`{$key}` between '{$value[0]}' and '{$value[1]}'";
                }
                $sql_condition_between = implode(' and ', $sql_condition_between);
            }
            else {
                $sql_condition_between = '1';
            }

            if(!empty($conditions_in))
            {
                $sql_condition_in = array(); // transform to "key in (value...)" string groups
                foreach ($conditions_in as $key=>$value)
                {
                    $sql_condition_in[] = "`{$key}` in ('" . implode("','", $value) . "'')";
                }
                $sql_condition_in = implode(' and ', $sql_condition_in); // implode string array with 'and'
            }
            else {
                $sql_condition_in = '1';
            }

            if(!empty($order))
            {
                $sql_order = array(); // transform to "order by ... asc ... desc" string groups
                foreach ($order as $key=>$value)
                {
                    $sql_order[] = "`{$key}` {$value}";
                }
                $sql_order = 'order by ' . implode(',', $sql_order); // implode string array with ','
            }
            else {
                $sql_order = '';
            }

            if($other_conditions==='')
            {
                $sql_other_conditions = '1';
            }
            else {
                $sql_other_conditions = $other_conditions;
            }

            $sql = "SELECT {$sql_key} FROM {$table_name} WHERE {$sql_condition_yes} and {$sql_condition_no} and {$sql_condition_between} and {$sql_condition_in} and {$sql_other_conditions} {$sql_order};";
            return $this->CONNECTION_SESSION->query($sql);
        }

        // public function:insert data into database
        /**
         * @param string $table_name: database table name
         * @param array $key_value_names: value that needed insert and key where insert to
         * @return bool|mysqli_result: return mysql->query($sql) result
         */
        public function insert(string $table_name, array $key_value_names) {
            foreach($key_value_names as $key=>$value) {
                if($value!== 'NULL') {
                    $key_value_names[$key] = "'{$value}'";
                }
            }
            $sql_key = implode(',', array_keys($key_value_names));
            $sql_value = implode(',', array_values($key_value_names));

            $sql = "INSERT INTO {$table_name} ({$sql_key}) VALUES ({$sql_value});";
            $returns = $this->CONNECTION_SESSION->query($sql);
            $this->CONNECTION_SESSION->commit();
            return $returns;
        }

        // public function: update data in database

        /**
         * @param string $table_name : database table name
         * @param array $key_value_names : value that needed update and key where update
         * @param array $conditions_yes : positive search conditions
         * @param array $conditions_no : negative search conditions
         * @param array $conditions_between : conditions between two value
         * @param array $conditions_in : conditions in an array
         * @param string $other_conditions : give other conditions for "where" in string type
         * @return bool|mysqli_result: return mysql->query($sql) result
         */
        public function update(string $table_name, array $key_value_names,
                               array $conditions_yes=array(), array $conditions_no=array(),
                               array $conditions_between=array(), array $conditions_in=array(),
                               string $other_conditions='')
        {
            $sql_updateDATA = array();
            foreach($key_value_names as $key=>$value) // transform from key-value groups to "key=value, key=value...."
            {
                if($value!== 'NULL') {
                    $sql_updateDATA[] = "{$key}='{$value}'";
                }
                else {
                    $sql_updateDATA[] = "{$key}={$value}";
                }
            }
            $sql_updateDATA = implode(',', $sql_updateDATA);

            if(!empty($conditions_yes))
            {
                $sql_condition_yes = array(); // transform from key-value groups to "key=value" string groups
                foreach ($conditions_yes as $key=>$value)
                {
                    $sql_condition_yes[] = "{$key}='{$value}'";
                }
                $sql_condition_yes = implode(' and ', $sql_condition_yes); // implode string array with 'and'
            }
            else {
                $sql_condition_yes = '1';
            }

            if(!empty($conditions_no))
            {
                $sql_condition_no = array(); // transform from key-value groups to "key=value" string groups
                foreach ($conditions_no as $key=>$value)
                {
                    $sql_condition_no[] = "{$key}!='{$value}'";
                }
                $sql_condition_no = implode(' and ', $sql_condition_no); // implode string array with 'and'
            }
            else {
                $sql_condition_no = '1';
            }

            if(!empty($conditions_between))
            {
                $sql_condition_between = array(); // transform to "key between value1 and value2" string groups
                foreach ($conditions_between as $key=>$value)
                {
                    $sql_condition_between[] = "`{$key}` between '{$value[0]}' and '{$value[1]}'";
                }
                $sql_condition_between = implode(' and ', $sql_condition_between);
            }
            else {
                $sql_condition_between = '1';
            }

            if(!empty($conditions_in))
            {
                $sql_condition_in = array(); // transform to "key in (value...)" string groups
                foreach ($conditions_in as $key=>$value)
                {
                    $sql_condition_in[] = "`{$key}` in ('" . implode("','", $value) . "'')";
                }
                $sql_condition_in = implode(' and ', $sql_condition_in); // implode string array with 'and'
            }
            else {
                $sql_condition_in = '1';
            }

            if($other_conditions==='')
            {
                $sql_other_conditions = '1';
            }
            else {
                $sql_other_conditions = $other_conditions;
            }

            $sql = "UPDATE {$table_name} SET {$sql_updateDATA} WHERE {$sql_condition_yes} and {$sql_condition_no} and {$sql_condition_between} and {$sql_condition_in} and {$sql_other_conditions};";
            $returns = $this->CONNECTION_SESSION->query($sql);
            $this->CONNECTION_SESSION->commit();
            return $returns;
        }

        // public function: provide custom query commit
        /**
         * @param $sql: custom sql query
         * @return bool|mysqli_result: return mysql->query($sql) result
         */
        public function query($sql) {
            $returns =  $this->CONNECTION_SESSION->query( $sql );
            try{ // if sql query is insert or update, it needs commit operation
                $this->CONNECTION_SESSION->commit();
                return $returns;
            }
           catch (Exception $e) { // if sql query is search, it do not need commit operation
                return $returns;
           }
        }
    }
}