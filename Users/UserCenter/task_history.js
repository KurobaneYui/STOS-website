$(function () {
    $.get(
        "/Ajax/Users/get_schedule_history",
        function (data, status) {
            if (status === "success") {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "提供的数据错误，请联系管理员",
                        icon: "error",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误",
                        text: "非现场组组员无历史任务查看权限。",
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
                        title: "功能维护中，暂不允许获取历史任务",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取历史任务函数移至新位置'); }
                    //状态码200，处理data
                    fill_history_mission(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
})

function fill_history_mission(data) {
    fill_selfstudy_history(data['selfstudy']);
    fill_courses_history(data['courses']);
    replace_point();
}

function fill_selfstudy_history(data) {
    let tableBody = $("#task-history-selfstudy-table-body");
    tableBody.html("");
    for (one_data of data) {
        let status = "";
        if (one_data.recheck == true) { status = 'green_point' }
        else if (one_data.recheck == false && one_data.submitted == true) { status = 'yellow_point' }
        else { status = 'red_point' }
        let row = `
            <tr>
                <td>${one_data['date']}</td>
                <td>${one_data['classroom_name']}</td>
                <td><img height="15" width="15" src-data="${status}"></td>
            </tr>
        `;
        tableBody.append(row);
    }
}

function fill_courses_history(data) {
    let tableBody = $("#task-history-inspectclass-table-body");
    tableBody.html("");
    for (one_data of data) {
        let status = "";
        if (one_data.recheck == true) { status = 'green_point' }
        else if (one_data.recheck == false && one_data.submitted == true) { status = 'yellow_point' }
        else { status = 'red_point' }
        let row = `
            <tr>
                <td>${one_data['date']}</td>
                <td>${one_data['period']}</td>
                <td>${one_data['course_order']}</td>
                <td>${one_data['classroom_name']}</td>
                <td><img height="15" width="15" src-data="${status}"></td>
            </tr>
        `;
        tableBody.append(row);
    }
}