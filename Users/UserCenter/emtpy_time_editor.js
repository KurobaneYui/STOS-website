var allMemberEmptyTable = {};

$(function () {
    get_empty_table();
})

function change_point(cell) {
    let pointColor = $(cell).children().first().attr('src-data');
    let student_id = $(cell).parent().parent().parent().parent().parent().parent().parent().children().first().attr("student_id");
    let weekName = $(cell).attr('weekName');
    let timePeriodOrder = $(cell).parent().attr('timePeriodOrder');
    let evenOrNot = $(cell).parent().parent().attr("id").includes("even");
    let emptyOrNot = (pointColor === "red_point");
    
    $.post(
        "/Ajax/GroupManager/set_member_empty_table",
        {
            student_id: student_id,
            weekName: weekName,
            timePeriodOrder: timePeriodOrder,
            evenOrNot: evenOrNot,
            emptyOrNot: emptyOrNot
        },
        function (data, status) {
            if (status === "success") {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "提供的数据错误，请联系管理员",
                        text: data['message'],
                        icon: "error",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误",
                        text: "仅现场组组长可修改空课表。",
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
                        title: "功能维护中，暂不允许修改空课表",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('修改空课表函数移至新位置'); }
                    //状态码200，处理data
                    if (emptyOrNot) {
                        $(cell).children().first().attr('src-data', "green_point");
                        replace_point();
                    } else {
                        $(cell).children().first().attr('src-data', "red_point");
                        replace_point();
                    }
                }
            }
            else
                alert("请检查网络状况。");
        }
    )
}

function get_empty_table() {
    $.get(
        "/Ajax/GroupManager/get_group_empty_table",
        function (data, status) {
            if (status === "success") {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "提供的数据错误，请联系管理员",
                        text: data['message'],
                        icon: "error",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误",
                        text: "仅现场组组长可查看组内空课表。",
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
                        title: "功能维护中，暂不允许查看组内空课表",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('查看组内空课表函数移至新位置'); }
                    //状态码200，处理data
                    $("#card-container").html("");
                    for (one_student of data["data"]) {
                        add_card($("#card-container"), one_student);
                    }
                    replace_point();
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function add_card(container, data) {
    let card = `
        <div class="col-12 col-md-6 col-xxl-4">
            <div class="card">
                <h5 class="card-header" student_id=${data["student_id"]}>${data["student_name"]}</h5>
                <ul class="nav nav-pills ms-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active btn-sm" type="button" id="odd-week-tab-${data["student_id"]}"
                            data-bs-toggle="tab" data-bs-target="#odd-week-${data["student_id"]}" role="tab"
                            aria-controls="odd-week-${data["student_id"]}" aria-selected="true">单周</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link btn-sm" type="button" id="even-week-tab-${data["student_id"]}"
                            data-bs-toggle="tab" data-bs-target="#even-week-${data["student_id"]}" type="button"
                            role="tab" aria-controls="even-week-${data["student_id"]}" aria-selected="false">双周</button>
                    </li>
                </ul>
                <div class="tab-content p-1" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="odd-week-${data["student_id"]}" role="tabpanel"
                        aria-labelledby="odd-week-tab-${data["student_id"]}">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-striped table-hover text-center">
                                <thead>
                                    <tr>
                                        <th>时段</th>
                                        <th>周一</th>
                                        <th>周二</th>
                                        <th>周三</th>
                                        <th>周四</th>
                                        <th>周五</th>
                                    </tr>
                                </thead>
                                <tbody id="odd-table-body-${data["student_id"]}">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>时段</th>
                                        <th>周一</th>
                                        <th>周二</th>
                                        <th>周三</th>
                                        <th>周四</th>
                                        <th>周五</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="even-week-${data["student_id"]}" role="tabpanel"
                        aria-labelledby="even-week-tab-${data["student_id"]}">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-striped table-hover text-center">
                                <thead>
                                    <tr>
                                        <th>时段</th>
                                        <th>周一</th>
                                        <th>周二</th>
                                        <th>周三</th>
                                        <th>周四</th>
                                        <th>周五</th>
                                    </tr>
                                </thead>
                                <tbody id="even-table-body-${data["student_id"]}">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>时段</th>
                                        <th>周一</th>
                                        <th>周二</th>
                                        <th>周三</th>
                                        <th>周四</th>
                                        <th>周五</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.append(card);
    add_table($(`#odd-table-body-${data["student_id"]}`), $(`#even-table-body-${data["student_id"]}`), data);
}

function add_table(odd_table_container, even_table_container, data) {
    let weekName = ["mon", "tue", "wed", "thu", "fri"];
    let timePeriodName = ["1-2", "3-4", "5-6", "7-8"];

    for (order in timePeriodName) {
        let odd_cells = "";
        let even_cells = "";

        for (val of weekName) {
            if (data[val][order] === "1" || data[val][order] === "3") {
                odd_cells += `<td onclick="change_point(this)" weekName=${val}><img height="15" width="15" src-data="green_point"></td>`;
            } else if (data[val][order] === "0" || data[val][order] === "2") {
                odd_cells += `<td onclick="change_point(this)" weekName=${val}><img height="15" width="15" src-data="red_point"></td>`;
            }

            if (data[val][order] === "2" || data[val][order] === "3") {
                even_cells += `<td onclick="change_point(this)" weekName=${val}><img height="15" width="15" src-data="green_point"></td>`;
            } else if (data[val][order] === "0" || data[val][order] === "1") {
                even_cells += `<td onclick="change_point(this)" weekName=${val}><img height="15" width="15" src-data="red_point"></td>`;
            }
        }

        let odd_row = `
            <tr timePeriodOrder=${order}>
                <td>${timePeriodName[order]}</td>
                ${odd_cells}
            </tr>
        `;
        let even_row = `
        <tr timePeriodOrder=${order}>
            <td>${timePeriodName[order]}</td>
            ${even_cells}
        </tr>
        `;

        odd_table_container.append(odd_row);
        even_table_container.append(even_row);
    }
}