// Please import this file after import jQuery core

$(function(){
    $.post("/Ajax/Programs/wage.php", {
            'requestFunction': 'wageInfo'
        },
        function handleData(data, status) {
            if (status === "success") {
                let tempdata = JSON.parse(data);
                let returnCode = tempdata['ReturnCode'];
                let Data = tempdata['Data'];//'' 'rows':n，'fields':['','','']，'results':Array(rows) ''
                if (returnCode === '400') {
                    alert("参数错误，请联系管理员");
                }
                else if (returnCode === '401') {
                    alert('无权查看信息，请联系管理员处理');
                }
                else if (returnCode === '404') {
                    alert('功能不存在，请联系管理员更正文件调用');
                }
                else if (returnCode === '417') {
                    alert('功能错误，请联系管理员处理');
                }
                else if (returnCode === '200' || returnCode === '301') {
                    //状态码301，提醒转移函数
                    if (returnCode === '301') { window.console.log('全队成员信息获取函数移至新位置'); }
                    //状态码200，处理data
                    tempdata = JSON.parse(Data);
                    $("#wage-date").val(tempdata["日期"]);
                    $("#wage-teacher-name").val(tempdata["指导老师姓名"]);
                    $("#wage-teacher-phone").val(tempdata["指导老师电话"]);
                    $("#wage-teacher-email").val(tempdata["指导老师邮箱"]);
                    $("#wage-teamLeader-name").val(tempdata["骨干姓名"]);
                    $("#wage-teamLeader-phone").val(tempdata["骨干电话"]);
                    $("#wage-teamLeader-email").val(tempdata["骨干邮箱"]);
                    $("#wage-work-place").val(tempdata["办公地点"]);
                    $("#wage-first-money").val(tempdata["一档金额"]);
                    $("#wage-second-money").val(tempdata["二档金额"]);
                    $("#wage-third-money").val(tempdata["三档金额"]);
                    $("#wage-subsidyDossier-num").val(tempdata["建档立卡专设岗位"]);
                }
            }
            else {
                alert("请检查网络连接，或稍后再试");
            }
            return 0;
        })
});

function wage() {
    // 获取需要的数据
    let date = $("#wage-date").val();
    let teacherName = $("#wage-teacher-name").val();
    let teacherPhone = $("#wage-teacher-phone").val();
    let teacherEmail = $("#wage-teacher-email").val();
    let teamLeaderName = $("#wage-teamLeader-name").val();
    let teamLeaderPhone = $("#wage-teamLeader-phone").val();
    let teamLeaderEmail = $("#wage-teamLeader-email").val();
    let workPlace = $("#wage-work-place").val();
    let firstMoney = $("#wage-first-money").val();
    let secondMoney = $("#wage-second-money").val();
    let thirdMoney = $("#wage-third-money").val();
    let subsidyDossierNum = $("#wage-subsidyDossier-num").val();
    // 提交数据
    $.post("/Ajax/Programs/wage.php", {
            'requestFunction': 'wageFile',
            'date': date.replaceAll(" ",""),
            'teacherName': teacherName.replaceAll(" ",""),
            'teacherPhone': teacherPhone.replaceAll(" ",""),
            'teacherEmail': teacherEmail.replaceAll(" ",""),
            'teamLeaderName': teamLeaderName.replaceAll(" ",""),
            'teamLeaderPhone': teamLeaderPhone.replaceAll(" ",""),
            'teamLeaderEmail': teamLeaderEmail.replaceAll(" ",""),
            'workPlace': workPlace.replaceAll(" ",""),
            'firstMoney': firstMoney.replaceAll(" ",""),
            'secondMoney': secondMoney.replaceAll(" ",""),
            'thirdMoney': thirdMoney.replaceAll(" ",""),
            'subsidyDossierNum': subsidyDossierNum.replaceAll(" ","")
        },
        function handleData(data, status) {
            if (status === "success") {
                let tempdata = JSON.parse(data);
                let returnCode = tempdata['ReturnCode'];
                let Data = tempdata['Data'];//'' 'rows':n，'fields':['','','']，'results':Array(rows) ''
                if (returnCode === '400') {
                    alert("参数错误，请联系管理员");
                }
                else if (returnCode === '401') {
                    alert('无权查看信息，请联系管理员处理');
                }
                else if (returnCode === '404') {
                    alert('功能不存在，请联系管理员更正文件调用');
                }
                else if (returnCode === '417') {
                    alert('功能错误，请联系管理员处理');
                }
                else if (returnCode === '200' || returnCode === '301') {
                    //状态码301，提醒转移函数
                    if (returnCode === '301') { window.console.log('全队成员信息获取函数移至新位置'); }
                    //状态码200，处理data
                    //根据返回的连接引导下载
                    window.open(Data.substr(Data.indexOf("/tmpFiles")));
                }
            }
            else {
                alert("请检查网络连接，或稍后再试");
            }
            return 0;
        })
}