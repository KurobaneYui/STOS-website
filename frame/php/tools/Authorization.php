<?php
session_start();

if(!function_exists("get_authorization_code")){
    function get_authorization_code(string $manual): bool{
        return true;
    }//TODO: not even start
}