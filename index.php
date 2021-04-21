<?php
session_start();
$_SESSION["userID"]='2016020903001';
$_SESSION['userName']='罗寅松';
$_SESSION['isLogin'] = hash('sha256',session_id().$_SESSION['userID'].'true');