$(function () {
    get_school();
})

function get_school() {
    $.get(
        "/Ajax/DataManager/get_school",
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
                        title: "功能维护中，暂不允许获取学院信息",
                        icon: "warning",
                        });
                    }
                    else if (returnCode===200 || returnCode===301) {
                        //状态码301，提醒转移函数
                        if(returnCode===301){window.console.log('获取学院信息函数移至新位置');}
                        //状态码200，处理data
                        fill_school_table(data['data']);
                        editable_readonly_input();
                    }
            }
            else
                alert("请检查网络状况。");
        })
}

function fill_school_table(data) {
    let table_body = $("#school-table-body");

    table_body.html("");

    for (let one_school of data)
    {
        table_body.append(`
        <tr old-data='${one_school['school_id']}'>
            <td>
                <input type="number" min="1" max="50" class="form-control-plaintext editable-readonly-input text-center" style="min-width: 20px;" value="${one_school['school_id']}" readonly required/>
            </td>
            <td>
                <input type="text" class="form-control-plaintext editable-readonly-input text-center" style="min-width: 120px;" value="${one_school['name']}" readonly/>
            </td>
            <td>
                <input type="text" class="form-control-plaintext editable-readonly-input text-center" style="min-width: 150px;" value="${one_school['campus']}" readonly/>
            </td>
            <td>
                <button class="btn btn-danger btn-sm rounded-pill">删除</button>
            </td>
        </tr>
        `)
    }
}

function upload_school(element_row) {
    let selector = element_row.children().first();
    let school_id = eval(selector.children().first().val());
    selector = selector.next();
    let name = selector.children().first().val();
    selector = selector.next();
    let campus = selector.children().first().val();
    let old_school_id = eval(element_row.attr("old-data"));

    if (school_id === undefined || school_id < 1 || school_id > 50) {
        alert("请检编号应在1~50之间");
    }

    $.post(
        "/Ajax/DataManager/update_school",
        {"school_id":school_id,"name":name,"campus":campus,"old_school_id":old_school_id},
        function(data,status){
            if(status === "success"){
                get_school();
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
                        title: "功能维护中，暂不允许修改学院信息",
                        icon: "warning",
                        });
                    }
                    else if (returnCode===200 || returnCode===301) {
                        //状态码301，提醒转移函数
                        if(returnCode===301){window.console.log('修改学院信息函数移至新位置');}
                        //状态码200，处理data
                        showToast('success',"成功","数据已修改，如有问题可刷新重试。")
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
    $(element).removeClass('form-control');
    $(element).addClass('form-control-plaintext');
    upload_school($(element).parent().parent());
}