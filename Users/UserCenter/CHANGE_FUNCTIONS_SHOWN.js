$(function(){
    $.get('/Ajax/Users/topbarInfo.php',function(data,status){
        let a = JSON.parse(data);
        if(status==="success"){
            // 获取所有含有functionID的元素
            // 遍历每个元素
            // ## 拆开元素的functionID为独立数字
            // ## 遍历每个独立数字
            // ## ## 数字所在的解包数据是否为true，是则保留（单独设立一个变量，初始化为false，需要保留则改为true）
            // ## 判断保留变量是否为true，是则保留元素，否则删除元素
        }
        else {
            alert("请检查网络连接，或稍后再试");
        }
    });
});