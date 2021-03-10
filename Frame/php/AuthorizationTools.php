<?php
session_start();
require __DIR__ . '../../ROOT_PATH.php';
require ROOT_PATH . '/Frame/php/Database_connector.php';
require ROOT_PATH . '/Frame/php/DateTools.php';

if (!class_exists('AuthorizationTools')) {
    class AuthorizationTools
    {
        public function Permission(): void
        {
            ;
        }
    }
}