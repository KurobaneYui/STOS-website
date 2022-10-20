<?php
header("Content-type: text/html; charset=utf-8");

function tree($directory) {
    $mydir=dir($directory);
    echo "<ul>";
    while($file=$mydir->read()){
        if((is_dir("$directory/$file")) && ($file!='.') && ($file!='..')){
            if($file!='.'&&$file!=".."){
                echo "<li><font color='#ff00cc'><b>$file</b></font></li>";
            }
            tree("$directory/$file");
        }else {
            if($file!="."&&$file!="..") {
                $directory = substr($directory,14);
                echo "<li><a href='$directory/$file'>$file<a></li>";
            }
        }
    }
    echo "</ul>";
    $mydir->close();
}

echo "<h2>各学期文件</h2>";
echo "<p>年份后的数字表示上半年或下半年，如：2020-2表示2020下半年</p>";
echo "<p>点击文件名（非紫色）直接下载对应文件，chrome默认打开pdf可在打开后的右上角找到下载按钮下载</p><br/>";
tree("/var/www/html/document/category");