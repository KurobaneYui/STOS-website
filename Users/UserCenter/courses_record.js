$(function () {
    get_records();
    Date.prototype.format = function (formatStr) {
        var str = formatStr;
        var Week = ['日', '一', '二', '三', '四', '五', '六'];
        str = str.replace(/yyyy|YYYY/, this.getFullYear());
        str = str.replace(/MM/, (this.getMonth() + 1) > 9 ? (this.getMonth() + 1).toString() : '0' + (this.getMonth() + 1));
        str = str.replace(/dd|DD/, this.getDate() > 9 ? this.getDate().toString() : '0' + this.getDate());
        return str;
    }
    let currentDate = (new Date()).format("yyyy-MM-dd");
})

function get_records() {
    $.get(
        "/Ajax/Users/get_courses_check_data",
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
                    fill_page(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function fill_page(data) {
    $("#courses-card-container").html("");

    for (oneDatePeriod in data) {
        add_card($("#courses-card-container"), oneDatePeriod, data[oneDatePeriod]);
    }
}

function add_card(card_container, oneDatePeriod, data) {
    Date.prototype.format = function (formatStr) {
        var str = formatStr;
        var Week = ['日', '一', '二', '三', '四', '五', '六'];
        str = str.replace(/yyyy|YYYY/, this.getFullYear());
        str = str.replace(/MM/, (this.getMonth() + 1) > 9 ? (this.getMonth() + 1).toString() : '0' + (this.getMonth() + 1));
        str = str.replace(/dd|DD/, this.getDate() > 9 ? this.getDate().toString() : '0' + this.getDate());
        return str;
    }
    let currentDate = (new Date()).format("yyyy-MM-dd");
    let expanded = 'false';
    let show = '';
    if (currentDate === data[0]['date']) {
        expanded = 'true';
        show = 'show';
    }
    let card = `
        <div class="col-12">
            <div class="card">
                <h5 class="card-header pb-2" data-bs-toggle="collapse"
                    href="#collapseExample${oneDatePeriod}" role="button" aria-expanded="${expanded}"
                    aria-controls="collapseExample${oneDatePeriod}">${oneDatePeriod}</h5>
                <div class="card-body">
                    <div class="row collapse ${show}" id="collapseExample${oneDatePeriod}">
                        <div class="col-12 table-responsive text-nowrap">
                            <table class="table table-hover text-center">
                                <thead>
                                    <tr>
                                        <th>表编号</th>
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
                                    </tr>
                                </thead>
                                <tbody id="courses-record-table-body-${oneDatePeriod}">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>表编号</th>
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
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    card_container.append(card);

    for (one_record of data) {
        add_row($(`#courses-record-table-body-${oneDatePeriod}`), one_record);
    }
}

function add_row(table_container, data) {
    let one_records = JSON.parse(data['record']);
    let row = `
        <tr data-bs-toggle="modal"
            data-bs-target="#update-courses-record"
            onclick="fill_data_into_modal(this)"
            course_id=${data["course_id"]}>
            <td>${data["course_order"]}</td>
            <td>${data["classroom_name"]}</td>
            <td>${data["course_name"]}</td>
            <td>${data["student_supposed"]}</td>
            <td>${one_records["firstPresent"] !== undefined && one_records["firstPresent"] !== null ? one_records["firstPresent"] : ""}</td>
            <td>${one_records["firstDisciplinary"] !== undefined && one_records["firstDisciplinary"] !== null ? one_records["firstDisciplinary"] : ""}</td>
            <td>${one_records["secondPresent"] !== undefined && one_records["secondPresent"] !== null ? one_records["secondPresent"] : ""}</td>
            <td>${one_records["secondDisciplinary"] !== undefined && one_records["secondDisciplinary"] !== null ? one_records["secondDisciplinary"] : ""}</td>
            <td>${one_records["remark"] || ""}</td>
            <td>${data["grade"]}</td>
            <td>${data["school_name"]}</td>
        </tr>
    `;

    table_container.append(row);
}

function fill_data_into_modal(row) {
    let course_id = $(row).attr("course_id").trim();
    let order = $($(row).children()[0]).text().trim();
    let oneDatePeriod = $(row).parent().parent().parent().parent().parent().prev().text().trim();
    let classroomName = $($(row).children()[1]).text().trim();
    let courseName = $($(row).children()[2]).text().trim();
    let firstPresent = $($(row).children()[4]).text().trim();
    let firstDisciplinary = $($(row).children()[5]).text().trim();
    let secondPresent = $($(row).children()[6]).text().trim();
    let secondDisciplinary = $($(row).children()[7]).text().trim();
    let remark = $($(row).children()[8]).text().trim();
    let grade = $($(row).children()[9]).text().trim();
    let schoolName = $($(row).children()[10]).text().trim();

    let modal_head = `${order} ${oneDatePeriod}<br/>${classroomName} ${grade} ${schoolName}<br/>${courseName}`; // 表编号、日期、时段；教室、年级、学院；课程
    $("#firstPresent").val(parseInt(firstPresent) || 0);
    $("#firstDisciplinary").val(parseInt(firstDisciplinary) || 0);
    $("#secondPresent").val(parseInt(secondPresent) || 0);
    $("#secondDisciplinary").val(parseInt(secondDisciplinary) || 0);
    $("#remark").val(remark);
    $("#modal-subtitle").html(modal_head);
    $("#modal-subtitle").attr("course_id", course_id || 0);
}

function submit() {
    try {
        let course_id = parseInt($("#modal-subtitle").attr("course_id").trim());
        if (course_id === 0) { throw "courses ID Illegal !"; }

        let firstPresent = parseInt($("#firstPresent").val().trim());
        let firstDisciplinary = parseInt($("#firstDisciplinary").val().trim());
        let secondPresent = parseInt($("#secondPresent").val().trim());
        let secondDisciplinary = parseInt($("#secondDisciplinary").val().trim());
        let remark = $("#remark").val().trim();

        $.ajax({
            url: "/Ajax/Users/submit_courses_record",
            method: "POST",
            data: JSON.stringify({
                course_id: course_id,
                record: {
                    firstPresent: firstPresent,
                    firstDisciplinary: firstDisciplinary,
                    secondPresent: secondPresent,
                    secondDisciplinary: secondDisciplinary,
                    remark: remark,
                },
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
                            title: "功能维护中，暂不允许提交查课记录信息",
                            icon: "warning",
                        });
                    }
                    else if (returnCode === 200 || returnCode === 301) {
                        //状态码301，提醒转移函数
                        if (returnCode === 301) { window.console.log('提交查课记录函数移至新位置'); }
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