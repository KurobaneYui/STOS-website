<?php
session_start();
require __DIR__. '/../../ROOT_PATH.php';
require ROOT_PATH.'/frame/php/Person.php';
require ROOT_PATH.'/frame/php/TransJson.php';

/**
 * @param string $StudentID
 * @param array $registerData
 * @return bool|string: true when successfully register; string when error thrown
 */
function register(string $StudentID, array $registerData) {
    $person = new Person($StudentID);
    if ($person->exist()) {
        return json_encode(array('title'=>'此学号对应的用户已存在！','text'=>''),JSON_UNESCAPED_UNICODE);
    }
    if ($person->change_info($registerData)) {
        if ($person->commit_all_personal_information()) {
            return true;
        }
        $person->delete_all_info();
        $error_string = '';
        foreach ($person->error_array(array('basic','password')) as $key=>$value) {
            $error_string .= $key.'->'.$value.'；';
        }
        return json_encode(array('title'=>'这些信息填写有误，请检查：','text'=>$error_string),JSON_UNESCAPED_UNICODE);
    }
    $error_string = implode(',', array_values($person->invalid_infos['changeInfo']));
    return json_encode(array('title'=>'这些信息无法修改：','text'=>$error_string),JSON_UNESCAPED_UNICODE);
}

if (isset($_POST['requestFunction'])) { // 是否有要请求的类别
   if ($_POST['requestFunction']==='register') { // 如果请求注册
       if (isset($_POST['StudentID'], $_POST['Name'], $_POST['Gender'], $_POST['Ethnicity'], // 判断是否符合需要的参数
           $_POST['Dormitory_yuan'], $_POST['Dormitory_dong'], $_POST['Dormitory_hao'],
           $_POST['School'], $_POST['Campus'], $_POST['Hometown'], $_POST['Password'],
           $_POST['SubsidyDossier'], $_POST['BankID'], $_POST['PhoneNumber'], $_POST['QQ']))
       {
           $register_info = array(
               '姓名'=>$_POST['Name'], '性别'=>$_POST['Gender'], '民族'=>$_POST['Ethnicity'],
               '籍贯'=>$_POST['Hometown'], '电话'=>$_POST['PhoneNumber'], 'QQ'=>$_POST['QQ'],
               '校区'=>$_POST['Campus'], '学院'=>$_POST['School'], '工资用银行卡号'=>$_POST['BankID'],
               '寝室_苑'=>$_POST['Dormitory_yuan'], '寝室_栋'=>$_POST['Dormitory_dong'],
               '寝室_号'=>$_POST['Dormitory_hao'], '建档立卡'=>$_POST['SubsidyDossier'], '密码'=>$_POST['Password']
           );
           $default_ifo = array(
               '所属组'=>null, '岗位'=>null, '考评'=>'合格', '计分'=>5, '周一空课'=>'0000',
               '周二空课'=>'0000', '周三空课'=>'0000', '周四空课'=>'0000', '周五空课'=>'0000',
               '基础工资'=>0, '工资用姓名'=>$_POST['Name'], '工资用学号'=>$_POST['StudentID'],
               '备注'=>array(), '权限'=>'210000.0000000000.200'
           );
           $register_return = register($_POST['StudentID'],array_merge($register_info,$default_ifo));
           if ($register_return===true) {
               echo (new TransJson(true,'','',''))->encode2json();
           } else {
               echo (new TransJson(false,'00',$register_return,''))->encode2json();
           }
       }
       else {
           $returns = new TransJson(false,'12','请确认完整提供了注册所需的所有信息');
           echo $returns->encode2json();
       }
   }
   else {
       $returns = new TransJson(false,'30',"你试图使用 {$_POST['requestFunction']} 功能，但是此功能并不存在");
       echo $returns->encode2json();
   }
}

//$returns = new TransJson(false,'30',"你试图使用 {$_POST['requestFunction']} 功能，但是此功能正在开发维护，暂不提供使用");
//echo $returns->encode2json();
