var allData = {};

$(function () {
    $.ajaxSettings.async = false;
    get_xianchang_group_list();
    get_records();
    $.ajaxSettings.async = true;
})

function get_xianchang_group_list() {
    $.get(
        "/Ajax/GroupManager/get_xianchang_group_list",
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
                        text: "非现场组组长或队长无组内查课数据查看权限。",
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
                        title: "功能维护中，暂不允许获取组内查课数据",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取组内查课数据函数移至新位置'); }
                    //状态码200，处理data
                    $("#group-list-container").html("");
                    for (departmentInfo of data['data']) {
                        $("#group-list-container").append(`
                            <div class="col-auto">
                                <button type="button" onclick="toggerButton(this)" class="btn btn-sm btn-primary rounded-pill" department_id=${departmentInfo['department_id']}>${departmentInfo['department_name']}</button>
                            </div>
                        `);
                    }
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function get_records() {
    let group_id_list = Array();
    for (btd of $("#group-list-container").children()) {
        let department_id = parseInt($(btd).children().first().attr("department_id").trim());
        let selected = $(btd).children().first().hasClass("btn-primary");
        if (selected) group_id_list.push(department_id);
    }
    $.ajax({
        url: "/Ajax/GroupManager/get_group_courses_check_data",
        method: "POST",
        data: JSON.stringify({
            group_id_list: group_id_list
        }),
        // data: JSON.stringify(courses_classrooms_data),
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
                        text: "非现场组组长或队长无组内查课数据查看权限。",
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
                        title: "功能维护中，暂不允许获取组内查课数据",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取组内查课数据函数移至新位置'); }
                    //状态码200，处理data
                    fill_group_courses_check_data(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        }
    })
}

function fill_group_courses_check_data(data) {
    let card_container = $("#card-container");
    card_container.html("");

    for (date_period of data) {
        date = date_period['date'];
        period = date_period['period'];
        let table_container = add_card(card_container, date, period);
        for (one_department of date_period['data']) {
            let department_id = one_department['department_id'];
            let department_name = one_department['department_name'];
            let table_body = add_table(table_container, department_id, department_name, date, period);
            for (one_schedule of one_department['data']) {
                add_row(table_body, one_schedule);
                allData[one_schedule["course_id"]] = {
                    coursecheckdata_id: one_schedule['coursecheckdata_id'],
                    recheck_remark: one_schedule['recheck_remark'],
                    recheck: one_schedule['recheck'],
                    submitted: one_schedule['submitted'],
                };
            }
        }
    }
}

function add_card(card_container, date, period) {
    let card = `
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">${date} ${period}</h5>
                <div class="card-body">
                    <div class="row g-2" id="${date + "_" + period}-table-container">
                    </div>
                </div>
            </div>
        </div>
    `;
    card_container.append(card);
    return $(`#${date + "_" + period}-table-container`);
}

function add_table(table_container, department_id, department_name, date, period) {
    let table = `
        <h6 class="card-text mt-3 mb-0">${department_name}</h6>
        <div class="col-12 table-responsive text-nowrap">
            <table class="table table-hover text-center">
                <thead>
                    <tr>
                        <th>姓名</th>
                        <th>学号</th>
                        <th>教室</th>
                        <th>课程</th>
                        <th>应到</th>
                        <th>第一次出勤</th>
                        <th>第一次违纪</th>
                        <th>第二次出勤</th>
                        <th>第二次违纪</th>
                        <th>备注</th>
                        <th>年级</th>
                        <th>学院</th>
                        <th>表编号</th>
                    </tr>
                </thead>
                <tbody id="courses-record-table-body-${department_id}-${date}-${period}">
                </tbody>
                <tfoot>
                    <tr>
                        <th>姓名</th>
                        <th>学号</th>
                        <th>教室</th>
                        <th>课程</th>
                        <th>应到</th>
                        <th>第一次出勤</th>
                        <th>第一次违纪</th>
                        <th>第二次出勤</th>
                        <th>第二次违纪</th>
                        <th>备注</th>
                        <th>年级</th>
                        <th>学院</th>
                        <th>表编号</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
    table_container.append(table);
    return $(`#courses-record-table-body-${department_id}-${date}-${period}`);
}

function add_row(table_body, one_schedule) {
    let record = JSON.parse(one_schedule['record']);
    let row_color = '';
    if (one_schedule.submitted === false) row_color = "bg-label-danger";
    else if (one_schedule.recheck === false) row_color = "bg-label-warning";
    else row_color = "bg-label-success";
    let row = `
        <tr data-bs-toggle="modal"
            data-bs-target="#recheck-courses-record"
            onclick="fill_data_into_modal(this)"
            course_id=${one_schedule["course_id"]}
            class="${row_color} text-gray">
            <td>${one_schedule["actual_student_name"]}</td>
            <td>${one_schedule["actual_student_id"]}</td>
            <td>${one_schedule["classroom_name"]}</td>
            <td>${one_schedule["course_name"]}</td>
            <td>${one_schedule["student_supposed"]}</td>
            <td>${record["firstPresent"] !== undefined && record["firstPresent"] !== null ? record["firstPresent"] : ""}</td>
            <td>${record["firstDisciplinary"] !== undefined && record["firstDisciplinary"] !== null ? record["firstDisciplinary"] : ""}</td>
            <td>${record["secondPresent"] !== undefined && record["secondPresent"] !== null ? record["secondPresent"] : ""}</td>
            <td>${record["secondDisciplinary"] !== undefined && record["secondDisciplinary"] !== null ? record["secondDisciplinary"] : ""}</td>
            <td>${record["remark"] || ""}</td>
            <td>${one_schedule["grade"] || ""}</td>
            <td>${one_schedule["school_name"]}</td>
            <td>${one_schedule["course_order"]}</td>
        </tr>
    `;
    table_body.append(row);
}

function fill_data_into_modal(row) {
    let course_id = $(row).attr("course_id").trim();
    let date = $(row).parent().parent().parent().prev().text().trim();
    let student_name = $($(row).children()[0]).text().trim();
    let classroomName = $($(row).children()[2]).text().trim();
    let coursecheckdata_id = allData[course_id].coursecheckdata_id;
    let submitted = allData[course_id].submitted;
    let recheck = allData[course_id].recheck;
    let recheck_remark = allData[course_id].recheck_remark;

    let modal_head = `${date} ${student_name} ${classroomName}`;
    $("#modal-subtitle").html(modal_head);
    $("#modal-subtitle").attr("course_id", course_id || 0);
    $("#modal-subtitle").attr("coursecheckdata_id", coursecheckdata_id || 0);

    $("#recheck").prop("checked", recheck);
    $("#recheck").prop("disabled", !submitted);
    $("#remark").val(recheck_remark);
    $("#remark").prop("disabled", !submitted);
    $("#submit-button").prop("disabled", !submitted);
}

function submit() {
    try {
        let course_id = parseInt($("#modal-subtitle").attr("course_id").trim()) || -1;
        let coursecheckdata_id = parseInt($("#modal-subtitle").attr("coursecheckdata_id").trim()) || -1;
        let rechecked = $("#recheck").prop("checked");
        let recheck_remark = $("#remark").val().trim();

        $.post(
            "/Ajax/GroupManager/submit_courses_record_recheck",
            {
                course_id: course_id,
                coursecheckdata_id: coursecheckdata_id,
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
                            text: "非本组组长无组内查课数据确认权限。",
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
                            title: "功能维护中，暂不允许确认组内查课数据",
                            icon: "warning",
                        });
                    }
                    else if (returnCode === 200 || returnCode === 301) {
                        //状态码301，提醒转移函数
                        if (returnCode === 301) { window.console.log('确认组内查课数据函数移至新位置'); }
                        //状态码200，处理data
                        swal({
                            title: "成功。",
                            icon: "success",
                        });
                        if (rechecked === true) $(`tr[course_id=${course_id}]`).attr('class', 'bg-label-success text-gray');
                        else if (allData[course_id].submitted === false) $(`tr[course_id=${course_id}]`).attr('class', 'bg-label-danger text-gray');
                        else $(`tr[course_id=${course_id}]`).attr('class', 'bg-label-warning text-gray');
                        allData[course_id].recheck = rechecked;
                        allData[course_id].recheck_remark = recheck_remark;
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

function toggerButton(button) {
    $(button).hasClass("btn-outline-secondary") ? $(button).removeClass("btn-outline-secondary").addClass("btn-primary") : $(button).removeClass("btn-primary").addClass("btn-outline-secondary");
    get_records();
}