function get_work_list_container() {
    $.get("/Ajax/Users/get_login_works", function (data, status) {
        if (status === "success") {
            let returnCode = data['code'];
            if (returnCode === 400) {
                swal({
                    title: "参数错误，请联系管理员",
                    icon: "warning",
                });
            }
            else if (returnCode === 401) {
                swal({
                    title: "权限错误",
                    text: "如果未登录，请先登录",
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
                    title: "功能维护中，暂不允许可登录岗位获取",
                    icon: "warning",
                });
            }
            else if (returnCode === 200 || returnCode === 301) {
                //状态码301，提醒转移函数
                if (returnCode === 301) { window.console.log('可登录岗位获取函数移至新位置'); }
                //状态码200，处理data
                fill_work_list_container(data["data"]);
            }
        }
        else {
            alert("请检查网络连接，或稍后再试");
        }
    })
}

function fill_work_list_container(data) {
    $("#work-list-container").html("");
    for (let one_work of data) {
        let department_id = one_work['department_id'];
        let workName = one_work['name'];
        let workJob = '';
        let pillContent = '';
        if (department_id === 1) {
            workJob = one_work['job'] === 1 ? "队长" : "副队长";
        }
        else {
            workJob = one_work['job'] === 1 ? "组长" : "组员"
        }
        if (department_id === 0) {
            pillContent = workName;
        }
        else {
            pillContent = workName + " - " + workJob;
        }
        let button_pill = `
        <div class='col'>
            <button class="btn btn-outline-primary rounded-pill" onclick="loginAsSpecifiedWork(${department_id},${one_work['job']})">
                ${pillContent}
            </button>
        </div>`;
        $("#work-list-container").append(button_pill);
    }
}

function loginAsSpecifiedWork(department_id = 0, job = 0) { // 选择岗位后的流程
    $.post("/Ajax/Users/login_as_specified_work",
        {
            department_id: parseInt(department_id),
            job: parseInt(job)
        },
        function (data, status) {
            if (status === 'success') {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "参数错误，请联系管理员",
                        icon: "error",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "登录错误",
                        text: "请确保已经登录后重试。",
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
                        title: "功能维护中，暂不允许登录",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('登录函数移至新位置'); }
                    //状态码200，处理data
                    $(window).attr('location', data["data"]);
                }
            }
            else {
                alert('请检查浏览器网络连接，建议刷新后重试');
            }
        });
}