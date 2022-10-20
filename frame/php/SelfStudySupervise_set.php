<?php
require __DIR__ . '/../../ROOT_PATH.php';
require ROOT_PATH . '/frame/php/Database_connector.php';
require ROOT_PATH . '/frame/php/SelfStudySupervise_single.php';

if (!class_exists('SelfStudySupervise_set')) {
    class SelfStudySupervise_set
    {
        private $j;
    }
}