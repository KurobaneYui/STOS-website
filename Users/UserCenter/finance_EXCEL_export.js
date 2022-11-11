function download_EXCEL() {
    let date = $("#month").val();
    let teacherName = $("#teacher-name").val();
    let teacherPhone = $("#teacher-phone").val();
    let teacherEmail = $("#teacher-email").val();
    let teamLeaderName = $("#groupLeader-name").val();
    let teamLeaderPhone = $("#groupLeader-phone").val();
    let teamLeaderEmail = $("#groupLeader-email").val();
    let workPlace = $("#work-place").val();
    let firstWage = $("#first-wage").val();
    let secondWage = $("#second-wage").val();
    let thirdWage = $("#third-wage").val();
    let numForSubsidy = $("#num-for-subsidy").val();

    $.post(
        "/Ajax/TeamManager/download_finance_EXCEL",
        {
            date: date,
            teacherName: teacherName,
            teacherPhone: teacherPhone,
            teacherEmail: teacherEmail,
            teamLeaderName: teamLeaderName,
            teamLeaderPhone: teamLeaderPhone,
            teamLeaderEmail: teamLeaderEmail,
            workPlace: workPlace,
            firstWage: firstWage,
            secondWage: secondWage,
            thirdWage: thirdWage,
            numForSubsidy: numForSubsidy
        },
        function (data, status) {
            if (status === "success") {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "提供的数据错误，请联系管理员",
                        text: data['message'],
                        icon: "error",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误",
                        text: "仅队长可下载。",
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
                        title: "功能维护中，暂不允许下载财务报表",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('下载财务报表函数移至新位置'); }
                    //状态码200，处理data
                    window.open(data["data"]);
                }
            }
            else
                alert("请检查网络状况。");
        })
}