<?php
session_start();
require_once __DIR__ . '/../../ROOT_PATH.php';
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
require_once ROOT_PATH . "/Frame/php/Connector/DatabaseConnector.php";
require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
require_once ROOT_PATH . "/Frame/php/Tools/TranslateBetweenChineseAndEnglish.php";
// TODO:require log file

if (isset($_POST['requestFunction'])) { // 是否有要请求的类别
    if ($_POST['requestFunction']==='getPersonalInfos') {
        require_once ROOT_PATH . '/Frame/php/Users/getPersonInfo.php';
        try {
            $returns = new UnionReturnInterface();
            // 获取信息，并进行key的中英文转换
            $personalInfo_ = getOnePersonAllInfo();
            $personalInfo_['备注']=$personalInfo_["成员信息备注"];
            $personalInfo = array();
            foreach ($personalInfo_ as $key=>$value) {
                $translation = TranslateBetweenChineseAndEnglish::C2E($key);
                if($translation!==false) {
                    $personalInfo[$translation] = $value;
                }
            }
            // 处理信息并打包数据
            $returns->setData($personalInfo);
            echo $returns;
        } catch (JsonException $e) {
            $returns = new UnionReturnInterface('417','数据封装过程中出现错误');
            echo $returns;
        } catch (STSAException $e) {
            $returns = new UnionReturnInterface();
            $returns->boundSTSAException($e);
            echo $returns;
        }
    } elseif ($_POST['requestFunction']==='uploadPersonalInfos') {
        require_once ROOT_PATH . '/Frame/php/Users/setPersonInfo.php';
        if( // 判断提供了足够的数据
        isset($_POST["name"], $_POST["studentID"], $_POST["gender"],
            $_POST["school"], $_POST["nation"], $_POST["hometown"],
            $_POST["phone"], $_POST["QQ"], $_POST["campus"], $_POST["dormitory-yuan"],
            $_POST["dormitory-dong"], $_POST["dormitory-hao"],
            $_POST["applicant-name"], $_POST["applicant-studentID"],
            $_POST["applicant-credit-card"], $_POST["subsidyDossier"], $_POST["remark"])
        ) { // 数据封装成数组
            $personalInfo = array();
            foreach ($_POST as $key=>$value) {
                $translation = TranslateBetweenChineseAndEnglish::E2C($key);
                if($translation!==false) {
                    $personalInfo[$translation] = $value;
                }
            }
            // 如果提供了密码，则数据封装包括密码部分
            if(isset($_POST["password"])) {
                $translation = TranslateBetweenChineseAndEnglish::E2C("password");
                $personalInfo[$translation] = $_POST["password"];
            }
            // 提交数据，获取结果
            try {
                $returns = new UnionReturnInterface();
                $returns->setData(setOnePersonAllInfo($personalInfo));
                echo $returns;
            } catch (JsonException $e) {
                $returns = new UnionReturnInterface('417','数据封装过程中出现错误');
                echo $returns;
            } catch (STSAException $e) {
                $returns = new UnionReturnInterface();
                $returns->boundSTSAException($e);
                echo $returns;
            }
        } else {
            try {
                $Message = json_encode(["信息" => "数据提供不完整，请联系管理员处理"], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                $returns = new UnionReturnInterface('400', "传入的参数不足", $Message);
                echo $returns;
            } catch (JsonException $e) {
                $returns = new UnionReturnInterface('417','数据封装过程中出现错误');
                echo $returns;
            }
        }
    } else {
        $returns = new UnionReturnInterface('404', "功能不存在");
        echo $returns;
    }
}
else {
    $returns = new UnionReturnInterface('404', "没有选择需要的功能");
    echo $returns;
}