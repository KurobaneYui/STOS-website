// JavaScript Document
$(function(){
    $.post('/Ajax/Users/topbarInfo.php',{'requestFunction':'getTopbarInfo'},function(data,status){
        let a = JSON.parse(data);
        if(status==="success"){
            if(a['ReturnCode']==='400') {
                alert("未登录，请先登录。如无法登陆，请联系管理员");
                window.location.href="/Users/Authentication/login.html"
            }
            else if(a['ReturnCode']==='401') {
                alert('权限不足，请联系管理员处理');
            }
            else if(a['ReturnCode']==='404') {
                alert('功能不存在，请联系管理员更正文件调用');
            }
            else if(a['ReturnCode']==='417') {
                alert('功能错误，请联系管理员处理');
            }
            else if(a['ReturnCode']==='200' || a['ReturnCode']==='301') {
                if(a['ReturnCode']==='301'){window.console.log('头部栏的信息获取函数移至新位置');}
                a = JSON.parse(a.Data);
                var name = a["姓名"];
                var work = a["所属组与岗位"];
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