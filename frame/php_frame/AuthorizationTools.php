<?php
session_start();
require (ROOT_PATH.'/frame/php_frame/Database_connector.php');
require (ROOT_PATH.'/frame/php_frame/DateTools.php');

if (!class_exists('AuthorizationTools')) {
    class AuthorizationTools
    {
        public function Permission(): void
        {
            ;
        }
    }
}