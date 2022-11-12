function get_campus() {
    $.get("/Ajax/DataManager/get_campus_for_form", function (data, status) {
        if (status === 'success') {
            let returnCode = data['code'];
            if (returnCode === 400) {
                swal({
                    title: "参数错误，请联系管理员",
                    icon: "warning",
                });
            }
            else if (returnCode === 401) {
                swal({
                    title: "权限错误，请联系管理员",
                    icon: "warning",
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
                    title: "功能维护中，暂不允许获取校区信息",
                    icon: "warning",
                });
            }
            else if (returnCode === 200 || returnCode === 301) {
                //状态码301，提醒转移函数
                if (returnCode === 301) { window.console.log('获取校区信息函数移至新位置'); }
                //状态码200，处理data
                for (let i of data['data']) {
                    $("#campus").append(`<option value="${i['campus']}">${i['campus']}</option>`);
                }
                $("#campus").val("");
            }
        }
        else {
            alert('请检查浏览器网络连接，建议刷新后重试');
        }
    })
}

function get_school() {
    let originalSelect = $("#school").val();
    let existsInNewOptions = false;
    $("#school").html("");
    $.ajaxSettings.async = false;
    $.post("/Ajax/DataManager/get_school_for_form",
        { campus: $("#campus").val() },
        function (data, status) {
            if (status === 'success') {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "参数错误，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误，请联系管理员",
                        icon: "warning",
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
                        title: "功能维护中，暂不允许获取学院信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取学院信息函数移至新位置'); }
                    //状态码200，处理data
                    for (let i of data['data']) {
                        $("#school").append(`<option value="${i['name']}">${i['name']}</option>`);
                        if (originalSelect === i['name']) existsInNewOptions = true;
                    }
                    if (existsInNewOptions) { $("#school").val(originalSelect); }
                    else { $("#school").val(""); }
                }
            }
            else {
                alert('请检查浏览器网络连接，建议刷新后重试');
            }
        })
    $.ajaxSettings.async = true;
}

$(function () {
    get_campus();
    $("#campus").change(function () {
        get_school();
    })
})