<?php
require __DIR__.'/../../ROOT_PATH.php';
require ROOT_PATH.'/frame/php/Database_connector.php';
require ROOT_PATH.'/frame/php/DateTools.php';

if (!class_exists('Person')) {
    class Person_assessment
    {
        // 数据库连接
        private $STSA_DATABASE; // database connection



        /**
         * Person_assessment constructor.
         * @param string $studentID
         */
        public function __construct(string $studentID) {
            $this->STSA_DATABASE = new Database_connector(ROOT_PATH.'/config/DataBase_STSA.conf');

            ;
        }

        public function __destruct() {
            unset($this->STSA_DATABASE);
        }
    }
}