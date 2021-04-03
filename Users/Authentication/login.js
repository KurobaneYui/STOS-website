function remembered() { // 自动补全“记住我”保存的用户名和密码
    let student = $("input[placeholder='Username']");
    let password = $("input[placeholder='Password']");
    let rememberMe = $("#checkbox-signup");
    let cookie_info = {requestFunction: "getCookie"};
    $.post("/Ajax/Users/login.php",
        cookie_info,
        function (data, status) {
            if (status === 'success') {
                let tempdata=JSON.parse(data);
                let returnCode=tempdata['ReturnCode'];
                data=tempdata['Data'];
                if(returnCode==='400') {
                    alert("参数错误，请联系管理员");
                }
                else if(returnCode==='401') {
                    alert('权限错误，请联系管理员处理');
                }
                else if(returnCode==='404') {
                    alert('功能不存在，请联系管理员更正文件调用');
                }
                else if(returnCode==='417') {
                    alert('功能错误，请联系管理员处理');
                }
                else if(returnCode==='499') {
                    $('#RememberMeWarning').show().append('Remember Me功能维护中，暂不可用');
                }
                else if (returnCode==='200' || returnCode==='301') {
                    //状态码301，提醒转移函数
                    if(returnCode==='301'){window.console.log('cookie获取函数移至新位置');}
                    //状态码200，处理data
                    data = JSON.parse(data);
                    if (data['STSA_rm'])
                        rememberMe.prop('checked', true);
                    else
                        rememberMe.prop('checked', false);
                    password.val(data['STSA_pd']);
                    student.val(data['STSA_un']);
                }
            } else {
                alert('请检查浏览器网络连接，建议刷新后重试');
            }
        });
}

function login() { // 点击登录按钮的功能
    let student = $("input[placeholder='Username']").val();
    let password = $("input[placeholder='Password']").val();
    let rememberMe = $("#checkbox-signup").prop('checked');
    let login_info = {
        requestFunction:"login",
        StudentID:student,
        Password:password,
        RememberMe:rememberMe
    };
    $.post("/Ajax/Users/login.php",
        login_info,
        function(data,status){
            if (status==='success') {
                let tempdata=JSON.parse(data);
                let returnCode=tempdata['ReturnCode'];
                data=tempdata['Data'];
                if(returnCode==='400') {
                    alert("参数错误，请联系管理员");
                }
                else if(returnCode==='401') {
                    alert('权限错误，请联系管理员处理');
                }
                else if(returnCode==='404') {
                    alert('功能不存在，请联系管理员更正文件调用');
                }
                else if(returnCode==='417') {
                    alert('功能错误，请联系管理员处理');
                }
                else if(returnCode==='499') {
                    swal({
                      title: "功能维护中，暂不允许登录",
                      icon: "warning",
                    });
                }
                else if (returnCode==='200' || returnCode==='301') {
                    //状态码301，提醒转移函数
                    if(returnCode==='301'){window.console.log('登录函数移至新位置');}
                    //状态码200，处理data
                    data = JSON.parse(data);
                    if(data[0]===true)
                        $(window).attr('location','../UserCenter/index.html');
                    else
                        swal({
                          title: "登录错误",
                          text: "用户名或密码错误，请确认输入，留意大小写与输入前后的空格",
                          icon: "error",
                        });
                }
            }
            else {
                alert('请检查浏览器网络连接，建议刷新后重试');
            }
        });
}

function reset_password() { // 点击重置按钮的功能
    let name = $("input[placeholder='姓名']").val();
    let studentID = $("input[placeholder='学号']").val();
    let school = $("input[placeholder='学院']").val();
    let hometown = $("input[placeholder='籍贯']").val();
    let reset_info = {
        requestFunction:"resetPassword",
        Name:name,
        StudentID:studentID,
        School:school,
        Hometown:hometown,
    };
    $.post("/Ajax/Users/login.php",
        reset_info,
        function (data, status) {
            if (status === 'success') {
                let tempdata=JSON.parse(data);
                let returnCode=tempdata['ReturnCode'];
                data=tempdata['Data'];
                if(returnCode==='400') {
                    alert("参数错误，请联系管理员");
                }
                else if(returnCode==='401') {
                    alert('权限错误，请联系管理员处理');
                }
                else if(returnCode==='404') {
                    alert('功能不存在，请联系管理员更正文件调用');
                }
                else if(returnCode==='417') {
                    alert('功能错误，请联系管理员处理');
                }
                else if(returnCode==='499') {
                    swal({
                      title: "功能维护中，暂不提供重置密码功能",
                      icon: "warning",
                    });
                }
                else if (returnCode==='200' || returnCode==='301') {
                    //状态码301，提醒转移函数
                    if(returnCode==='301'){window.console.log('重置密码函数移至新位置');}
                    //状态码200，处理data
                    data = JSON.parse(data);
                    if(data[0]===true) {
                        swal({
                          title: "重置成功",
                          text: "密码已改为学号，建议登录后设置重新设置密码",
                          icon: "success",
                        }).then( (value)=>{window.location.reload();} );
                    }
                    else
                        swal({
                          title: "信息错误",
                          text: "提供的的信息不匹配，请确认输入，留意大小写以及输入前后的空格",
                          icon: "error",
                        });
                }
            } else {
                alert('请检查浏览器网络连接，建议刷新后重试');
            }
        });
}

$("#loginform").submit(function(e){
    e.preventDefault();
    login();
  });
$("#recoverform").submit(function(e){
    e.preventDefault();
    reset_password();
  });
window.onload=function () { // 记住密码相关功能函数
    remembered();
};