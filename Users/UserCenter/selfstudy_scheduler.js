function get_schedule_on_date() {
    $.post(
        "/Ajax/DataManager/get_schedule_on_date",
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

function remove() {
    $.post(
        "/Ajax/DataManager/remove_selfstudy_schedule",
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
                        title: "功能维护中，暂不允许删除排班信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('删除排班信息函数移至新位置'); }
                    //状态码200，处理data
                    get_schedule_on_date();
                    swal({
                        title: "成功删除排班",
                        icon: "success",
                    });
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function submit() {
    let shahe_table = $("#selfstudy-scheduler-shahe-table-body");
    let qingshuihe_table = $("#selfstudy-scheduler-qingshuihe-table-body");
    let shahe_data = Array();
    let qingshuihe_data = Array();

    for (row of shahe_table.children()) {
        let rowContent = getRowContent($(row));
        if (rowContent.selfstudy_id === undefined) break;
        if (rowContent.student_id === '' || rowContent.schedule_student_name === '' || setRowContent.student_department_name === '') continue;
        shahe_data.push({ selfstudy_id: rowContent.selfstudy_id, student_id: rowContent.schedule_student_id });
    }

    for (row of qingshuihe_table.children()) {
        let rowContent = getRowContent($(row));
        if (rowContent.selfstudy_id === undefined) break;
        if (rowContent.student_id === '' || rowContent.schedule_student_name === '' || setRowContent.student_department_name === '') continue;
        qingshuihe_data.push({ selfstudy_id: rowContent.selfstudy_id, student_id: rowContent.schedule_student_id });
    }

    $.ajax({
        url: "/Ajax/DataManager/submit_selfstudy_schedule",
        method: "POST",
        data: JSON.stringify({
            date: $("#form-date").val(),
            data: { qingshuihe: qingshuihe_data, shahe: shahe_data }
        }),
        // data: JSON.stringify(selfstudy_classrooms_data),
        contentType: 'application/json',
        success: function (data, status) {
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
                        text: "仅数据组可编辑。",
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
                        title: "功能维护中，暂不允许提交早自习排班信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('提交早自习排班函数移至新位置'); }
                    //状态码200，处理data
                    get_schedule_on_date();
                    swal({
                        title: "提交成功",
                        icon: "success",
                    });
                }
            }
            else
                alert("请检查网络状况。");
        }
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
    refreshRowOrderNumber();
}

function move_up(button) {
    let selectedRowCurrent = $(button).parent().parent();
    let selectedRowCurrentContent = getRowContent(selectedRowCurrent);
    // 判断是不是第一行，如果上一行非空则可以挪动，如果上一行空则不挪动
    let selectedRowAnother = selectedRowCurrent.prev();
    if (selectedRowAnother.length !== 0) {
        let selectedRowAnotherContent = getRowContent(selectedRowAnother);

        [selectedRowCurrentContent.schedule_student_name, selectedRowAnotherContent.schedule_student_name] = [selectedRowAnotherContent.schedule_student_name, selectedRowCurrentContent.schedule_student_name];
        [selectedRowCurrentContent.schedule_student_id, selectedRowAnotherContent.schedule_student_id] = [selectedRowAnotherContent.schedule_student_id, selectedRowCurrentContent.schedule_student_id];
        [selectedRowCurrentContent.schedule_student_department_name, selectedRowAnotherContent.schedule_student_department_name] = [selectedRowAnotherContent.schedule_student_department_name, selectedRowCurrentContent.schedule_student_department_name];

        setRowContent(selectedRowAnother, selectedRowAnotherContent);
        setRowContent(selectedRowCurrent, selectedRowCurrentContent);
    }
    // 处理一下序号
    refreshRowOrderNumber();
}

function move_down(button) {
    let selectedRowCurrent = $(button).parent().parent();
    let selectedRowCurrentContent = getRowContent(selectedRowCurrent);

    // 判断是不是最后一行，如果下一行非空则可以挪动，如果下一行空则不挪动
    let selectedRowAnother = selectedRowCurrent.next();
    if (selectedRowAnother.length !== 0) {
        let selectedRowAnotherContent = getRowContent(selectedRowAnother);

        [selectedRowCurrentContent.schedule_student_name, selectedRowAnotherContent.schedule_student_name] = [selectedRowAnotherContent.schedule_student_name, selectedRowCurrentContent.schedule_student_name];
        [selectedRowCurrentContent.schedule_student_id, selectedRowAnotherContent.schedule_student_id] = [selectedRowAnotherContent.schedule_student_id, selectedRowCurrentContent.schedule_student_id];
        [selectedRowCurrentContent.schedule_student_department_name, selectedRowAnotherContent.schedule_student_department_name] = [selectedRowAnotherContent.schedule_student_department_name, selectedRowCurrentContent.schedule_student_department_name];

        setRowContent(selectedRowAnother, selectedRowAnotherContent);
        setRowContent(selectedRowCurrent, selectedRowCurrentContent);
    }
    // 处理一下序号
    refreshRowOrderNumber();
}

function duplicate_info(button) {
    let selectedRow = $(button).parent().parent();
    let selectedTable = selectedRow.parent();
    let tmpContent = getRowContent(selectedRow);
    tmpContent.student_name = tmpContent.schedule_student_name;
    tmpContent.student_id = tmpContent.schedule_student_id;
    tmpContent.student_department_name = tmpContent.schedule_student_department_name;
    addUnscheduledRow(selectedTable, tmpContent);
    refreshRowOrderNumber();
}

function delete_info(button) {
    let selectedRow = $(button).parent().parent();
    if (getRowContent(selectedRow).selfstudy_id === undefined) {
        removeUnscheduledRow(selectedRow);
    } else {
        removeStudentInfoOfScheduledRow(selectedRow);
    }
    refreshRowOrderNumber();
}

function fill_shahe_schedule_table(data) {
    let shahe_table = $("#selfstudy-scheduler-shahe-table-body");
    shahe_table.html("");

    for (one_data of data["scheduled"]) {
        addScheduledRow(shahe_table, one_data);
    }

    for (one_data of data["unscheduled"]) {
        addUnscheduledRow(shahe_table, one_data)
    }
}

function fill_qingshuihe_schedule_table(data) {
    let qingshuihe_table = $("#selfstudy-scheduler-qingshuihe-table-body");
    qingshuihe_table.html("");

    for (one_data of data["scheduled"]) {
        addScheduledRow(qingshuihe_table, one_data);
    }

    for (one_data of data["unscheduled"]) {
        addUnscheduledRow(qingshuihe_table, one_data)
    }
}

function getRowContent(row) {
    let selfstudy_id = row.attr("selfstudy_id");
    let order_number = $(row.children()[0]).text().trim();
    if (order_number.startsWith("#")) {
        order_number = order_number.slice(1);
    }
    let classroom_name = $(row.children()[1]).text().trim();
    let school_name = $(row.children()[2]).text().trim();
    let selfstudy_info_remark = $(row.children()[3]).text().trim();
    let schedule_student_name = $(row.children()[4]).text().trim();
    let schedule_student_id = $(row.children()[5]).text().trim();
    let schedule_student_department_name = $(row.children()[6]).text().trim();

    return {
        selfstudy_id: selfstudy_id,
        order_number: order_number,
        classroom_name: classroom_name,
        school_name: school_name,
        selfstudy_info_remark: selfstudy_info_remark,
        schedule_student_name: schedule_student_name,
        schedule_student_id: schedule_student_id,
        schedule_student_department_name: schedule_student_department_name
    };
}

function setRowContent(row, data) {
    if (data.selfstudy_id === undefined) {
        row.removeAttr("selfstudy_id");
    } else {
        row.attr("selfstudy_id", data.selfstudy_id);
    }
    $(row.children()[1]).text(data.classroom_name);
    $(row.children()[2]).text(data.school_name);
    $(row.children()[3]).text(data.selfstudy_info_remark);
    $(row.children()[4]).text(data.schedule_student_name);
    $(row.children()[5]).text(data.schedule_student_id);
    $(row.children()[6]).text(data.schedule_student_department_name);
}

function addScheduledRow(table, data) {
    let one_row = `
        <tr selfstudy_id=${data["selfstudy_id"]}>
            <td></td>
            <td>${data["classroom_name"] ? data["classroom_name"] : ""}</td>
            <td>${data["school_name"] ? data["school_name"] : ""}</td>
            <td>${data["selfstudy_info_remark"] ? data["selfstudy_info_remark"] : ""}</td>
            <td>${data["schedule_student_name"] ? data["schedule_student_name"] : ""}</td>
            <td>${data["schedule_student_id"] ? data["schedule_student_id"] : ""}</td>
            <td>${data["schedule_student_department_name"] ? data["schedule_student_department_name"] : ""}</td>
            <td>
                <i class='bx bx-up-arrow-alt fs-4' onclick="move_up(this)"></i>
                <i class='bx bx-down-arrow-alt fs-4 ms-1 me-1' onclick="move_down(this)"></i>
                <i class='bx bx-copy-alt fs-4 ms-1 me-1' onclick="duplicate_info(this)"></i>
                <i class='bx bx-x fs-4 ms-1' onclick="delete_info(this)"></i>
            </td>
        </tr>
    `;
    table.append(one_row);
}

function addUnscheduledRow(table, data) {
    let one_row = `
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>${data["student_name"] ? data["student_name"] : ""}</td>
            <td>${data["student_id"] ? data["student_id"] : ""}</td>
            <td>${data["student_department_name"] ? data["student_department_name"] : ""}</td>
            <td>
                <i class='bx bx-up-arrow-alt fs-4' onclick="move_up(this)"></i>
                <i class='bx bx-down-arrow-alt fs-4 ms-1 me-1' onclick="move_down(this)"></i>
                <i class='bx bx-copy-alt fs-4 ms-1 me-1' onclick="duplicate_info(this)"></i>
                <i class='bx bx-x fs-4 ms-1' onclick="delete_info(this)"></i>
            </td>
        </tr>
    `;
    table.append(one_row);
}

function removeStudentInfoOfScheduledRow(row) {
    $(row.children()[4]).text("");
    $(row.children()[5]).text("");
    $(row.children()[6]).text("");
}

function removeUnscheduledRow(row) {
    row.remove();
}

function refreshRowOrderNumber() {
    let shahe_table = $("#selfstudy-scheduler-shahe-table-body");
    let qingshuihe_table = $("#selfstudy-scheduler-qingshuihe-table-body");

    let scheduledRowCounter = 1;
    let unscheduledRowCounter = 1;
    for (row of shahe_table.children()) {
        if ($(row).attr('selfstudy_id') !== undefined) {
            $(row).children().first().text(scheduledRowCounter);
            scheduledRowCounter++;
        } else {
            $(row).children().first().text("#" + unscheduledRowCounter);
            unscheduledRowCounter++;
        }
    }

    scheduledRowCounter = 1;
    unscheduledRowCounter = 1;
    for (row of qingshuihe_table.children()) {
        if ($(row).attr('selfstudy_id') !== undefined) {
            $(row).children().first().text(scheduledRowCounter);
            scheduledRowCounter++;
        } else {
            $(row).children().first().text("#" + unscheduledRowCounter);
            unscheduledRowCounter++;
        }
    }
}