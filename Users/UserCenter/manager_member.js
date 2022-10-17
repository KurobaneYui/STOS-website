function get_all_groups_members() {
    // ajax to get all member
    $.get(
        "/Ajax/GroupManager/get_all_groups_members",
        function(data,status){
            // handle ajax return

            // if success
            fill_page_tables(data['data']);
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
    for(let student of data) {
        let row =
        `<tr>
            <td>${student['rowNumber']}</td>
            <td>${student['name']}</td>
            <td>${render_gender(student['gender'])}</td>
            <td>${student['student_id']}</td>
            <td><buton class="btn btn-danger btn-sm rounded-pill" onclick="remove_member(this)">删除</buton></td>
        </tr>`;
    }

    $("#"+String(group_id)).append(row)
}


function render_gender(gender) {
    if (gender === '男') return "<span class='badge bg-label-info me-1'>男</span>";
    else if (gender === "女") return "<span class='badge bg-label-danger me-1'>女</span>";
    else return "<span class='badge bg-label-dark me-1'>-</span>";
}


function set_modal_header(element) {
    let group_name = $(element).parent().prev().text();
    let group_id = $(element).prev().children().first().next().prop("id");
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
            // handle ajax return

            // if success
            fill_search_results(data['data']);
        }
    )
}


function fill_search_results(data) {
    $("#search-table-body").html("");
    for(let student of data) {
        let row = 
        `<tr>
            <td>${student['rowNumber']}</td>
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

    // ajax to add member
}


function remove_member(element) {
    ;
}

$(function() {
    // get member
    // get_all_groups_members();
})