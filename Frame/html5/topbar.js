// JavaScript Document
$(function () {
    getTopbarInfo();
});


function getTopbarInfo() {
    $.get('/Ajax/Users/topbarInfo', function (data, status) {
        if (status === "success") {
            let returnCode = data['code'];
            if (returnCode === 400) {
                swal({
                    title: "参数错误，请联系管理员",
                    icon: "warning",
                });
            }
            else if (returnCode === 401) {
                swal({
                    title: "权限错误",
                    text: "如果未登录，请先登录",
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
                    title: "功能维护中，暂不允许topbar信息获取",
                    icon: "warning",
                });
            }
            else if (returnCode === 200 || returnCode === 301) {
                //状态码301，提醒转移函数
                if (returnCode === 301) { window.console.log('topbar信息获取函数移至新位置'); }
                //状态码200，处理data
                update_topbar(data["data"]);
            }
        }
        else {
            alert("请检查网络连接，或稍后再试");
        }
    });
}


function update_topbar(data) {
    let name = data['name'];
    $("#topbar-name").text(name);
    $("#formal-member").text(data['memberType']);
    update_badges(data['memberType']);
}


function update_badges(data) {
    $("#self-status-badges").html("")
    let counter = 0;

    let currentDate = new Date();
    let endMonthDate = new Date();
    endMonthDate.setMonth(endMonthDate.getMonth() + 1);
    endMonthDate.setDate(0);

    let badge = ``;
    badge = `
    <div class="col-auto">
        <span class="badge rounded-pill bg-label-success fs-6">查早：完成</span>
    </div>`;
    $("#self-status-badges").append(badge);
    badge = `
    <div class="col-auto">
        <span class="badge rounded-pill bg-label-warning fs-6">查课：未确认</span>
    </div>`;
    $("#self-status-badges").append(badge);
    badge = `
    <div class="col-auto">
        <span class="badge rounded-pill bg-label-danger fs-6">查课：未完成</span>
    </div>`;
    $("#self-status-badges").append(badge);
    if (endMonthDate.getDate() - currentDate.getDate() <= 10) {
        badge = `
        <div class="col-auto">
            <span class="badge rounded-pill bg-label-success fs-6">${`距&yen还剩` + String(endMonthDate.getDate() - currentDate.getDate()) + '天'}</span>
        </div>`;
    }
    else {
        badge = `
        <div class="col-auto">
            <span class="badge rounded-pill bg-label-secondary fs-6">钱离的还远</span>
        </div>`;
    }
    $("#self-status-badges").append(badge);
}


function logout() {
    $.get("/Ajax/Users/logout", function (data, status) {
        if (status === "success") {
            if (data["data"] === "finished") {
                window.location.href = "/Users/Authentication/logout.html";
            }
        }
    })
}