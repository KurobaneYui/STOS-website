// JavaScript Document
$(function(){
    getTopbarInfo();
});


function getTopbarInfo() {
    $.get('/Ajax/Users/topbarInfo',function(data,status){
        if(status==="success"){
            let returnCode=data['code'];
            if(returnCode===400) {
                swal({
                    title: "参数错误，请联系管理员",
                    icon: "warning",
                  });
            }
            else if(returnCode===401) {
                swal({
                    title: "权限错误",
                    text: "如果未登录，请先登录",
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
                if(returnCode===301){window.console.log('topbar信息获取函数移至新位置');}
                //状态码200，处理data
                let name = data["data"]['name'];
                $("#topbar-name").html(name);
            }
        }
        else {
            alert("请检查网络连接，或稍后再试");
        }
    });
}


function logout() {
    $.get("/Ajax/Users/logout",function(data,status) {
        if (status === "success") {
            if (data["data"] === "finished") {
                window.location.href = "/Users/Authentication/logout.html";
            }
        }
    })
}