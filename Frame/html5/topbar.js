// JavaScript Document
$(function(){
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
                window.location.href="/Users/Authentication/login.html"
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
                let name = data["data"]["name"];
                let work = data["data"]["groupAndWork"];
                $("#HEAD_BAR_NAME").html(name+"&nbsp;<i class='fa fa-angle-down'></i>");
                var headBarInnerName = $("#HEAD_BAR_INNER_NAME");
                headBarInnerName.html(name);
                headBarInnerName.next().remove();
                for(var i=0;i<work.length;i++){
                    headBarInnerName.after(`<p class="text-muted" style="font-size: 1em">${work[i]['部门名称']}-${work[i]['岗位']}</p>`);
                }
            }
        }
        else {
            alert("请检查网络连接，或稍后再试");
        }
    });
    // 修复手机视角下，侧边栏菜单多次点击时图标错误
    $("#repair-icon").parent().parent().click(function (){
        setTimeout(function(){
            let a = $("#repair-icon").prop("class").split(' ');
            if(a[1]==="ti-menu") {
                $("#repair-icon").prop("class",a[0]+' '+a[2]);
            }
        },25);
    })
});