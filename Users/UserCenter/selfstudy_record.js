var AllAbsentListData = {};

$(function () {
    $.get(
        "/Ajax/DataManager/get_selfstudy_check_data",
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
                        text: "非现场组组员无早自习数据查看权限。",
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
                        title: "功能维护中，暂不允许获取早自习数据",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取早自习数据函数移至新位置'); }
                    //状态码200，处理data
                    fill_selfstudy_check_data(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
})

function fill_selfstudy_check_data(data) {
    let selfstudy_record_table_body = $("#selfstudy-record-table-body");
    selfstudy_record_table_body.html("");

    for (one_records of data) {
        AllAbsentListData[one_records["selfstudy_id"]] = one_records["absent"];

        let record = JSON.parse(one_records['record']);
        let row = `
            <tr data-bs-toggle="modal"
                data-bs-target="#update-selfstudy-record"
                onclick="fill_data_into_modal(this)"
                selfstudy_id=${one_records["selfstudy_id"]}>
                <td>${one_records["date"]}</td>
                <td>${one_records["classroom_name"]}</td>
                <td>${one_records["student_supposed"]}</td>
                <td>${record["firstPresent"] || ""}</td>
                <td>${record["absent"] || ""}</td>
                <td>${record["secondPresent"] || ""}</td>
                <td>${record["leaveEarly"] || ""}</td>
                <td>${record["remark"] || ""}</td>
                <td>${one_records["school_name"]}</td>
                <td>${one_records["campus"]}</td>
            </tr>
        `;
        selfstudy_record_table_body.append(row)
    }
}

function fill_data_into_modal(row) {
    let selfstudy_id = $(row).attr("selfstudy_id");
    let date = $($(row).children()[0]).text();
    let classroomName = $($(row).children()[1]).text();
    let firstPresent = $($(row).children()[3]).text();
    let absent = $($(row).children()[4]).text();
    let secondPresent = $($(row).children()[5]).text();
    let leaveEarly = $($(row).children()[6]).text();
    let remark = $($(row).children()[7]).text();
    let schoolName = $($(row).children()[8]).text();
    let campus = $($(row).children()[9]).text();

    let modal_head = `${date} ${campus}校区 ${schoolName} ${classroomName}`;
    $("#firstPresent").val(parseInt(firstPresent) || 0);
    $("#absent").val(parseInt(absent) || 0);
    $("#secondPresent").val(parseInt(secondPresent) || 0);
    $("#leaveEarly").val(parseInt(leaveEarly) || 0);
    $("#remark").val(remark);
    $("#modal-subtitle").html(modal_head);

    let absent_table_body = $("#selfstudy-absent-list-table-body");
    absent_table_body.html("");

    let absentList = JSON.parse(AllAbsentListData[selfstudy_id]);
    for (one_student of absentList) {
        let row = `
            <tr>
                <td>${one_student['student_name']}</td>
                <td>${one_student['student_id']}</td>
                <td>
                    <button class="btn btn-danger btn-sm rounded-pill" onclick="delete_absent_student(this)">删除</button>
                </td>
            </tr>
        `;
        absent_table_body.append(row);
    }
}

function delete_absent_student(button) {
    $(button).parent().parent().remove();
}

function add_absent_student() {
    let student_name = $("#student_name").val();
    let student_id = $("#student_id").val();

    let row = `
        <tr>
            <td>${student_name}</td>
            <td>${student_id}</td>
            <td>
                <button class="btn btn-danger btn-sm rounded-pill" onclick="delete_absent_student(this)">删除</button>
            </td>
        </tr>
    `;

    $("#selfstudy-absent-list-table-body").append(row);
}

function submit() {
    ;
}

function render_campus(campus) {
    if (campus === "清水河") return `<span class='badge bg-label-primary'>${campus}</span>`;
    else if (campus === "沙河") return `<span class='badge bg-label-warning'>${campus}</span>`;
}