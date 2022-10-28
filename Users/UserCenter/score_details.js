$(function () {
    get_score_details();
})

function get_score_details() {
    $.get(
        "/Ajax/Users/get_score_details",
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
                        title: "功能维护中，暂不允许获取扣分详情信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取扣分详情函数移至新位置'); }
                    //状态码200，处理data
                    add_score_details_table(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function add_score_details_table(data) {
    let table_container = $("#score-details-table-container");
    for (let department_id in data) {
        let card =
            `<div class="col-12 col-lg-6">
            <div class="card">
                <h5 class="card-header"></h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                            <th>日期</th>
                            <th>变动</th>
                            <th>缘由</th>
                            </tr>
                        </thead>
                        <tbody id="department-${department_id}">
                        </tbody>
                        <tfoot class="table-border-bottom-0">
                            <tr>
                            <th>日期</th>
                            <th>变动</th>
                            <th>缘由</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>`;
        table_container.append(card);
        fill_score_details_table(data[department_id], department_id);
    }
}

function fill_score_details_table(data, id) {
    let table_body = $(`#department-${id}`);
    table_body.parent().parent().prev().text(data[0]["department_name"])

    table_body.html("");

    for (let one_record of data) {
        table_body.append(`
        <tr>
            <td>${one_record['date']}</td>
            <td>${render_score(one_record['variant'])}</td>
            <td>${one_record['reason']}</td>
        </tr>
        `)
    }
}

function render_score(score) {
    if (score > 0) return `<span class='badge bg-label-primary me-1'>${score}</span>`;
    else if (score < 0) return `<span class='badge bg-label-danger me-1'>${score}</span>`;
    else return `<span class='badge bg-label-dark me-1'>${score}</span>`;
}