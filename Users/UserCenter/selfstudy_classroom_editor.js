function get_selfstudy_classroom_details(date) {
    $.post(
        "/Ajax/DataManager/get_selfstudy_classroom_details",
        { "date": date },
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
                        text: "仅数据组可查看。",
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
                        title: "功能维护中，暂不允许获取早自习教室信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取早自习教室函数移至新位置'); }
                    //状态码200，处理data
                    $("#form-date").val(date);
                    fill_selfstudy_classroom_table(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function fill_selfstudy_classroom_table(data) {
    let table_body = $(`#selfstudy-classroom-editor-table-body`);

    table_body.html("");
    table_body.parent().parent().next().html(`<button class="btn btn-sm btn-warning rounded-pill" onclick="change_to_editable_row(this)">编辑</button>`)

    let counter = 1;
    for (let one_record of data) {
        table_body.append(`
        <tr data-item-id='${one_record["selfstudy_id"]}'>
            <td>${counter}</td>
            <td>${render_campus(one_record["campus"])}</td>
            <td>${one_record["building"] + one_record["area"] + one_record["room"]}</td>
            <td>${one_record["sit_available"]}</td>
            <td>${one_record["school_name"]}</td>
            <td>${one_record["student_supposed"]}</td>
            <td>${one_record["remark"]}</td>
            <td><button class="btn btn-sm btn-danger rounded-pill"
                    onclick="delete_row(this)">删除</button></td>
        </tr>
        `);
        counter++;
    }
}

function render_campus(campus) {
    if (campus === "清水河") return `<span class='badge bg-label-primary'>${campus}</span>`;
    else if (campus === "沙河") return `<span class='badge bg-label-warning'>${campus}</span>`;
}

function add_editable_row() {
    let table_body = $(`#selfstudy-classroom-editor-table-body`);
    table_body.append(`
    <tr data-item-id='-1'>
        <td></td>
        <td>
            <select class="form-control" style="min-width: 80px;" required></select>
        </td>
        <td>
            <select class="form-control" style="min-width: 150px;" required></select>
        </td>
        <td>-</td>
        <td>
            <select class="form-select" style="min-width: 170px;" required></select>
        </td>
        <td><input class="form-control" type="number" style="min-width: 70px;" required/></td>
        <td><input class="form-control" type="text" style="min-width: 120px;" required/></td>
        <td><button class="btn btn-sm btn-danger rounded-pill" onclick="delete_row(this)">删除</button></td>
    </tr>
    `);
    let campusElement = table_body.children().last().children().first().next().children().first();
    let classroomElement = campusElement.parent().next().children().first();
    let schoolElement = classroomElement.parent().next().next().children().first();
    get_campus(campusElement);
    campusElement.change(function () { get_school(schoolElement, campusElement); get_classroom(classroomElement, campusElement); });
    classroomElement.change(function () { $(this).parent().next().text($(this).find("option:selected").first().attr("data-sit-available")) });
}

function change_to_editable_row(button) {
    $(button).parent().append(`<button class="btn btn-sm btn-success rounded-pill" onclick="add_editable_row()">新增</button>`);
    $(button).remove();

    let table_body = $("#selfstudy-classroom-editor-table-body");
    let rows = table_body.children();

    rows.each(function () {
        let row = $(this);
        let cells = row.children();

        // 序号跳过
        let cell = cells.first();
        let tmp = cell.text();
        // 改校区格式
        cell = cell.next();
        tmp = cell.text();
        cell.html(`
        <select class="form-control" style="min-width: 80px;" required>
            <option value="${tmp}">${tmp}</option>
        </select>`);
        let campusElement = cell.children().first();
        campusElement.val(tmp);
        get_campus(campusElement);
        // 改教室格式
        cell = cell.next();
        tmp = cell.text();
        cell.html(`
        <select class="form-control" style="min-width: 150px;" required>
            <option value="${tmp}">${tmp}</option>
        </select>`);
        let classroomElement = cell.children().first();
        classroomElement.val(tmp);
        get_classroom(classroomElement, campusElement);
        // 容纳人数跳过
        cell = cell.next();
        tmp = cell.text();
        // 改学院格式
        cell = cell.next();
        tmp = cell.text();
        cell.html(`
        <select class="form-select" style="min-width: 170px;" required>
            <option value="${tmp}">${tmp}</option>
        </select>`);
        let schoolElement = cell.children().first();
        schoolElement.val(tmp);
        get_school(schoolElement, campusElement);
        // 改应到人数格式
        cell = cell.next();
        tmp = cell.text();
        cell.html(`<input class="form-control" type="number" style="min-width: 70px;" required/>`);
        cell.children().first().val(tmp);
        // 改备注格式
        cell = cell.next();
        tmp = cell.text();
        cell.html(`<input class="form-control" type="text" style="min-width: 120px;" required/>`);
        cell.children().first().val(tmp);

        // 绑定校区改变则改变教室和学院
        campusElement.change(function () { get_school(schoolElement, campusElement); get_classroom(classroomElement, campusElement); });
        classroomElement.change(function () { $(this).parent().next().text($(this).find("option:selected").first().attr("data-sit-available")) });
    })
}

function delete_row(element) {
    let table_body = $(element).parent().parent();
    table_body.remove();
}

function get_submitted_selfstudy_date() {
    $.get(
        "/Ajax/DataManager/get_submitted_selfstudy_date",
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
                        text: "仅数据组可查看。",
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
                        title: "功能维护中，暂不允许获取已提交早自习教室信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取已提交早自习教室函数移至新位置'); }
                    //状态码200，处理data
                    fill_import_table_body_table(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function fill_import_table_body_table(data) {
    let table_body = $("#import-table-body");

    table_body.html("");

    for (let date of data) {
        table_body.append(`
        <tr>
            <td>${date['date']}</td>
            <td><button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-dismiss="modal"
            aria-label="Close" onclick="import_data(this)">导入</button></td>
        </tr>`);
    }
}

function import_data(button) {
    get_selfstudy_classroom_details($(button).parent().prev().text());
}

function submit() {
    let table_content = $("#selfstudy-classroom-editor-table-body").children();
    let classrooms = Array();
    for (row of table_content) {
        let current_cell = $(row).children().first().next();
        campus = current_cell.children().first().val();
        current_cell = current_cell.next();
        classroom_name = current_cell.children().first().val();
        current_cell = current_cell.next().next();
        school_name = current_cell.children().first().val();
        current_cell = current_cell.next();
        student_supposed = parseInt(current_cell.children().first().val());
        current_cell = current_cell.next();
        remark = current_cell.children().first().val();

        classrooms.push({
            campus: campus,
            classroom_name: classroom_name,
            school_name: school_name,
            student_supposed: student_supposed,
            remark: remark
        })
    }
    let selfstudy_classrooms_data = JSON.stringify({
        "date": $("#form-date").val(),
        "data": classrooms
    });
    $.ajax({
        url: "/Ajax/DataManager/upload_selfstudy_classroom",
        method: "POST",
        data: selfstudy_classrooms_data,
        // data: JSON.stringify(selfstudy_classrooms_data),
        contentType: 'application/json',
        success: function(data, status) {
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
                        title: "功能维护中，暂不允许提交早自习教室信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('提交早自习教室函数移至新位置'); }
                    //状态码200，处理data
                    swal({
                        title: "提交成功",
                        icon: "success",
                    });
                    get_selfstudy_classroom_details($("#form-date").val());
                }
            }
            else
                alert("请检查网络状况。");
        }
    })
}