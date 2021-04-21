// please import this document when jQuery has been imported

// 获取数据的函数
function get_info() {
    $.post("/Ajax/Users/personalInfo.php", {
            'requestFunction': 'getPersonalInfos'
        },
        function handleData(data, status) {
            if (status === "success") {
                let tempdata = JSON.parse(data);
                let returnCode = tempdata['ReturnCode'];
                data = tempdata['Data'];
                if (returnCode === '400') {
                    alert("参数错误，请联系管理员");
                } else if (returnCode === '401') {
                    alert('无权查看个人信息，请联系管理员处理');
                } else if (returnCode === '404') {
                    alert('功能不存在，请联系管理员更正文件调用');
                } else if (returnCode === '417') {
                    alert('功能错误，请联系管理员处理');
                } else if (returnCode === '200' || returnCode === '301') {
                    //状态码301，提醒转移函数
                    if (returnCode === '301') {
                        window.console.log('个人基本信息获取函数移至新位置');
                    }
                    //状态码200，处理data
                    let tempdata = JSON.parse(data);
                    for (let key in tempdata) {
                        if(key==="remark") {
                            $("#" + key).val(tempdata[key]);
                        }
                        else {
                            $("#" + key).prop("value", tempdata[key]);
                        }
                    }
                    update_dorm_name();
                }
            } else {
                alert("请检查网络连接，或稍后再试");
            }
        });
}
// 上传数据的函数
function upload_info() {
    var school = $("#school").prop("value");
    var name = $("#name").prop("value");
    var studentID = $("#studentID").prop("value");
    var gender = $("#gender").prop("value");
    var nation = $("#nation").prop("value");
    var hometown = $("#hometown").prop("value");
    var phone = $("#phone").prop("value");
    var QQ = $("#QQ").prop("value");
    var campus = $("#campus").prop("value");
    var dormitoryBlock = $("#dormitory-yuan").prop("value");
    var dormitoryBuild = $("#dormitory-dong").prop("value");
    var dormitoryRoom = $("#dormitory-hao").prop("value");
    var applicantName = $("#applicant-name").prop("value");
    var applicantStudentID = $("#applicant-studentID").prop("value");
    var applicanCreditCard = $("#applicant-credit-card").prop("value");
    var subsidyDossier = $("#subsidyDossier").prop("value");
    var remark = $("#remark").val();
    let uploadDataDict = { // 这里把收集到的数据整合
        'requestFunction': 'uploadPersonalInfos',
        "school": school,
        'name': name,
        'studentID': studentID,
        'gender': gender,
        'nation': nation,
        'hometown': hometown,
        'phone': phone,
        'QQ': QQ,
        'campus': campus,
        'dormitory-yuan': dormitoryBlock,
        'dormitory-dong': dormitoryBuild,
        'dormitory-hao': dormitoryRoom,
        'applicant-name': applicantName,
        'applicant-studentID': applicantStudentID,
        'applicant-credit-card': applicanCreditCard,
        'subsidyDossier': subsidyDossier,
        'remark': remark};
    if ($('#password-enable').prop('checked')) { // 如果要修改密码，则记录密码信息
        uploadDataDict['password'] = $('#password').prop('value');
    }
    $.post("/Ajax/Users/personalInfo.php", uploadDataDict,
        function handleData(data, status) {
            if (status === "success") {
                let tempdata = JSON.parse(data);
                let returnCode = tempdata['ReturnCode'];
                data = tempdata['Data'];
                if (returnCode === '400') {
                    // 提醒出错的数据
                    tmp = JSON.parse(tempdata.Message);
                    var errorString = ''
                    for (key in tmp) {
                        let element = $(`label[for=${key}`);
                        errorString += ('* '+element.text()+tmp[key]+'\n');
                        element.prop("style","background: rgba(210,50,50,0.5)");
                    }
                    errorString+='如对信息检查有疑问，请联系管理员，谢谢！';
                    swal({
                        title: "信息有误",
                        text: errorString,
                        icon: "error",
                    });
                } else if (returnCode === '401') {
                    alert('无权修改信息，请联系管理员处理');
                } else if (returnCode === '404') {
                    alert('功能不存在，请联系管理员更正文件调用');
                } else if (returnCode === '417') {
                    alert('功能错误，请联系管理员处理');
                } else if (returnCode === '200' || returnCode === '301') {
                    //状态码301，提醒转移函数
                    if (returnCode === '301') {
                        window.console.log('个人基本信息提交获取函数移至新位置');
                    }
                    //状态码200，处理data
                    JSON.parse(data);
                    swal({
                        title: "修改成功",
                        text: "确认后会刷新页面以更新信息，如果没有请手动刷新",
                        icon: "success",
                    }).then((value) => {
                        window.location.reload();
                    })
                }
            } else {
                alert("请检查网络连接，或稍后再试");
            }
        });
}

// 点击提交按钮的函数
$("#personal-info-form").submit(function (e) {
    e.preventDefault();
    upload_info();
});

// 更新对应宿舍名称的函数
function update_dorm_name(){
    // 根据校区选择，更新disabled
    let camp = $("#campus").prop("value");
    for(let i of $("#dormitory-yuan option")) {
        if(camp==="清水河" && (i.value==='校内'||i.value==='校外')) {
            $(i).prop("disabled",true);
        } else if(camp==="沙河" && (i.value==='学知苑'||i.value==='硕丰苑')) {
            $(i).prop("disabled",true);
        } else if(camp==="" && i.value!=='') {
            $(i).prop("disabled",true);
        } else {
            $(i).prop("disabled",false);
        }
        if(i.value===$("#dormitory-yuan").prop("value") && $(i).prop("disabled")) {
            $("#dormitory-yuan").prop("value",'');
        }
    }
}
// 修改校区后更新对应宿舍名称
$("#campus").change(update_dorm_name);

// 页面加载完成时的内容
$(function () { // 页面加载完成后处理一下switchery
    $("[data-toggle='popover']").popover();
    let elems = Array.prototype.slice.call(document.querySelectorAll('.switchery-idx'));
    elems.forEach(function (html) {
        new Switchery(html, {
            color: '#64bd63',
            secondaryColor: '#e13c1e',
            size: 'small'
        });
    });
    get_info();
    setTimeout("$('#alert-infos').fadeOut(700)", 5000); // 提示框延时5s消失
    // 修改密码按钮开关影响密码输入框的启用和禁用
    $("#password-enable").change(function () {
        $('#password').prop('readonly', !$("#password-enable").prop('checked'))
    })
});