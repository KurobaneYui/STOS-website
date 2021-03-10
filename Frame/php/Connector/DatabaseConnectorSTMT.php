<?php


if (!isset($__DatabaseConnectorSTMT__)) {
    $__DatabaseConnectorSTMT__ = true;

    class DatabaseConnectorSTMT
    {
        private bool $status;
        private mysqli $session;
        private string $server;
        private string $username;
        private string $password;
        private string $databaseName;

        public function __construct(string $config_file){
            $this->connect($config_file);
        }

        public function __destruct() {
            $this->disconnect();
        }

        public function connect(string $config_file, bool $reconnect = false) {
            if ($reconnect) {
                if ($this->status) {
                    $this->disconnect();
                }
            }
            elseif (!$reconnect) {
                // Get config from file
                $conf = json_decode(file_get_contents($config_file), true, 1024, JSON_THROW_ON_ERROR);
                // Setting
                $this->server = $conf['host']??'';
                $this->username = $conf['user']??'';
                $this->password = $conf['password']??'';
                $this->databaseName = $conf['database']??'';
            }

            $this->session = new mysqli( $this->server, $this->username, $this->password, $this->databaseName);
            // Check whether connection established
            if ($this->session->connect_errno) {
                $this->status = false;
            }
            else{
                $this->status = true;
            }
        }

        public function disconnect() {
            if ($this->status) {
                $this->session->close();
                $this->status = false;
            }
        }

        public function getStatus() {
            return $this->status;
        }

        public function getErrorList() {
            if ($this->status) {
                return $this->session->error_list;
            }
            return array();
        }

        public function query(string $sql){
            if ($this->status) {
                $returns = $this->session->query($sql);
                try{ // if sql query is insert or update, it needs commit operation
                    $this->session->commit();
                    return $returns;
                }
                catch (Exception $e) { // if sql query is search, it do not need commit operation
                    return $returns;
                }
            }
        }

        /************************* Tools function ***************************/
        public static function pariseConditionsBasic(array $conditionsArray, bool $attitude=true) {
            if (empty($conditionsArray)) {
                return $attitude ? 'true' : 'false';
            }

            $conditions = array();
            foreach ($conditionsArray as $key=>$value) // transform from key-value groups to "key=value" string groups
            {
                if ($value===null) {
                    $conditions[] = $attitude ? "{$key} is null" : "{$key} is not null";
                } elseif ($key==='密码') {
                    $conditions[] = $attitude ? "{$key}=AES_ENCRYPT('{$value}','{$value}')" : "{$key}!=AES_ENCRYPT('{$value}','{$value}')";
                } else {
                    $conditions[] = $attitude ? "{$key}='{$value}'" : "{$key}!='{$value}'";
                }
            }
            return implode(' and ', $conditions); // implode string array with 'and'
        }

        public static function pariseConditionsBetween(array $conditionsArray, bool $attitude=true) {
            if (empty($conditionsArray)) {
                return $attitude ? 'true' : 'false';
            }

            $conditions=array();
            foreach ($conditionsArray as $key=>$value) // transform to "key between value1 and value2" string groups
            {
                $conditions[] = $attitude ? "`{$key}` between '{$value[0]}' and '{$value[1]}'" : "`{$key}` not between '{$value[0]}' and '{$value[1]}'";
            }
            return implode(' and ', $conditions);
        }

        public static function pariseConditionsIn(array $conditionsArray, bool $attitude=true) {
            if (empty($conditionsArray)) {
                return $attitude ? 'true' : 'false';
            }

            $conditions=array();
            foreach ($conditionsArray as $key=>$value)  // transform to "key in (value...)" string groups
            {
                $conditions[] = $attitude ? "`{$key}` in ('" . implode("','", $value) . "'')" : "`{$key}` not in ('" . implode("','", $value) . "'')";
            }
            return implode(' and ', $conditions);
        }

        public static function pariseCondition(array $conditionsArray) {
            if (empty($conditionsArray)) {
                return 'true';
            }
        }

        public static function pariseOrder(array $orderArray) {
            if (empty($orderArray)) {
                return 'true';
            }

            $order = array();
            foreach ($order as $key=>$value) // transform to "order by ... asc ... desc" string groups
            {
                $order[] = "`{$key}` {$value}";
            }
            return implode(',', $order); // implode string array with ','
        }
    }
}