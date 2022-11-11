$(function () {
    get_school();
})

function get_school() {
    $.get(
        "/Ajax/DataManager/get_school",
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
                        text: "仅队长可查看和编辑。",
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
                        title: "功能维护中，暂不允许获取学院信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取学院信息函数移至新位置'); }
                    //状态码200，处理data
                    fill_school_table(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function fill_school_table(data) {
    let table_body = $("#school-table-body");

    table_body.html("");

    for (let one_school of data) {
        table_body.append(`
        <tr old-data='${one_school['school_id']}'>
            <td>${one_school['school_id']}</td>
            <td>${one_school['name']}</td>
            <td>${render_campus(one_school['campus'])}</td>
            <td><button class="btn btn-warning btn-sm rounded-pill" onclick="change_to_editable_row(this)">编辑</button></td>
        </tr>
        `);
    }
}

function change_to_editable_row(button) {
    let row = $(button).parent().parent();
    let cells = row.children();
    
    $(button).parent().append(`<button class="btn btn-primary btn-sm rounded-pill" onclick="upload_school(this)">提交</button><button class="btn btn-danger btn-sm rounded-pill" onclick="delete_school(this)">删除</button>`);
    $(button).remove();

    // 改序号格式
    let cell = cells.first();
    let tmp = cell.text();
    cell.html(`<input type="number" min="1" max="50" class="form-control text-center" style="min-width: 20px;" required/>`);
    cell.children().first().val(tmp);
    // 改名称格式
    cell = cell.next();
    tmp = cell.text();
    cell.html(`<input type="text" class="form-control text-center" style="min-width: 120px;" required/>`);
    cell.children().first().val(tmp);
    // 改校区格式
    cell = cell.next();
    tmp = cell.text();
    cell.html(`<input type="text" class="form-control text-center" style="min-width: 150px;" required/>`);
    cell.children().first().val(tmp);
}

function upload_school(button) {
    let element_row = $(button).parent().parent();
    $(button).parent().html("");

    let selector = element_row.children().first();
    let school_id = eval(selector.children().first().val());
    selector = selector.next();
    let name = selector.children().first().val();
    selector = selector.next();
    let campus = selector.children().first().val();
    let old_school_id = eval(element_row.attr("old-data"));

    if (school_id === undefined || school_id < 1 || school_id > 50) {
        alert("请检编号应在1~50之间");
    }

    $.post(
        "/Ajax/DataManager/update_school",
        { "school_id": school_id, "name": name, "campus": campus, "old_school_id": old_school_id },
        function (data, status) {
            if (status === "success") {
                get_school();
                let returnCode = data['code'];
                if (returnCode === 400) {
                    showToast('error', "提供的数据有误", data['message']);
                }
                else if (returnCode === 401) {
                    showToast('error', "权限错误", data['message']);
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
                        title: "功能维护中，暂不允许修改学院信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('修改学院信息函数移至新位置'); }
                    //状态码200，处理data
                    showToast('success', "成功", "数据已修改，如有问题可刷新重试。")
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function add_row_for_add_school() {
    let table_body = $("#school-table-body");
    table_body.append(`
    <tr>
        <td>
            <input type="number" min="1" max="50" class="form-control text-center" style="min-width: 20px;"/>
        </td>
        <td>
            <input type="text" class="form-control text-center" style="min-width: 120px;"/>
        </td>
        <td>
            <input type="text" class="form-control text-center" style="min-width: 150px;"/>
        </td>
        <td>
            <button class="btn btn-primary btn-sm rounded-pill" onclick="add_school(this)">确定</button>
        </td>
    </tr>
    `);
}

function add_school(element) {
    let element_row = $(element).parent().parent();
    let selector = element_row.children().first();
    let school_id = eval(selector.children().first().val());
    selector = selector.next();
    let name = selector.children().first().val();
    selector = selector.next();
    let campus = selector.children().first().val();

    $.post(
        "/Ajax/DataManager/add_school",
        { "school_id": school_id, "name": name, "campus": campus },
        function (data, status) {
            if (status === "success") {
                get_school();
                let returnCode = data['code'];
                if (returnCode === 400) {
                    showToast('error', "提供的数据有误", data['message']);
                }
                else if (returnCode === 401) {
                    showToast('error', "权限错误", data['message']);
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
                        title: "功能维护中，暂不允许添加学院信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('添加学院信息函数移至新位置'); }
                    //状态码200，处理data
                    showToast('success', "成功", "数据已添加，如有问题可刷新重试。")
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function delete_school(element) {
    let element_row = $(element).parent().parent();
    let selector = element_row.children().first();
    let school_id = eval(selector.children().first().val());
    selector = selector.next();
    let name = selector.children().first().val();
    selector = selector.next();
    let campus = selector.children().first().val();
    let old_school_id = eval(element_row.attr("old-data"));

    if (school_id === undefined || school_id < 1 || school_id > 50) {
        alert("请检编号应在1~50之间");
    }

    $.post(
        "/Ajax/DataManager/delete_school",
        { "school_id": school_id, "name": name, "campus": campus, "old_school_id": old_school_id },
        function (data, status) {
            if (status === "success") {
                get_school();
                let returnCode = data['code'];
                if (returnCode === 400) {
                    showToast('error', "提供的数据有误", data['message']);
                }
                else if (returnCode === 401) {
                    showToast('error', "权限错误", data['message']);
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
                        title: "功能维护中，暂不允许删除学院信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('删除学院信息函数移至新位置'); }
                    //状态码200，处理data
                    showToast('success', "成功", "数据已删除，如有问题可刷新重试。")
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function showToast(status, title, text) {
    let success =
        `<div class="bs-toast toast m-2 fade bg-success hide" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="1000">
        <div class="toast-header">
            <i class="bx bx-check me-2"></i>
            <div class="me-auto fw-semibold">${title}</div>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">${text}</div>
    </div>`
    let error =
        `<div class="bs-toast toast m-2 fade bg-danger hide" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
        <div class="toast-header">
            <i class="bx bx-x me-2"></i>
            <div class="me-auto fw-semibold">${title}</div>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">${text}</div>
    </div>`
    container = $("#toast-container");
    if (status === "success") {
        container.append(success);
        let a = new bootstrap.Toast(container.children().last());
        a.show();
    }
    else if (status === "error") {
        container.append(error);
        let a = new bootstrap.Toast(container.children().last());
        a.show();
    }
}

function render_campus(campus) {
    if (campus === "清水河") return `<span class='badge bg-label-primary'>${campus}</span>`;
    else if (campus === "沙河") return `<span class='badge bg-label-warning'>${campus}</span>`;
}