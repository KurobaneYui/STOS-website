var AllAbsentListData = {};
var AllAskForLeaveListData = {};

$(function () {
    get_records();
})

function get_records() {
    $.get(
        "/Ajax/Users/get_selfstudy_check_data",
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
}

function fill_selfstudy_check_data(data) {
    let selfstudy_record_table_body = $("#selfstudy-record-table-body");
    selfstudy_record_table_body.html("");

    for (one_records of data) {
        AllAbsentListData[one_records["selfstudy_id"]] = one_records["absent"];
        AllAskForLeaveListData[one_records["selfstudy_id"]] = one_records["askForLeave"];

        let record = JSON.parse(one_records['record']);
        Date.prototype.format = function (formatStr) {
            var str = formatStr;
            var Week = ['日', '一', '二', '三', '四', '五', '六'];
            str = str.replace(/yyyy|YYYY/, this.getFullYear());
            str = str.replace(/MM/, (this.getMonth() + 1) > 9 ? (this.getMonth() + 1).toString() : '0' + (this.getMonth() + 1));
            str = str.replace(/dd|DD/, this.getDate() > 9 ? this.getDate().toString() : '0' + this.getDate());
            return str;
        }
        let currentDate = (new Date()).format("yyyy-MM-dd");
        let bg_class = '';
        if (currentDate === one_records['date']) bg_class = "class='bg-label-primary'";
        let row = `
            <tr ${bg_class}
                data-bs-toggle="modal"
                data-bs-target="#update-selfstudy-record"
                onclick="fill_data_into_modal(this)"
                selfstudy_id=${one_records["selfstudy_id"]}>
                <td>${one_records["date"]}</td>
                <td>${one_records["classroom_name"]}</td>
                <td>${one_records["student_supposed"]}</td>
                <td>${record["firstPresent"] !== undefined && record["firstPresent"] !== null ? record["firstPresent"] : ""}</td>
                <td>${record["absent"] !== undefined && record["absent"] !== null ? record["absent"] : ""}</td>
                <td>${record["secondPresent"] !== undefined && record["secondPresent"] !== null ? record["secondPresent"] : ""}</td>
                <td>${record["leaveEarly"] !== undefined && record["leaveEarly"] !== null ? record["leaveEarly"] : ""}</td>
                <td>${record["askForLeave"] !== undefined && record["askForLeave"] !== null ? record["askForLeave"] : ""}</td>
                <td>${record["remark"] || ""}</td>
                <td>${one_records["school_name"]}</td>
            </tr>
        `;
        selfstudy_record_table_body.append(row)
    }
}

function fill_data_into_modal(row) {
    let selfstudy_id = $(row).attr("selfstudy_id").trim();
    let date = $($(row).children()[0]).text().trim();
    let classroomName = $($(row).children()[1]).text().trim();
    let firstPresent = $($(row).children()[3]).text().trim();
    let absent = $($(row).children()[4]).text().trim();
    let secondPresent = $($(row).children()[5]).text().trim();
    let leaveEarly = $($(row).children()[6]).text().trim();
    let askForLeave = $($(row).children()[7]).text().trim();
    let remark = $($(row).children()[8]).text().trim();
    let schoolName = $($(row).children()[9]).text().trim();

    let modal_head = `${date} ${schoolName} ${classroomName}`;
    $("#firstPresent").val(parseInt(firstPresent) || 0);
    $("#absent").val(parseInt(absent) || 0);
    $("#secondPresent").val(parseInt(secondPresent) || 0);
    $("#leaveEarly").val(parseInt(leaveEarly) || 0);
    $("#askForLeave").val(parseInt(askForLeave) || 0);
    $("#remark").val(remark);
    $("#modal-subtitle").html(modal_head);
    $("#modal-subtitle").attr("selfstudy_id", selfstudy_id || 0);

    $("#student_name").val('')
    $("#student_id").val('')

    let absent_table_body = $("#selfstudy-absent-list-table-body");
    absent_table_body.html("");

    let absentList = JSON.parse(AllAbsentListData[selfstudy_id]);
    for (one_student of absentList) {
        let row = `
            <tr>
                <td>${one_student['student_name']}</td>
                <td>${one_student['student_id']}</td>
                <td>
                    <button class="btn btn-danger btn-sm rounded-pill" onclick="delete_student(this)">删除</button>
                </td>
            </tr>
        `;
        absent_table_body.append(row);
    }

    $("#student_name_askForLeave").val('')
    $("#student_id_askForLeave").val('')

    let askForLeave_table_body = $("#selfstudy-askForLeave-list-table-body");
    askForLeave_table_body.html("");

    let askForLeaveList = JSON.parse(AllAskForLeaveListData[selfstudy_id]);
    for (one_student of askForLeaveList) {
        let row = `
            <tr>
                <td>${one_student['student_name']}</td>
                <td>${one_student['student_id']}</td>
                <td>${one_student['reason']}</td>
                <td>
                    <button class="btn btn-danger btn-sm rounded-pill" onclick="delete_student(this)">删除</button>
                </td>
            </tr>
        `;
        askForLeave_table_body.append(row);
    }
}

function delete_student(button) {
    $(button).parent().parent().remove();
}

function add_absent_student() {
    let student_name = $("#student_name").val().trim();
    let student_id = $("#student_id").val().trim();

    let row = `
        <tr>
            <td>${student_name}</td>
            <td>${student_id}</td>
            <td>
                <button class="btn btn-danger btn-sm rounded-pill" onclick="delete_student(this)">删除</button>
            </td>
        </tr>
    `;

    $("#selfstudy-absent-list-table-body").append(row);
}

function add_askForLeave_student() {
    let student_name = $("#student_name_askForLeave").val().trim();
    let student_id = $("#student_id_askForLeave").val().trim();
    let reason = $("#reason").val().trim();

    let row = `
        <tr>
            <td>${student_name}</td>
            <td>${student_id}</td>
            <td>${reason}</td>
            <td>
                <button class="btn btn-danger btn-sm rounded-pill" onclick="delete_student(this)">删除</button>
            </td>
        </tr>
    `;

    $("#selfstudy-askForLeave-list-table-body").append(row);
}

function submit() {
    try {
        let selfstudy_id = parseInt($("#modal-subtitle").attr("selfstudy_id").trim());
        if (selfstudy_id === 0) { throw "Selfstudy ID Illegal !"; }

        let firstPresent = parseInt($("#firstPresent").val().trim());
        let absent = parseInt($("#absent").val().trim());
        let secondPresent = parseInt($("#secondPresent").val().trim());
        let leaveEarly = parseInt($("#leaveEarly").val().trim());
        let askForLeave = parseInt($("#askForLeave").val().trim());
        let remark = $("#remark").val().trim();

        let absent_table_body = $("#selfstudy-absent-list-table-body");
        let absent_list = Array();
        for (row of absent_table_body.children()) {
            let student_name = $(row).children().first().text().trim();
            let student_id = $(row).children().first().next().text().trim();

            absent_list.push({ student_name: student_name, student_id: student_id });
        }

        let askForLeave_table_body = $("#selfstudy-askForLeave-list-table-body");
        let askForLeave_list = Array();
        for (row of askForLeave_table_body.children()) {
            let student_name = $(row).children().first().text().trim();
            let student_id = $(row).children().first().next().text().trim();
            let reason = $(row).children().first().next().next().text().trim();

            askForLeave_list.push({ student_name: student_name, student_id: student_id, reason: reason });
        }

        $.ajax({
            url: "/Ajax/Users/submit_selfstudy_record",
            method: "POST",
            data: JSON.stringify({
                selfstudy_id: selfstudy_id,
                record: {
                    firstPresent: firstPresent,
                    absent: absent,
                    secondPresent: secondPresent,
                    leaveEarly: leaveEarly,
                    askForLeave: askForLeave,
                    remark: remark,
                },
                absentList: absent_list,
                askForLeaveList: askForLeave_list
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
            title: "提供的数据错误，请检查或联系管理员。",
            icon: "error",
        });
    }
}