function changeInfo() { // 点击确定按钮的功能
    let name = $("#username").val();
    let gender = $("input[name='gender']:checked").val(); // female是女，male是男
    let ethnicity = $("#ethnicity").val();
    let hometown = $("#hometown").val();
    let infoRemark = $("#infoRemark").val()

    let phone = $("#phone").val();
    let qq = $("#qq").val();
    let campus = $("#campus").val();
    let school = $("#school").val();
    let dormitory_yuan = $("#dormitory_yuan").val();
    let dormitory_dong = $("#dormitory_dong").val();
    let dormitory_hao = $("#dormitory_hao").val();

    let application_bankcard = $("#application_bankcard").val();
    let application_name = $("#application_name").val();
    let application_student_id = $("#application_student_id").val();
    let subsidyDossier = $("#subsidyDossier").prop('checked'); // false否，true是
    let wageRemark = $("#wageRemark").val()

    let password = $("#password").val();
    let changePassword = $("#passwordChange").prop('checked');

    let register_info = {
        "name": name,
        "gender": gender === 'male' ? '男' : '女',
        "ethnicity": ethnicity,
        "hometown": hometown,
        "infoRemark": infoRemark,

        "phone": phone,
        "qq": qq,
        "campus": campus,
        "school": school,
        "dormitory_yuan": dormitory_yuan,
        "dormitory_dong": dormitory_dong,
        "dormitory_hao": dormitory_hao,

        "application_bankcard": application_bankcard,
        "application_name": application_name,
        "application_student_id": application_student_id,
        "subsidyDossier": subsidyDossier,
        "wageRemark": wageRemark,
    };
    if (changePassword === true) register_info.password = password;

    $.post("/Ajax/Users/change_personal_info",
        register_info,
        function (data, status) {
            if (status === 'success') {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "内容不符合要求",
                        text: data['message'],
                        icon: "error",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误",
                        text: data['message'],
                        icon: "error",
                    });
                }
                else if (returnCode === 404) {
                    swal({
                        title: "功能不存在，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 417) {
                    swal({
                        title: "功能错误，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 498) {
                    swal({
                        title: "数据库异常，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 499) {
                    swal({
                        title: "功能维护中，暂不允许修改个人信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('修改个人信息函数移至新位置'); }
                    //状态码200，处理data
                    swal({
                        title: "修改成功",
                        text: data['message'],
                        icon: "success",
                    }).then((value) => { $("#passwordChange").prop('checked', true).click(); getTopbarInfo(); });
                }
            }
            else {
                alert('请检查浏览器网络连接，建议刷新后重试');
            }
        });
}


function confirmDelete() {
    if ($('#deleteConfirm').val() === '我已知晓删除账户的影响且确认注销账户') {
        $.post("/Ajax/Users/delete_personal_info",
            { 'confirmDelete': 'confirm' },
            function (data, status) {
                if (status === 'success') {
                    let returnCode = data['code'];
                    if (returnCode === 400) {
                        swal({
                            title: "内容不符合要求",
                            text: data['message'],
                            icon: "error",
                        });
                    }
                    else if (returnCode === 401) {
                        swal({
                            title: "权限错误",
                            text: data['message'],
                            icon: "error",
                        });
                    }
                    else if (returnCode === 404) {
                        swal({
                            title: "功能不存在，请联系管理员",
                            icon: "warning",
                        });
                    }
                    else if (returnCode === 417) {
                        swal({
                            title: "功能错误，请联系管理员",
                            icon: "warning",
                        });
                    }
                    else if (returnCode === 498) {
                        swal({
                            title: "数据库异常，请联系管理员",
                            icon: "warning",
                        });
                    }
                    else if (returnCode === 499) {
                        swal({
                            title: "功能维护中，暂不允许注销个人信息",
                            icon: "warning",
                        });
                    }
                    else if (returnCode === 200 || returnCode === 301) {
                        //状态码301，提醒转移函数
                        if (returnCode === 301) { window.console.log('注销个人信息函数移至新位置'); }
                        //状态码200，处理data
                        swal({
                            title: "注销成功",
                            icon: "success",
                        }).then((value) => { window.location.href = "/index.html" });
                    }
                }
                else {
                    alert('请检查浏览器网络连接，建议刷新后重试');
                }
            });
    }
    else {
        swal({
            title: "请确认",
            text: "如需注销账户，请填写确认文字！",
            icon: "error",
        });
    }
}


function fill_personal_info(data) {
    $("#username").val(data["name"]);
    $("#id").val(data["student_id"]);

    if (data['gender'] === '男')
        $("#gender_male").prop('checked', true)
    else if (data["gender"] === "女")
        $("#gender_female").prop('checked', true)
    else
        alert("获取的数据有误，请联系管理员！error:gender");

    $("#ethnicity").val(data["ethnicity"]);
    $("#hometown").val(data["hometown"]);
    $("#infoRemark").val(data["infoRemark"]);

    $("#phone").val(data["phone"]);
    $("#qq").val(data["qq"]);
    $("#campus").val(data["campus"]);
    get_school();
    $("#school").val(data["school"]);
    $("#dormitory_yuan").val(data["dormitory_yuan"]);
    $("#dormitory_dong").val(data["dormitory_dong"]);
    $("#dormitory_hao").val(data["dormitory_hao"]);

    $("#application_bankcard").val(data["application_bankcard"]);
    $("#application_name").val(data["application_name"]);
    $("#application_student_id").val(data["application_student_id"]);
    if (data['subsidy_dossier'] == true)
        $("#subsidyDossier").prop('checked', true); // false否，true是
    else
        $("#subsidyDossier").prop('checked', false);
    $("#wageRemark").val(data["wageRemark"]);
}


$("#changeInfo").submit(function (e) {
    e.preventDefault(); // 表单提交事件发生时拦截，执行自定义函数。拦截表单提交事件并不阻止表单检查，因此可以自动检查required属性
    changeInfo();
});


$("#passwordChange").change(function (e) {
    if ($("#passwordChange").prop('checked') === true) {
        $("#password").prop("readonly", false);
        $("#password").prop("disabled", false);
    }
    else {
        $("#password").prop("readonly", true);
        $("#password").prop("disabled", true);
    }
})


$(function () {
    $.get(
        "/Ajax/Users/get_personal_info",
        function (data, status) {
            if (status === 'success') {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "数据提交错误，请联系管理员！",
                        icon: "error",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误",
                        text: data['message'],
                        icon: "error",
                    });
                }
                else if (returnCode === 404) {
                    swal({
                        title: "功能不存在，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 417) {
                    swal({
                        title: "功能错误，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 498) {
                    swal({
                        title: "数据库异常，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 499) {
                    swal({
                        title: "功能维护中，暂不允许获取个人信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取个人信息函数移至新位置'); }
                    //状态码200，处理data
                    fill_personal_info(data['data'][0]);
                }
            }
        })
})