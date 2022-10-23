$(function () {
    $.get(
        "/Ajax/TeamManager/get_blacklist",
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
                            text: "预备队员无通讯录查看权限。",
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
                        title: "功能维护中，暂不允许获取通讯录",
                        icon: "warning",
                        });
                    }
                    else if (returnCode===200 || returnCode===301) {
                        //状态码301，提醒转移函数
                        if(returnCode===301){window.console.log('获取通讯录函数移至新位置');}
                        //状态码200，处理data
                        fill_blacklist(data['data']);
                    }
            }
            else
                alert("请检查网络状况。");
        })
})

function fill_blacklist(data) {
    let table_body = $("#blacklist-table-body");

    for (let one_blacklist of data)
    {
        table_body.append(`
        <tr>
            <td>${one_blacklist['rowNum']}</td>
            <td>${one_blacklist['name']}</td>
            <td>${render_gender(one_blacklist['gender'])}</td>
            <td>${one_blacklist['student_id']}</td>
            <td>${one_blacklist['reason']}</td>
        </tr>
        `)
    }
}

function render_gender(gender) {
    if (gender === '男') return "<span class='badge bg-label-info me-1'>男</span>";
    else if (gender === "女") return "<span class='badge bg-label-danger me-1'>女</span>";
    else return "<span class='badge bg-label-dark me-1'>-</span>";
}