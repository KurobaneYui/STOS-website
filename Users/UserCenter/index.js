$(function(){
    // 提示框延时3s消失
    setTimeout("$('#alert-info').fadeOut(700)",3000);
    // 绘制排名的柱状条 - 第一个
    var c=document.getElementById("person-ingroup-No");
    var ctx=c.getContext("2d");
    ctx.fillStyle="#EEEEEE";
    ctx.fillRect(0,0,15,70);
    ctx.fillStyle="#87CEEB";
    ctx.fillRect(0,30,15,70);
    // 绘制排名的柱状条 - 第二个
    var c=document.getElementById("group-inteam-No");
    var ctx=c.getContext("2d");
    ctx.fillStyle="#EEEEEE";
    ctx.fillRect(0,0,12,70);
    ctx.fillStyle="#87CEEB";
    ctx.fillRect(0,30,12,70);
    // 绘制knob图
    $(".dial").knob({
        'min':-2.0,
        'max':13.0,
        'step':0.1,
        'angleOffset':-135,
        'angleArc':270,
        'lineCap':'round',
        'width':70,
        'height':70,
        'readOnly':true
    });
})