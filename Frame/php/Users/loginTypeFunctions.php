<?php
session_start();
require_once __DIR__ . '/../../../ROOT_PATH.php';
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/Tools/DeviceAndIPDetector.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

if(!function_exists("getCookie")) {
    function getCookie(): array{
        $logger = new STSA_log();
        if (isset($_COOKIE['STSA_un'], $_COOKIE['STSA_pd'])) {
            $logger->add_log(__FILE__.":".__LINE__, "getCookie, cookie exist and return", "Log");
            return ['STSA_un' => $_COOKIE['STSA_un'], 'STSA_pd' => $_COOKIE['STSA_pd'], 'STSA_rm' => true];
        }
        $logger->add_log(__FILE__.":".__LINE__, "getCookie, cookie is not exist", "Log");
        return ['STSA_un' => '', 'STSA_pd' => '', 'STSA_rm' => false];
    }
}

if(!function_exists("login")) {
    function login(array $log_info): array{
        $logger = new STSA_log();
        $session = new DatabaseConnector();
        $sql = "select `学号` from `账户密码` where `学号`='{$log_info['学号']}'  and `密码`=AES_ENCRYPT( '{$log_info['密码']}','{$log_info['密码']}' );";
        $passwordCheck = $session->query($sql);
        if ($passwordCheck===false) {
            $logger->add_log(__FILE__.":".__LINE__, "login, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误",417);
        }

        // 获取设备信息
        $LogInfo = new DeviceAndIPDetector();

        $rows = $passwordCheck->num_rows;
        if ($rows===1) {
            $sql = "select `姓名` from `成员基本信息` where `学号`='{$log_info['学号']}'";
            $name = $session->query($sql)->fetch_assoc()["姓名"];
            $_SESSION['userID'] = $log_info['学号'];
            $_SESSION['isLogin'] = hash('sha256',session_id().$log_info['学号'].'true');
            $_SESSION['userName'] = $name;
            if ($log_info["记住我"]==='true') { // 设定14天的cookie
                setcookie('STSA_un', $log_info['学号'], time() + 1209600);
                setcookie('STSA_pd', $log_info['密码'], time() + 1209600);
            } else { // 如果没有设置记住用户名和密码
                setcookie('STSA_un', '', time() - 604800);
                setcookie('STSA_pd', '', time() - 604800);
            }
            // 记录成功的登录信息
            $sql = "select id from 登录信息 order by id DESC;";
            $id = $session->query($sql)->fetch_assoc()["id"];
            if($id === null) {$id = 0;}
                else {++$id;}
            $sql = "insert into `登录信息` (`学号`, `操作系统`, `浏览器`, `语言`, `访问IP`, `访问地址`, `访问时间`, `登录结果`, `id`)
                    value ('{$log_info['学号']}', '$LogInfo->OS', '$LogInfo->browser', '$LogInfo->language', '$LogInfo->IP', '$LogInfo->address', '$LogInfo->datetime', '成功', $id);";
            $session->query($sql);
            $session->commit();
            $logger->add_log(__FILE__.":".__LINE__, "login, login successfully", "Log");

            return [true];
        }
        // 记录失败的登录信息
        $sql = "select id from 登录信息 order by id DESC;";
        $id = $session->query($sql)->fetch_assoc()["id"];
        if($id === null) {$id = 0;}
            else {++$id;}
        $sql = "insert into `登录信息` (`学号`, `操作系统`, `浏览器`, `语言`, `访问IP`, `访问地址`, `访问时间`, `登录结果`, `id`)
                value ('{$log_info['学号']}', '$LogInfo->OS', '$LogInfo->browser', '$LogInfo->language', '$LogInfo->IP', '$LogInfo->address', '$LogInfo->datetime', '失败', $id);";
        $session->query($sql);
        $session->commit();
        $logger->add_log(__FILE__.":".__LINE__, "login, login unsuccessfully", "Log");

        return [false];
    }
}

if(!function_exists("resetPassword")) {
    function resetPassword(array $reset_info): array{
        $logger = new STSA_log();
        $session = new DatabaseConnector();
        $sql = "select `学号` from `成员保密信息` where `学号`='{$reset_info['学号']}' and `籍贯`='{$reset_info['籍贯']}' and
                                `姓名`='{$reset_info['姓名']}' and `学院名称`='{$reset_info['学院']}';";
        $resetPasswordCheck = $session->query($sql);
        if ($resetPasswordCheck===false) {
            $logger->add_log(__FILE__.":".__LINE__, "resetPassword, 数据库查询错误", "Error");
            throw new STSAException("数据库查询错误",417);
        }

        $rows = $resetPasswordCheck->num_rows;
        if ($rows===1) {
            $sql = "update 账户密码 set `密码备份`='{$reset_info['学号']}', `密码`=AES_ENCRYPT( '{$reset_info['学号']}','{$reset_info['学号']}' ) where `学号`='{$reset_info['学号']}';";
            $resetPasswordSql = $session->query($sql);
            if ($resetPasswordSql===false) {
                $logger->add_log(__FILE__.":".__LINE__, "resetPassword, 数据库更新错误", "Error");
                throw new STSAException("数据库更新错误",417);
            }
            $session->commit();
            $logger->add_log(__FILE__.":".__LINE__, "resetPassword, resetPassword successfully", "Log");

            return [true];
        }
        $logger->add_log(__FILE__.":".__LINE__, "resetPassword, resetPassword unsuccessfully", "Log");
        return [false];
    }
}