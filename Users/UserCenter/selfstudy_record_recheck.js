var allRecheckRemark = {};

$(function () {
    get_records();
})

function get_records() {
    $.get(
        "/Ajax/DataManager/get_group_selfstudy_check_data",
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
                        text: "非现场组组长或队长无组内早自习数据查看权限。",
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
                        title: "功能维护中，暂不允许获取组内早自习数据",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取组内早自习数据函数移至新位置'); }
                    //状态码200，处理data
                    fill_group_selfstudy_check_data(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function fill_group_selfstudy_check_data(data) {
    let card_container = $("#card-container");
    card_container.html("");

    for (department_id in data) {
        let one_department = data[department_id];
        let department_name = one_department['department_name'];

        let table_container = add_card(card_container, department_name, department_id);

        for (date in one_department["data"]) {
            let table_body = add_table(table_container, department_id, date);

            for (one_schedule of one_department["data"][date]) {
                add_row(table_body, one_schedule);
                allRecheckRemark[one_schedule["selfstudy_id"]] = one_schedule["recheck_remark"];
            }
        }
    }
}

function add_card(card_container, department_name, department_id) {
    let card = `
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">${department_name}</h5>
                <div class="card-body">
                    <div class="row g-2" id="${department_id}-table-container">
                    </div>
                </div>
            </div>
        </div>
    `;
    card_container.append(card);
    return $(`#${department_id}-table-container`);
}

function add_table(table_container, department_id, date) {
    let table = `
        <h6 class="card-text mt-3 mb-0">${date}</h6>
        <div class="col-12 table-responsive text-nowrap">
            <table class="table table-hover text-center">
                <thead>
                    <tr>
                        <th>姓名</th>
                        <th>学号</th>
                        <th>教室</th>
                        <th>应到</th>
                        <th>第一次出勤</th>
                        <th>迟到</th>
                        <th>第二次出勤</th>
                        <th>早退</th>
                        <th>备注</th>
                        <th>学院</th>
                    </tr>
                </thead>
                <tbody id="selfstudy-record-table-body-${department_id}-${date}">
                </tbody>
                <tfoot>
                    <tr>
                        <th>姓名</th>
                        <th>学号</th>
                        <th>教室</th>
                        <th>应到</th>
                        <th>第一次出勤</th>
                        <th>迟到</th>
                        <th>第二次出勤</th>
                        <th>早退</th>
                        <th>备注</th>
                        <th>学院</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
    table_container.append(table);
    return $(`#selfstudy-record-table-body-${department_id}-${date}`);
}

function add_row(table_body, one_schedule) {
    let record = JSON.parse(one_schedule['record']);
    let row_color = '';
    if (one_schedule.submitted === false) row_color = "bg-label-danger";
    else if (one_schedule.recheck === false) row_color = "bg-label-warning";
    else row_color = "bg-label-success";
    let row = `
        <tr data-bs-toggle="modal"
            data-bs-target="#recheck-selfstudy-record"
            onclick="fill_data_into_modal(this)"
            selfstudy_id=${one_schedule["selfstudy_id"]}
            class="${row_color} text-gray">
            <td>${one_schedule["actual_student_name"]}</td>
            <td>${one_schedule["actual_student_id"]}</td>
            <td>${one_schedule["classroom_name"]}</td>
            <td>${one_schedule["student_supposed"]}</td>
            <td>${record["firstPresent"] || ""}</td>
            <td>${record["absent"] || ""}</td>
            <td>${record["secondPresent"] || ""}</td>
            <td>${record["leaveEarly"] || ""}</td>
            <td>${record["remark"] || ""}</td>
            <td>${one_schedule["school_name"]}</td>
        </tr>
    `;
    table_body.append(row);
}

function fill_data_into_modal(row) {
    let selfstudy_id = $(row).attr("selfstudy_id");
    let date = $(row).parent().parent().parent().prev().text();
    let student_name = $($(row).children()[0]).text();
    let classroomName = $($(row).children()[2]).text();
    let notSubmitted = $(row).attr("class").includes("bg-label-danger");
    let recheck = $(row).attr("class").includes("bg-label-success");
    let recheck_remark = allRecheckRemark[selfstudy_id];

    let modal_head = `${date} ${student_name} ${classroomName}`;
    $("#modal-subtitle").html(modal_head);
    $("#modal-subtitle").attr("selfstudy_id", selfstudy_id || 0);

    $("#recheck").prop("checked",recheck);
    $("#recheck").prop("disabled",notSubmitted);
    $("#remark").val(recheck_remark);
    $("#remark").prop("disabled",notSubmitted);
}

function submit() {
    try {
        throw "暂不提供此功能";
        let selfstudy_id = parseInt($("#modal-subtitle").attr("selfstudy_id"));
        if (selfstudy_id === 0) { throw "Selfstudy ID Illegal !"; }

        let firstPresent = parseInt($("#firstPresent").val());
        let absent = parseInt($("#absent").val());
        let secondPresent = parseInt($("#secondPresent").val());
        let leaveEarly = parseInt($("#leaveEarly").val());
        let remark = $("#remark").val().trim();

        let absent_table_body = $("#selfstudy-absent-list-table-body");
        let absent_list = Array();
        for (row of absent_table_body.children()) {
            let student_name = $(row).children().first().text().trim();
            let student_id = $(row).children().first().next().text().trim();

            absent_list.push({ student_name: student_name, student_id: student_id });
        }

        $.ajax({
            url: "/Ajax/DataManager/submit_selfstudy_record",
            method: "POST",
            data: JSON.stringify({
                selfstudy_id: selfstudy_id,
                record: {
                    firstPresent: firstPresent,
                    absent: absent,
                    secondPresent: secondPresent,
                    leaveEarly: leaveEarly,
                    remark: remark,
                },
                absentList: absent_list
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
                            text: "仅现场组可编辑。",
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
                            title: "功能维护中，暂不允许提交早自习记录信息",
                            icon: "warning",
                        });
                    }
                    else if (returnCode === 200 || returnCode === 301) {
                        //状态码301，提醒转移函数
                        if (returnCode === 301) { window.console.log('提交早自习记录函数移至新位置'); }
                        //状态码200，处理data
                        get_records();
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
    } catch (error) {
        swal({
            title: "暂不提供此功能",
            icon: "error",
        });
        // swal({
        //     title: "提供的数据错误，请检查或联系管理员。",
        //     icon: "error",
        // });
    }
}