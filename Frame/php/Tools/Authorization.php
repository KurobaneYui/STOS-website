<?php
session_start();

if(!function_exists("check_authorization")){
    function check_authorization(string $manual_code): bool{
        return true;
    }//TODO: not even start
}