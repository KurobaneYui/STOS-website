function get_all_groups_members() {
    // ajax to get all member
    $.get(
        "/Ajax/GroupManager/get_all_groups_members",
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
                            text: "非组长、队长无管理权限。",
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
                        title: "功能维护中，暂不允许获取所有管理的组的组员",
                        icon: "warning",
                        });
                    }
                    else if (returnCode===200 || returnCode===301) {
                        //状态码301，提醒转移函数
                        if(returnCode===301){window.console.log('获取所有管理的组的组员函数移至新位置');}
                        //状态码200，处理data
                        fill_page_tables(data['data']);
                    }
            }
            else
                alert("请检查网络状况。");
        }
    )
}


function fill_page_tables(data) {
    $("#group-card-container").html("");

    for(let group_id in data) {
        add_table_template(group_id, data[group_id]['group_name']);
        fill_table_by_id(group_id, data[group_id]['members'])
    }
}


function add_table_template(group_id, group_name) {
    let table =
    `<div class="col-12 col-lg-6">
        <div class="card">
            <h5 class="card-header">${group_name}</h5>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover table-striped mb-3">
                    <thead>
                        <tr>
                        <th>#</th>
                        <th>姓名</th>
                        <th>性别</th>
                        <th>学号</th>
                        <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="${group_id}">
                    </tbody>
                </table>
                <button class="btn btn-sm btn-primary rounded-pill mb-3 ms-3"
                        data-bs-toggle="modal" data-bs-target="#add-member"
                        onclick="set_modal_header(this)">
                        添加</button>
            </div>
        </div>
    </div>`;

    $("#group-card-container").append(table);
}


function fill_table_by_id(group_id,data) {
    let rowNumber = 0;
    for(let student of data) {
        rowNumber++;
        let row =
        `<tr>
            <td>${rowNumber}</td>
            <td>${student['student_name']}</td>
            <td>${render_gender(student['gender'])}</td>
            <td>${student['student_id']}</td>
            <td><buton class="btn btn-danger btn-sm rounded-pill" onclick="remove_member(this)">删除</buton></td>
            <div hidden>${student['department_id']}</div>
        </tr>`;
        $("#"+String(group_id)).append(row)
    }
}


function render_gender(gender) {
    if (gender === '男') return "<span class='badge bg-label-info me-1'>男</span>";
    else if (gender === "女") return "<span class='badge bg-label-danger me-1'>女</span>";
    else return "<span class='badge bg-label-dark me-1'>-</span>";
}


function set_modal_header(element) {
    let group_name = $(element).parent().prev().text();
    let group_id = $(element).prev().children().first().next().attr("id");
    $("#group-name").text(group_name);
    $("#group-id").text(group_id);
}


function search_member() {
    let ids = $("#multi-id").val();
    // ajax to search
    $.post(
        "/Ajax/GroupManager/search_member",
        {student_ids:ids},
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
                            text: "非组长、队长无权搜索成员。",
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
                        title: "功能维护中，暂不允许搜索成员",
                        icon: "warning",
                        });
                    }
                    else if (returnCode===200 || returnCode===301) {
                        //状态码301，提醒转移函数
                        if(returnCode===301){window.console.log('搜索成员函数移至新位置');}
                        //状态码200，处理data
                        fill_search_results(data['data']);
                    }
            }
            else
                alert("请检查网络状况。");
        }
    )
}


function fill_search_results(data) {
    $("#search-table-body").html("");
    for(let student of data) {
        let row = 
        `<tr>
            <td>${student['name']}</td>
            <td>${student['gender']}</td>
            <td>${student['student_id']}</td>
            <td><buton class="btn btn-success btn-sm rounded-pill" onclick="add_member(this)">添加</buton></td>
        </tr>`;
        $("#search-table-body").append(row);
    }
}


function add_member(element) {
    let student_id = $(element).parent().prev().text();
    let group_id = $("#group-id").text();
    $(element).remove();

    $.post(
        "/Ajax/GroupManager/add_member",
        {student_id:student_id,group_id:group_id},
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
                            text: "非组长、队长无权添加成员。",
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
                        title: "功能维护中，暂不允许添加组员",
                        icon: "warning",
                        });
                    }
                    else if (returnCode===200 || returnCode===301) {
                        //状态码301，提醒转移函数
                        if(returnCode===301){window.console.log('添加组员函数移至新位置');}
                        //状态码200，处理data
                        showToast("success","成功","已添加组员");
                    }
            }
            else
                alert("请检查网络状况。");
        }
    )
}


function remove_member(element) {
    let student_id = $(element).parent().prev().text();
    let group_id = $(element).parent().parent().parent().prop("id");
    $(element).remove();

    $.post(
        "/Ajax/GroupManager/remove_member",
        {student_id:student_id,group_id:group_id},
        function(data,status){
            if(status === "success"){
                let returnCode=data['code'];
                    if(returnCode===400) {
                        showToast("error","失败",data['message']);
                    }
                    else if(returnCode===401) {
                        swal({
                            title: "权限错误",
                            text: "非组长、队长无权删除成员。",
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
                        title: "功能维护中，暂不允许删除组员",
                        icon: "warning",
                        });
                    }
                    else if (returnCode===200 || returnCode===301) {
                        //状态码301，提醒转移函数
                        if(returnCode===301){window.console.log('删除组员函数移至新位置');}
                        //状态码200，处理data
                        showToast("success","成功","已删除组员");
                    }
            }
            else
                alert("请检查网络状况。");
        }
    )
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

$(function() {
    // get member
    get_all_groups_members();
})