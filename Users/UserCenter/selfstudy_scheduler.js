var shahe_rows_dict = Array();
var qingshuihe_rows_dict = Array();

function get_schedule_on_date() {
    $.post(
        "/Ajax/Users/get_schedule_on_date",
        { date: $("#form-date").val() },
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
                        text: "请先登录",
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
                        title: "功能维护中，暂不允许获取排班信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取排班信息函数移至新位置'); }
                    //状态码200，处理data
                    fill_selfstudy_schedule_card(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function fill_selfstudy_schedule_card(data) {
    $("#form-date").val(data["date"]);
    let shaheData = { scheduled: Array(), unscheduled: Array() };
    let qingshuiheData = { scheduled: Array(), unscheduled: Array() };

    for (let one_data of data['scheduled']) {
        if (one_data["campus"] === "沙河") {
            shaheData["scheduled"].push(one_data);
        } else {
            qingshuiheData["scheduled"].push(one_data);
        }
    }

    for (let one_data of data['unscheduled']) {
        if (one_data["campus"] === "沙河") {
            shaheData["unscheduled"].push(one_data);
        } else {
            qingshuiheData["unscheduled"].push(one_data);
        }
    }

    fill_shahe_schedule_table(shaheData);
    fill_qingshuihe_schedule_table(qingshuiheData);
}

function fill_shahe_schedule_table(data) {
    let shahe_table = $("#selfstudy-scheduler-shahe-table-body");
    shahe_table.html("");

    for (orderNumber in data["scheduled"]) {
        let one_data = data["scheduled"][orderNumber];
        let one_row = `
            <tr>
                <td>${orderNumber + 1}</td>
                <td>${one_data["classroom_name"]}</td>
                <td>${one_data["school_name"]}</td>
                <td>${one_data["selfstudy_info_remark"]}</td>
                <td>${one_data["schedule_student_name"]}</td>
                <td>${one_data["schedule_student_id"]}</td>
                <td>${one_data["schedule_student_department_name"]}</td>
                <td>
                    <i class='bx bx-up-arrow-alt fs-4' onclick="move_up(this)"></i>
                    <i class='bx bx-down-arrow-alt fs-4 ms-1 me-1' onclick="move_down(this)"></i>
                    <i class='bx bx-copy-alt fs-4 ms-1 me-1' onclick="duplicate_info(this)"></i>
                    <i class='bx bx-x fs-4 ms-1' onclick="delete_info(this)"></i>
                </td>
            </tr>
        `;
        shahe_table.append(one_row);
    }

    for (one_data of data["unscheduled"]) {
        let one_row = `
            <tr>
                <td>#</td>
                <td></td>
                <td></td>
                <td></td>
                <td>${one_data["student_name"]}</td>
                <td>${one_data["student_id"]}</td>
                <td>${one_data["student_department_name"]}</td>
                <td>
                    <i class='bx bx-up-arrow-alt fs-4' onclick="move_up(this)"></i>
                    <i class='bx bx-down-arrow-alt fs-4 ms-1 me-1' onclick="move_down(this)"></i>
                    <i class='bx bx-copy-alt fs-4 ms-1 me-1' onclick="duplicate_info(this)"></i>
                    <i class='bx bx-x fs-4 ms-1' onclick="delete_info(this)"></i>
                </td>
            </tr>
        `;
        shahe_table.append(one_row);
    }
}

function fill_qingshuihe_schedule_table(data) {
    let qingshuihe_table = $("#selfstudy-scheduler-qingshuihe-table-body");
    qingshuihe_table.html("");

    for (orderNumber in data["scheduled"]) {
        let one_data = data["scheduled"][orderNumber];
        let one_row = `
            <tr>
                <td>${orderNumber + 1}</td>
                <td>${one_data["classroom_name"]}</td>
                <td>${one_data["school_name"]}</td>
                <td>${one_data["selfstudy_info_remark"]}</td>
                <td>${one_data["schedule_student_name"]}</td>
                <td>${one_data["schedule_student_id"]}</td>
                <td>${one_data["schedule_student_department_name"]}</td>
                <td>
                    <i class='bx bx-up-arrow-alt fs-4' onclick="move_up(this)"></i>
                    <i class='bx bx-down-arrow-alt fs-4 ms-1 me-1' onclick="move_down(this)"></i>
                    <i class='bx bx-copy-alt fs-4 ms-1 me-1' onclick="duplicate_info(this)"></i>
                    <i class='bx bx-x fs-4 ms-1' onclick="delete_info(this)"></i>
                </td>
            </tr>
        `;
        qingshuihe_table.append(one_row);
    }

    for (one_data of data["unscheduled"]) {
        let one_row = `
            <tr>
                <td>#</td>
                <td></td>
                <td></td>
                <td></td>
                <td>${one_data["student_name"]}</td>
                <td>${one_data["student_id"]}</td>
                <td>${one_data["student_department_name"]}</td>
                <td>
                    <i class='bx bx-up-arrow-alt fs-4' onclick="move_up(this)"></i>
                    <i class='bx bx-down-arrow-alt fs-4 ms-1 me-1' onclick="move_down(this)"></i>
                    <i class='bx bx-copy-alt fs-4 ms-1 me-1' onclick="duplicate_info(this)"></i>
                    <i class='bx bx-x fs-4 ms-1' onclick="delete_info(this)"></i>
                </td>
            </tr>
        `;
        qingshuihe_table.append(one_row);
    }
}