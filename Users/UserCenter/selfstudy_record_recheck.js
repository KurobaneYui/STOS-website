var allData = {};

$(function () {
    get_records();
})

function get_records() {
    $.get(
        "/Ajax/GroupManager/get_group_selfstudy_check_data",
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
                allData[one_schedule["selfstudy_id"]] = {
                    selfstudycheckdata_id: one_schedule['selfstudycheckdata_id'],
                    recheck_remark: one_schedule['recheck_remark'],
                    recheck: one_schedule['recheck'],
                    submitted: one_schedule['submitted'],
                    selfstudycheckabsent_id: one_schedule['selfstudycheckabsent_id'],
                    absentList: one_schedule['absentList']
                };
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
                        <th>请假</th>
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
                        <th>请假</th>
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
            <td>${record["askForLeave"] || ""}</td>
            <td>${record["remark"] || ""}</td>
            <td>${one_schedule["school_name"]}</td>
        </tr>
    `;
    table_body.append(row);
}

function fill_data_into_modal(row) {
    let selfstudy_id = $(row).attr("selfstudy_id").trim();
    let date = $(row).parent().parent().parent().prev().text().trim();
    let student_name = $($(row).children()[0]).text().trim();
    let classroomName = $($(row).children()[2]).text().trim();
    let selfstudycheckdata_id = allData[selfstudy_id].selfstudycheckdata_id;
    let selfstudycheckabsent_id = allData[selfstudy_id].selfstudycheckabsent_id;
    let submitted = allData[selfstudy_id].submitted;
    let recheck = allData[selfstudy_id].recheck;
    let recheck_remark = allData[selfstudy_id].recheck_remark;

    let modal_head = `${date} ${student_name} ${classroomName}`;
    $("#modal-subtitle").html(modal_head);
    $("#modal-subtitle").attr("selfstudy_id", selfstudy_id || 0);
    $("#modal-subtitle").attr("selfstudycheckdata_id", selfstudycheckdata_id || 0);
    $("#modal-subtitle").attr("selfstudycheckabsent_id", selfstudycheckabsent_id || 0);

    $("#recheck").prop("checked", recheck);
    $("#recheck").prop("disabled", !submitted);
    $("#remark").val(recheck_remark);
    $("#remark").prop("disabled", !submitted);
    $("#submit-button").prop("disabled", !submitted);
}

function submit() {
    try {
        let selfstudy_id = parseInt($("#modal-subtitle").attr("selfstudy_id").trim()) || -1;
        let selfstudycheckdata_id = parseInt($("#modal-subtitle").attr("selfstudycheckdata_id").trim()) || -1;
        let selfstudycheckabsent_id = parseInt($("#modal-subtitle").attr("selfstudycheckabsent_id").trim()) || -1;
        let rechecked = $("#recheck").prop("checked");
        let recheck_remark = $("#remark").val().trim();

        $.post(
            "/Ajax/GroupManager/submit_selfstudy_record_recheck",
            {
                selfstudy_id: selfstudy_id,
                selfstudycheckdata_id: selfstudycheckdata_id,
                selfstudycheckabsent_id: selfstudycheckabsent_id,
                rechecked: rechecked,
                recheck_remark: recheck_remark
            },
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
                            text: "非现场组组长或队长无组内早自习数据确认权限。",
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
                            title: "功能维护中，暂不允许确认组内早自习数据",
                            icon: "warning",
                        });
                    }
                    else if (returnCode === 200 || returnCode === 301) {
                        //状态码301，提醒转移函数
                        if (returnCode === 301) { window.console.log('确认组内早自习数据函数移至新位置'); }
                        //状态码200，处理data
                        swal({
                            title: "成功。",
                            icon: "success",
                        });
                        if (rechecked === true) $(`tr[selfstudy_id=${selfstudy_id}]`).attr('class', 'bg-label-success text-gray');
                        else if (allData[selfstudy_id].submitted === false) $(`tr[selfstudy_id=${selfstudy_id}]`).attr('class', 'bg-label-danger text-gray');
                        else $(`tr[selfstudy_id=${selfstudy_id}]`).attr('class', 'bg-label-warning text-gray');
                        allData[selfstudy_id].recheck = rechecked;
                        allData[selfstudy_id].recheck_remark = recheck_remark;
                    }
                }
                else
                    alert("请检查网络状况。");
            })
    } catch (error) {
        swal({
            title: "提供的数据错误，请联系管理员。",
            icon: "error",
        });
    }
}