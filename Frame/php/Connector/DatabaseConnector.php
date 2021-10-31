<?php


if (!isset($__DatabaseConnector__)) {
    $__DatabaseConnector__ = true;
    require __DIR__ . '/../../../ROOT_PATH.php';

    class DatabaseConnector
    {
        private bool $status;
        private string $errorString;

        private mysqli $session;
        private mysqli_result|bool $query_result;

        private string $server;
        private int $port;
        private string $username;
        private string $password;
        private string $databaseName;

        public function __construct(string $config_file = ROOT_PATH.'/config/DataBase_STSA.conf'){
            $this->status = false;
            $this->errorString = '';

            $this->newConnect(true,$config_file);
        }

        public function __destruct() {
            $this->disconnect();
        }

        public function newConnect(bool $changeConfig = false, string $config_file = '') {
            if ($this->status) {
                $this->disconnect();
            }
            if ($changeConfig) {
                // Get config from file
                $conf = json_decode(file_get_contents($config_file), true, 1024, JSON_THROW_ON_ERROR);
                // Setting
                $this->server = $conf['host']??'';
                $this->port = $conf['port']??0;
                $this->username = $conf['user']??'';
                $this->password = $conf['password']??'';
                $this->databaseName = $conf['database']??'';
            }

            $this->session = new mysqli( $this->server, $this->username, $this->password, $this->databaseName,$this->port);
            // Check whether connection established
            if ($this->session->connect_errno) {
                $this->errorString = $this->session->connect_error;
                $this->status = false;
            }
            else{
                $this->status = true;
                $this->errorString= '';
            }
        }

        public function disconnect() {
            if ($this->status) {
                $this->session->close();
                $this->status = false;
            }
        }

        public function getStatus() {
            return ['status'=>$this->status,'errorString'=>$this->errorString];
        }

        public function getErrorList() {
            if ($this->status) {
                return $this->session->error_list;
            }
            return array();
        }

        public function getSession() {
            return $this->session;
        }

        public function query(string $sql): bool|mysqli_result {
            if ($this->status) {
                try{ // if sql query is insert or update, it needs commit operation
                    if (isset($this->query_result) && !is_bool($this->query_result)){$this->query_result->free();}
                    return $this->query_result = $this->session->query($sql);
                }
                catch (Exception $e) { // if sql query is search, it do not need commit operation
                    return false;
                }
            }

            return false;
        }

        public function commit(){
            $this->session->commit();
        }

        public function rollback(){
            $this->session->rollback();
        }

        public function autocommit($mode = 'true'){
            $this->session->autocommit($mode);
        }
    }
}