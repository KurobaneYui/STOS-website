<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>无标题文档</title>


</head>

<body>
    <?php
    require('/var/www/html/ROOT_PATH.php');
    require (ROOT_PATH.'/frame/php_frame/Database_connector.php');

    $test1 = new Database_connector(ROOT_PATH.'/config/DataBase_Information.conf');
    $d = $test1->search('成员信息',array(),array('学号'=>'2016020903001'),array('姓名'=>'路'),array(),array(),array('学号'=>'asc','姓名'=>'desc'));
    var_dump($d->fetch_assoc());
echo '<br/>';
    require (ROOT_PATH.'/frame/php_frame/DateTools.php');
    $r = new DateTools();
    var_dump($r->next_week());
echo '<br/>';
    require (ROOT_PATH.'/frame/php_frame/Person_connector.php');
    $t = new Person_connector('2016020903001');
    var_dump($t->work_info());
    ?>
    <img src="/assets/images/STOS.png" alt="homepage" class="light-logo" />
</body>
</html>