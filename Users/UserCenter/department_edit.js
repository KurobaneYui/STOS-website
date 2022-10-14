$(function () {
    get_department();
})

function get_department() {
    $.get(
        "/Ajax/TeamManager/get_department",
        function(data,status){
            if(status === "success"){
                let returnCode=data['code'];
                    if(returnCode===400) {
                        swal({
                            title: "提供的数据错误，请联系管理员",
                            icon: "error",
                        });
                    }
                    else if(returnCode===401) {
                        swal({
                            title: "权限错误",
                            text: "仅队长可查看和编辑。",
                            icon: "error",
                        });
                    }
                    else if(returnCode===404) {
                        swal({
                            title: "功能不存在，请联系管理员",
                            icon: "warning",
                        });
                    }
                    else if(returnCode===417) {
                        swal({
                            title: "功能错误，请联系管理员",
                            icon: "warning",
                        });
                    }
                    else if(returnCode===498) {
                        swal({
                            title: "数据库异常，请联系管理员",
                            icon: "warning",
                        });
                    }
                    else if(returnCode===499) {
                        swal({
                        title: "功能维护中，暂不允许登录",
                        icon: "warning",
                        });
                    }
                    else if (returnCode===200 || returnCode===301) {
                        //状态码301，提醒转移函数
                        if(returnCode===301){window.console.log('获取部门信息函数移至新位置');}
                        //状态码200，处理data
                        console.log(data['data']);
                        fill_department_table(data['data']);
                        editable_readonly_input();
                    }
            }
            else
                alert("请检查网络状况。");
        })
}

function fill_department_table(data) {
    let table_body = $("#department-table-body");

    table_body.html("");

    for (let one_department of data)
    {
        table_body.append(`
        <tr id='test'>
            <td>${one_department['department_id']}</td>
            <td>${one_department['department_name']}</td>
            <td>
                <input type="number" min="0" max="50" class="form-control-plaintext editable-readonly-input" value="${one_department['job_available']}" readonly required/>
            </td>
            <td>
                <input type="text" class="form-control-plaintext editable-readonly-input" value="${one_department['student_name']}（${one_department['student_id']}）" readonly/>
            </td>
            <td>
                <input type="text" class="form-control-plaintext editable-readonly-input" value="${one_department['remark']}" readonly/>
            </td>
            <td>
                <button class="btn btn-danger btn-sm rounded-pill">删除</button>
                </td>
        </tr>
        `)
    }
}

function upload_department(element_row) {
    let selector = element_row.children().first();
    let department_id = selector.text();
    selector = selector.next().next().children().first();
    let max_num = eval(selector.val());
    selector = selector.parent().next().children().first();
    let group_leader_id = selector.val();
    selector = selector.parent().next().children().first();
    let remark = selector.val();

    if (department_id === "" || max_num === "" || max_num < 0 || max_num > 50) {
        alert("请检编号、人数上限的信息，人数上限应在0~50之间");
    }
    let tmp = group_leader_id.indexOf("（");
    if (tmp != -1) {
        group_leader_id = group_leader_id.slice(tmp+1,-1);
    }

    $.post(
        "/Ajax/TeamManager/update_department",
        {"department_id":department_id,"max_num":max_num,"group_leader_id":group_leader_id,"remark":remark},
        function(data,status){
            if(status === "success"){
                let returnCode=data['code'];
                    if(returnCode===400) {
                        showToast('error',"提供的数据有误",data['message']);
                    }
                    else if(returnCode===401) {
                        showToast('error',"权限错误",data['message']);
                    }
                    else if(returnCode===404) {
                        swal({
                            title: "功能不存在，请联系管理员",
                            icon: "warning",
                        });
                    }
                    else if(returnCode===417) {
                        swal({
                            title: "功能错误，请联系管理员",
                            icon: "warning",
                        });
                    }
                    else if(returnCode===498) {
                        swal({
                            title: "数据库异常，请联系管理员",
                            icon: "warning",
                        });
                    }
                    else if(returnCode===499) {
                        swal({
                        title: "功能维护中，暂不允许登录",
                        icon: "warning",
                        });
                    }
                    else if (returnCode===200 || returnCode===301) {
                        //状态码301，提醒转移函数
                        if(returnCode===301){window.console.log('修改部门信息函数移至新位置');}
                        //状态码200，处理data
                        showToast('success',"成功","数据已修改，如有问题可刷新重试。")
                        get_department();
                    }
            }
            else
                alert("请检查网络状况。");
        })
}

function showToast(status,title,text) {
    let success =
    `<div class="bs-toast toast m-2 fade bg-success hide" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="1000">
        <div class="toast-header">
            <i class="bx bx-check me-2"></i>
            <div class="me-auto fw-semibold">${title}</div>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">${text}</div>
    </div>`
    let error =
    `<div class="bs-toast toast m-2 fade bg-danger hide" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
        <div class="toast-header">
            <i class="bx bx-x me-2"></i>
            <div class="me-auto fw-semibold">${title}</div>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">${text}</div>
    </div>`
    container = $("#toast-container");
    if (status==="success") {
        container.append(success);
        let a = new bootstrap.Toast(container.children().last());
        a.show();
    }
    else if (status==="error") {
        container.append(error);
        let a = new bootstrap.Toast(container.children().last());
        a.show();
    }
}

function change_content_editable(element) {
    $(element).prop("readonly",false);
    $(element).removeClass('form-control-plaintext')
    $(element).addClass('form-control')
}

function change_content_uneditable(element) {
    $(element).prop("readonly",true);
    $(element).removeClass('form-control')
    $(element).addClass('form-control-plaintext')
    upload_department($(element).parent().parent())
}