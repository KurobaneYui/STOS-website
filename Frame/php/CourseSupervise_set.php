<?php
require __DIR__ . '/../../ROOT_PATH.php';
require ROOT_PATH . '/Frame/php/Database_connector.php';
require ROOT_PATH . '/Frame/php/CourseSupervise_single.php';

if (!class_exists('CourseSupervise_set')) {
    class CourseSupervise_set
    {
        private $r;
    }
}