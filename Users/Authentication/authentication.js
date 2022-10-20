function login() { // 点击登录按钮的功能
    let student = $("input[placeholder='学号或工号']").val();
    let password = $("input[placeholder='密码']").val();
    let login_info = {
        StudentID:student,
        Password:password
    };
    $.post("/Ajax/Users/login",
        login_info,
        function(data,status){
            if (status==='success') {
                let returnCode=data['code'];
                if(returnCode===400) {
                    swal({
                        title: "参数错误，请联系管理员",
                        icon: "warning",
                      });
                }
                else if(returnCode===401) {
                    swal({
                        title: "登录错误",
                        text: "用户名或密码错误，请确认输入，留意大小写与输入前后的空格",
                        icon: "error",
                      });
                }
                else if(returnCode===404) {
                    swal({
                        title: "功能不存在，请联系管理员",
                        icon: "warning",
                      });
                }
                else if(returnCode===417) {
                    swal({
                        title: "功能错误，请联系管理员",
                        icon: "warning",
                      });
                }
                else if(returnCode===498) {
                    swal({
                        title: "数据库异常，请联系管理员",
                        icon: "warning",
                      });
                }
                else if(returnCode===499) {
                    swal({
                      title: "功能维护中，暂不允许登录",
                      icon: "warning",
                    });
                }
                else if (returnCode===200 || returnCode===301) {
                    //状态码301，提醒转移函数
                    if(returnCode===301){window.console.log('登录函数移至新位置');}
                    //状态码200，处理data
                    $(window).attr('location',data["data"]);
                }
            }
            else {
                alert('请检查浏览器网络连接，建议刷新后重试');
            }
        });
}


function reset_password() { // 点击重置按钮的功能
    let name = $("#name").val();
    let studentID = $("#id").val();
    let school = $("#school").val();
    let hometown = $("#hometown").val();
    let reset_info = {
        Name:name,
        StudentID:studentID,
        School:school,
        Hometown:hometown
    };
    $.post("/Ajax/Users/resetPassword",
        reset_info,
        function (data, status) {
            if (status === 'success') {
                let returnCode=data['code'];
                if(returnCode===400) {
                    swal({
                        title: "参数错误，请联系管理员",
                        icon: "warning",
                      });
                }
                else if(returnCode===401) {
                    swal({
                        title: "信息错误",
                        text: "提供的的信息不匹配，请确认输入，留意大小写以及输入前后的空格",
                        icon: "error",
                      });
                }
                else if(returnCode===404) {
                    swal({
                        title: "功能不存在，请联系管理员",
                        icon: "warning",
                      });
                }
                else if(returnCode===417) {
                    swal({
                        title: "功能错误，请联系管理员",
                        icon: "warning",
                      });
                }
                else if(returnCode===498) {
                    swal({
                        title: "数据库异常，请联系管理员",
                        icon: "warning",
                      });
                }
                else if(returnCode===499) {
                    swal({
                      title: "功能维护中，暂不允许重置密码",
                      icon: "warning",
                    });
                }
                else if (returnCode===200 || returnCode===301) {
                    //状态码301，提醒转移函数
                    if(returnCode===301){window.console.log('重置密码函数移至新位置');}
                    //状态码200，处理data
                    swal({
                        title: "重置成功",
                        text: "密码已改为学号，建议登录后重新设置密码",
                        icon: "success",
                    }).then( (value)=>{$(window).attr('location',data["data"]);} );
                }
            } else {
                alert('请检查浏览器网络连接，建议刷新后重试');
            }
        });
}


function register() { // 点击注册按钮的功能
    let name = $("#username").val();
    let studentID = $("#id").val();
    let gender = $("input[name='gender']:checked").val(); // female是女，male是男
    let ethnicity = $("#ethnicity").val();
    let hometown = $("#hometown").val();

    let phone = $("#phone").val();
    let qq = $("#qq").val();
    let campus = $("#campus").val();
    let school = $("#school").val();
    let dormitory_yuan = $("#dormitory_yuan").val();
    let dormitory_dong = $("#dormitory_dong").val();
    let dormitory_hao = $("#dormitory_hao").val();

    let bank = $("#bank").val();
    let subsidyDossier = $("#subsidyDossier").prop('checked'); // false否，true是

    let password = $("#password").val();

    let claimBox = $("#terms-conditions").prop('checked');

    let register_info = {
        "name":name,
        "studentID":studentID,
        "gender":gender==='male'?'男':'女',
        "ethnicity":ethnicity,
        "hometown":hometown,
        
        "phone":phone,
        "qq":qq,
        "campus":campus,
        "school":school,
        "dormitory_yuan":dormitory_yuan,
        "dormitory_dong":dormitory_dong,
        "dormitory_hao":dormitory_hao,

        "bank":bank,
        "subsidyDossier":subsidyDossier,

        "password":password
    };
    if (claimBox===false) {
        $("#terms-conditions").focus();
        $("label[for=terms-conditions]").css("color","red");
        sweetAlert("请阅读个人信息说明");
        return false;
    }
    $.post("/Ajax/Users/register",
        register_info,
        function(data,status){
            if (status==='success') {
                let returnCode=data['code'];
                if(returnCode===400) {
                    swal({
                        title: "内容不符合要求",
                        text: data['message'],
                        icon: "error",
                      });
                }
                else if(returnCode===401) {
                    swal({
                        title: "注册错误",
                        text: "提供的学号已存在，请检查！",
                        icon: "error",
                      });
                }
                else if(returnCode===404) {
                    swal({
                        title: "功能不存在，请联系管理员",
                        icon: "warning",
                      });
                }
                else if(returnCode===417) {
                    swal({
                        title: "功能错误，请联系管理员",
                        icon: "warning",
                      });
                }
                else if(returnCode===498) {
                    swal({
                        title: "数据库异常，请联系管理员",
                        icon: "warning",
                      });
                }
                else if(returnCode===499) {
                    swal({
                      title: "功能维护中，暂不允许注册",
                      icon: "warning",
                    });
                }
                else if (returnCode===200 || returnCode===301) {
                    //状态码301，提醒转移函数
                    if(returnCode===301){window.console.log('注册函数移至新位置');}
                    //状态码200，处理data
                    swal({
                        title: "注册成功",
                        text: "请使用学号和密码登录。",
                        icon: "success",
                    }).then( (value)=>{$(window).attr('location',data["data"]);} );
                }
            }
            else {
                alert('请检查浏览器网络连接，建议刷新后重试');
            }
        });
}


$("#loginform").submit(function(e){
    e.preventDefault(); // 表单提交事件发生时拦截，执行自定义函数。拦截表单提交事件并不阻止表单检查，因此可以自动检查required属性
    login();
  });


$("#recoverform").submit(function(e){
    e.preventDefault(); // 表单提交事件发生时拦截，执行自定义函数。拦截表单提交事件并不阻止表单检查，因此可以自动检查required属性
    reset_password();
  });


$("#register").submit(function(e){
    e.preventDefault(); // 表单提交事件发生时拦截，执行自定义函数。拦截表单提交事件并不阻止表单检查，因此可以自动检查required属性
    register();
  });