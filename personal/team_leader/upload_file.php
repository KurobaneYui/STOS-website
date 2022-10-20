<?php
// 允许上传的图片后缀
echo exec('whoami');
$allowedExts = array("rar", "zip", "xls", "xlsx", "pdf", "doc", "docx", "ppt", "pptx");
$temp = explode(".", $_FILES["STSA_file"]["name"]);
echo $_FILES["STSA_file"]["size"];
var_dump($_FILES["STSA_file"]["error"]);echo "<br/>";
$extension = end($temp);     // 获取文件后缀名
if (($_FILES["STSA_file"]["size"] < 41943040)   // 小于 40 MB
&& in_array($extension, $allowedExts))
{
    if ($_FILES["STSA_file"]["error"] > 0)
    {
        echo "错误：: " . $_FILES["STSA_file"]["error"] . "<br>";
    }
    else
    {
        echo "上传文件名: " . $_FILES["STSA_file"]["name"] . "<br>";
        echo "文件类型: " . $_FILES["STSA_file"]["type"] . "<br>";
        echo "文件大小: " . ($_FILES["STSA_file"]["size"] / 1048576) . " MB<br>";
        echo "文件临时存储的位置: " . $_FILES["STSA_file"]["tmp_name"] . "<br>";
        
        // 判断当前目录下的 upload 目录是否存在该文件
        // 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
        $saveDirector = "/var/www/html/document/category/{$_POST['STSA_year']}-{$_POST['STSA_yearPart']}/第{$_POST['STSA_weekNum']}周/";
        mkdir($saveDirector);
        if (file_exists($saveDirector . $_FILES["STSA_file"]["name"]))
        {
            echo $_FILES["STSA_file"]["name"] . " 文件已替换 ";
        }
        // 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
        var_dump(is_uploaded_file($_FILES["STSA_file"]["tmp_name"]));echo "<br>";
        if (move_uploaded_file($_FILES["STSA_file"]["tmp_name"], $saveDirector . basename($_FILES["STSA_file"]["name"]))) {
            echo "文件存储在: " . $saveDirector . $_FILES["STSA_file"]["name"];
        }
        else {
            echo "Possible file upload attack! <br>";
        }
        echo "Here is some more debugging info:";
        print_r($_FILES);
    }
}
else
{
    echo "非法的文件格式";
}
?>