<?php
session_start();
require("../../frame/Person_Class_frame.php");

if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
    $connection = new STOS_MySQL(); // 建立数据库连接
    $person = new person_all_info( $_SESSION[ "username" ] ); // 获取个人信息
    $self_study_classroom = new self_study_data_set($person->xuehao, time()); // 获取查早教室信息
    if($person->work_info()["权限"]!=1) header( 'refresh:0; url=../../log/logout.php' ); // 如果不是组员，强制登出
}
else { // 没有登陆
    header( 'refresh:0; url=../../log/login.php' ); // 返回登陆页面
}
?>

<!doctype html>
<html lang="zh, en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Personal Index Page">
    <meta name="author" content="罗寅松, 张锐">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/STOS.png">
    <title>早餐组数据录入</title>
    <!-- Bootstrap Core CSS -->
    <link href="../../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- chartist CSS -->
    <link href="../../assets/plugins/chartist-js/dist/chartist.min.css" rel="stylesheet">
    <link href="../../assets/plugins/chartist-js/dist/chartist-init.css" rel="stylesheet">
    <link href="../../assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.css" rel="stylesheet">
    <!--This page css - Morris CSS -->
    <link href="../../assets/plugins/c3-master/c3.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../css/style.css" rel="stylesheet">
    <!-- You can change the theme colors from here -->
    <link href="../../css/colors/blue.css" id="theme" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="fix-header fix-sidebar card-no-border" onload="auto_fill()">
<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ============================================================== -->
<div class="preloader">
    <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
</div>
<!-- ============================================================== -->
<!-- Main wrapper - style you can find in pages.scss -->
<!-- ============================================================== -->
<div id="main-wrapper">
    <!-- ============================================================== -->
    <!-- Topbar header - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <?php require('../../frame/personal_head_frame.php'); ?>
    <!-- ============================================================== -->
    <!-- End Topbar header -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <?php require('../../frame/personal_side_frame.php'); ?>
    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Page wrapper  -->
    <!-- =====here======================================================== -->
    <div class="page-wrapper">
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 col-8 align-self-center">
                    <h3 class="text-themecolor m-b-0 m-t-0">早餐组数据录入</h3>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)"><?php echo $person->xinming; ?></a></li>
                        <li class="breadcrumb-item active">早餐组数据录入</li>
                    </ol>
                </div>
                <!-- <div class="col-md-7 col-4 align-self-center">
                    <a href="#" class="btn waves-effect waves-light btn-danger pull-right hidden-sm-down"> Upgrade to Pro</a>
                </div> -->
            </div>
            <div class ="row">
                <div class="col-lg-12">
                    <div class = "card">
                        <div class="card-block">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="date">日期</label>
                                        <input type="date" name="user_date" id="date" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="gangwei">岗位</label>
                                        <select name="customers" id="gangwei" class="form-control" >
                                            <option value="">请选择岗位</option>
                                            <option value="信通" id="xt">A区主入口</option>
                                            <option value="电子" id="dz">A-C入口</option>
                                            <option value="空天" id="kt">B区主入口</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <ul class="nav nav-tabs" id="zaozixibiaodan" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="zaozixishujubiao-tab" data-toggle="tab" href="#zaozixishujubiao" role="tab" aria-controls="zaozixishujubiao" aria-selected="true">人数数据</a>
                                </li>
                            </ul>
                            <div class="tab-content" id="zaozixibiaodanContent">
                                <div class="tab-pane fade show active" id="zaozixishujubiao" role="tabpanel" aria-labelledby="zaozixishujubiao-tab">
                                    <form action="#" method="post">
                                        <input type="hidden" name="日期" value=""/>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="chidaorenshu">带早餐总人数</label>
                                                <input type="text" class="form-control" id="chidaorenshu" name="带早餐总人数" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="diyicichuqin">带早餐被拦下人数</label>
                                                <input type="text" class="form-control" id="diyicichuqin" name="带早餐被拦下人数" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="beizhu">备注</label>
                                                <textarea class="form-control" id="beizhu" name="备注"></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 mb-3">
                                                <button class="btn btn-success" type="button" onClick="upload_renshu()" name="早自习数据" value="yes">提交修改</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-block">
                            <h2 class="card-title" style="text-align:center">早餐组数据</h2>
                            <h4 id="chazaojiaoshi" class="card-subtitle" style="text-align:center">
                                <?php
                                $result_ = $self_study_classroom->get_classroom_info_array("周一");
                                echo(join("",$self_study_classroom->get_classroomID())." ".$result_["学院"]);
                                ?>
                            </h4>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>日期</th>
                                        <th>岗位</th>
                                        <th>带早餐总人数</th>
                                        <th>带早餐被拦下人数</th>
                                        <th>备注</th>
                                    </tr>
                                    </thead>

                                    <tbody id="self_study_data_table">
                                    <?php
                                    $zhou = array("周一","周二","周三","周四","周五","周六","周日");
                                    foreach($zhou as $value) {
                                        echo("<tr><td>{$value}</td>");

                                        $result = $self_study_classroom->get_data_array($value);
                                        $result_ = $self_study_classroom->get_classroom_info_array($value);
                                        echo("<td>{$result_["应到人数"]}</td>");
                                        echo("<td>{$result['迟到人数']}</td>");
                                        echo("<td>{$result['请假人数']}</td>");
                                        echo("<td>{$result['第一次出勤']}</td>");
                                        echo("<td>{$result['违纪人数']}</td>");
                                        echo("<td>{$result['第二次出勤']}</td>");
                                        echo("<td>{$result['早退人数']}</td>");
                                        echo("<td><textarea>{$result['备注']}</textarea></td>");

                                        echo("</tr>");
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- End Container fluid  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- footer -->
        <!-- ============================================================== -->
        <footer class="footer"> © 2019 学风督导队 <i class="mdi mdi-account-multiple"></i><span>罗寅松 张锐 吴金辰</span> </footer>
        <!-- ============================================================== -->
        <!-- End footer -->
        <!-- ============================================================== -->
    </div>

    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->
</div>
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- Custom script -->
<!-- ============================================================== -->
<script>
    function __creat_HTML_element(eleTagName,attributes,innerContent) {
        var Element = document.createElement(eleTagName);
        if(attributes!==null) {
            for(var key in attributes) {
                Element.setAttribute(key,attributes[key]);
            }
        }
        if(innerContent!==null) {
            var node=document.createTextNode(innerContent);
            Element.appendChild(node);
        }
        return Element;
    }

    function auto_fill(){ // 根据日期展示不同数据
        var riqi, tbody_id;
        switch( document.getElementById("riqi").value ) // 判断选择的日期
        {
            case "周一": riqi=0; tbody_id = "zhouyijimingbiao"; break;
            case "周二": riqi=1; tbody_id = "zhouerjimingbiao"; break;
            case "周三": riqi=2; tbody_id = "zhousanjimingbiao"; break;
            case "周四": riqi=3; tbody_id = "zhousijimingbiao"; break;
            case "周五": riqi=4; tbody_id = "zhouwujimingbiao"; break;
            case "周六": riqi=5; tbody_id = "zhouliujimingbiao"; break;
            case "周日": riqi=6; tbody_id = "zhourijimingbiao"; break;
        }
        var data = document.getElementById("self_study_data_table").children[riqi]; // 选择表格中指定日期的行
        document.getElementById("chidaorenshu").value = data.children[2].innerHTML; // 根据指定行的数据更新表单
        document.getElementById("qinjiarenshu").value = data.children[3].innerHTML; // ...
        document.getElementById("diyicichuqin").value = data.children[4].innerHTML; // ...
        document.getElementById("weijirenshu").value = data.children[5].innerHTML; // ...
        document.getElementById("diercichuqin").value = data.children[6].innerHTML; // ...
        document.getElementById("zaotuirenshu").value = data.children[7].innerHTML; // ...
        document.getElementById("beizhu").value = data.children[8].children[0].innerHTML; // ...

        //更新人数数据表单 和 记名表 的 提交日期
        document.getElementById("jimingbiao").children[0].value = document.getElementById("chidaorenshu").parentNode.parentNode.previousElementSibling.value = document.getElementById("riqi").value;
        //更新记名表表单数据（更新前先删除原有行数）
        removerow(true);
        for(var i=0;i<document.getElementById(tbody_id).childElementCount;i++) {
            addrow({"xinming":document.getElementById(tbody_id).children[i].children[1].innerHTML,"xuehao":document.getElementById(tbody_id).children[i].children[2].innerHTML});
        }
    }

    function addrow(data=null){
        //获取已显示的数量
        var num = document.getElementById("jimingbiao").childElementCount;
        num-=2;
        console.log("已有数量：",num-1);
        console.log("添加后数量：",num);

        //**********************************************************************************
        var div_row = __creat_HTML_element("div",{"class":"row"},null);
        var div_col = __creat_HTML_element("div",{"class":"col-sm-2 mb-3"},null);
        div_col.appendChild(__creat_HTML_element("h5",null,num+"#"));
        div_col.appendChild(__creat_HTML_element("button",{"type":"button","class":"btn btn-rounded btn-danger","onClick":"removerow(false,this)"},"删除本行"));
        div_row.appendChild(div_col);

        div_col = __creat_HTML_element("div",{"class":"col-sm-5 mb-3"},null);
        div_col.appendChild(__creat_HTML_element("label",{"for":"xinming"+num},"姓名"));
        div_col.appendChild(__creat_HTML_element("input",{"type":"text","class":"form-control","id":"xinming"+num,"name":"姓名"+num,"required":"true"},null));
        if(data!=null)div_col.lastElementChild.setAttribute("value",data["xinming"]);
        div_row.appendChild(div_col);

        div_col = __creat_HTML_element("div",{"class":"col-sm-5 mb-3"},null);
        div_col.appendChild(__creat_HTML_element("label",{"for":"xuehao"+num},"学号"));
        div_col.appendChild(__creat_HTML_element("input",{"type":"text","class":"form-control","id":"xuehao"+num,"name":"学号"+num,"required":"true"},null));
        if(data!=null)div_col.lastElementChild.setAttribute("value",data["xuehao"]);
        div_row.appendChild(div_col);

        document.getElementById("jimingbiao").insertBefore(div_row,document.getElementById("add_remove_button"))
    }

    function removerow(is_all=false, element=null){
        //获取已显示的数量
        var num = document.getElementById("jimingbiao").childElementCount;
        num-=3;
        console.log("已有数量：",num);
        console.log("删除后数量：",num-1);
        if(is_all===true) {
            while(num>0) {
                document.getElementById("jimingbiao").removeChild(document.getElementById("add_remove_button").previousElementSibling);
                num--;
            }
        }
        else {
            if(element==null) {
                if(num>0) {
                    document.getElementById("jimingbiao").removeChild(document.getElementById("add_remove_button").previousElementSibling);
                }
            }
            else if(element.tagName===document.createElement("button").tagName) {
                if(num>0) {
                    var next_ele = element.parentNode.parentNode.nextElementSibling;

                    var No = next_ele.children[0].children[0].innerHTML.replace("#","");
                    while(No<=num) {
                        next_ele.children[0].children[0].innerHTML = No-1+"#";

                        next_ele.children[1].children[0].setAttribute("for","xinming"+(No-1))

                        next_ele.children[1].children[1].setAttribute("id","xinming"+(No-1))
                        next_ele.children[1].children[1].setAttribute("name","姓名"+(No-1))

                        next_ele.children[2].children[0].setAttribute("for","xuehao"+(No-1))

                        next_ele.children[2].children[1].setAttribute("id","xuehao"+(No-1))
                        next_ele.children[2].children[1].setAttribute("name","学号"+(No-1))

                        next_ele = next_ele.nextElementSibling;
                        No = next_ele.children[0].children[0].innerHTML.replace("#","");
                    }
                    element.parentNode.parentNode.parentNode.removeChild(element.parentNode.parentNode);
                }
            }
            else console.log("Wrong Element!");
        }

    }
</script>
<!-- ============================================================== -->
<!-- End Custom script -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- Custom Ajax script -->
<!-- ============================================================== -->
<script>
    function upload_renshu() {
        var xmlhttp;
        var jsonstr;
        var str;
        if (window.XMLHttpRequest) {
            // IE7+, Firefox, Chrome, Opera, Safari 浏览器执行代码
            xmlhttp=new XMLHttpRequest();
        }
        else {
            // IE
            try {
                xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (e) {
                try {
                    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                }
                catch (e) {
                    alert("您的浏览器不支持AJAX！");
                    return false;
                }
            }
        }
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                jsonstr = xmlhttp.responseText;
                str = JSON.parse(jsonstr);
                if(str["status"]=="true") {
                    alert("成功："+str["content"]);
                    window.location.reload();
                }
                else {
                    alert("错误！！："+str["content"]);
                }
            }
        }

        var ajax_chidaorenshu = document.getElementById("chidaorenshu").value; // 迟到人数
        var ajax_diyicichuqin = document.getElementById("diyicichuqin").value; // 第一次出勤
        var ajax_weijirenshu = document.getElementById("weijirenshu").value; // 违纪人数
        var ajax_diercichuqin = document.getElementById("diercichuqin").value; // 第二次出勤
        var ajax_zaotuirenshu = document.getElementById("zaotuirenshu").value; // 早退人数
        var ajax_qinjiarenshu = document.getElementById("qinjiarenshu").value; // 请假人数
        var ajax_beizhu = document.getElementById("beizhu").value; // 备注
        var ajax_riqi = document.getElementById("chidaorenshu").parentNode.parentNode.previousElementSibling.value; // 日期

        xmlhttp.open("POST","/ajax/personal/group_member/selfstudy.php",true);
        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        xmlhttp.send("日期="+ajax_riqi+"&迟到人数="+ajax_chidaorenshu+"&第一次出勤="+ajax_diyicichuqin+"&违纪人数="+ajax_weijirenshu+"&第二次出勤="+ajax_diercichuqin+"&早退人数="+ajax_zaotuirenshu+"&请假人数="+ajax_qinjiarenshu+"&备注="+ajax_beizhu+"&早自习数据=yes");
    }

    function upload_queqinbiao() {
        var xmlhttp;
        var jsonstr;
        var str;
        if (window.XMLHttpRequest) {
            // IE7+, Firefox, Chrome, Opera, Safari 浏览器执行代码
            xmlhttp=new XMLHttpRequest();
        }
        else {
            // IE
            try {
                xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (e) {
                try {
                    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                }
                catch (e) {
                    alert("您的浏览器不支持AJAX！");
                    return false;
                }
            }
        }
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                jsonstr = xmlhttp.responseText;
                str = JSON.parse(jsonstr);
                if(str["status"]=="true") {
                    alert("成功："+str["content"]);
                    window.location.reload();
                }
                else {
                    alert("错误！！："+str["content"]);
                }
            }
        }

        var num = 1;
        var ajax_riqi = document.getElementById("jimingbiao").children[0].value; // 日期
        var post_head = "日期="+ajax_riqi+"&记名表=yes";
        while(true) {
            var ajax_xingming = document.getElementById("xinming"+String(num))
            if(ajax_xingming!==null)
                ajax_xingming = ajax_xingming.value; // 姓名
            else
                break;

            var ajax_xuehao = document.getElementById("xuehao"+String(num))
            if(ajax_xuehao!==null)
                ajax_xuehao = ajax_xuehao.value; // 学号
            else
                break;

            post_head += "&姓名"+String(num)+"="+ajax_xingming;
            post_head += "&学号"+String(num)+"="+ajax_xuehao;
            num++;
        }


        xmlhttp.open("POST","/ajax/personal/group_member/selfstudy.php",true);
        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        xmlhttp.send(post_head);
    }
</script>
<script type="text/javascript">
    var mydateInput = document.getElementById("date");
    var date = new Date();
    var month;
    var day;
    if ((date.getMonth() + 1)<10){
        month = '0'+(date.getMonth() + 1).toString();
    }
    else{
        month = (date.getMonth() + 1).toString()
    }
    if(date.getDate()<10){
        day = '0'+date.getDate().toString();
    }
    else{
        day = date.getDate().toString();
    }
    var dateString = date.getFullYear() + "-" + month + "-" + day;
    mydateInput.value = dateString;
</script>
<!-- ============================================================== -->
<!-- End Custom Ajax script -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- All Jquery -->
<!-- ============================================================== -->
<script src="../../assets/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap tether Core JavaScript -->
<script src="../../assets/plugins/bootstrap/js/tether.min.js"></script>
<script src="../../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<!-- slimscrollbar scrollbar JavaScript -->
<script src="../../js/jquery.slimscroll.js"></script>
<!--Wave Effects -->
<script src="../../js/waves.js"></script>
<!--Menu sidebar -->
<script src="../../js/sidebarmenu.js"></script>
<!--stickey kit -->
<script src="../../assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
<!--Custom JavaScript -->
<script src="../../js/custom.min.js"></script>
<!-- ============================================================== -->
<!-- This page plugins -->
<!-- ============================================================== -->
<!-- chartist chart -->
<script src="../../assets/plugins/chartist-js/dist/chartist.min.js"></script>
<script src="../../assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.min.js"></script>
<!--c3 JavaScript -->
<script src="../../assets/plugins/d3/d3.min.js"></script>
<script src="../../assets/plugins/c3-master/c3.min.js"></script>
<!-- Chart JS -->
<script src="../../js/dashboard1.js"></script>
</body>

</html>
