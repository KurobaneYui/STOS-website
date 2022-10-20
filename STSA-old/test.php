<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>无标题文档</title>

<script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.9.0/showdown.min.js"></script>
</head>
<style>
    blockquote {
        border-left: #eee solid 5px;
        padding-left: 20px;
    }
</style>
<body>
    <?php
    require(__DIR__.'/ROOT_PATH.php');
    require(ROOT_PATH . '/frame/php/Database_connector.php');
    echo '<br/><br/>';
    require(ROOT_PATH . '/frame/php/DateTools.php');
    $r = new DateTools();
    var_dump($r->next_week());
    echo '<br/><br/>';
//    require(ROOT_PATH . '/frame/php/Person.php');
//    $t = new Person('2016020903001');
//    var_dump($t->work_info());
    echo '<br/><br/>';
    $con = new Database_connector(ROOT_PATH.'/config/DataBase_CollectionData.conf');
    $t = $con->search('查早数据',array('日期'),array('日期'=>'2019-10-22'))->fetch_assoc();
    var_dump($t['日期']);
    echo '<br/><br/>';
    $conn = new Database_connector(ROOT_PATH.'/config/DataBase_Information.conf');
    $t = $conn->query("SELECT * FROM 成员信息 join 成员岗位  where 所属组 is not null;");
    echo "<table>";
    $r=$t->fetch_assoc();
    echo "<tr>";
    foreach($r as $key =>$value)
    {
	    echo "<th>{$key}</th>";
    }
    echo "</tr>";
    echo "<tr>";
    foreach($r as $key=>$value)
    {
	    echo "<td>{$value}</td>";
    }
    echo "</tr>";
    while($r=$t->fetch_assoc())
    {
	    echo "<tr>";
	    foreach($r as $key=>$value)
	    {	    
		    echo "<td>{$value}</td>";		    
	    }
	    echo "</tr>";
    }
    echo "</table>";
    echo '<br/><br/>';
//    $conn = new Database_connector(ROOT_PATH.'/config/DataBase_Information.conf');
//    $t = $conn->query("SELECT * FROM 登陆信息 where 学号 IN (SELECT 学号 FROM 成员信息 where 姓名='曾百惠');");
//    var_dump($t->fetch_assoc());
    phpinfo();
    echo '<br/><br/>';
    ?>
    <div id="markdown_test"></div>
<script>
    var converter = new showdown.Converter();
    var html = converter.makeHtml('# 督导队网站更新总览\n' +
        '> ## 2019-10-30\n' +
        '> * 加入权限模块和查询模块\n' +
        '> * 重构了数据收集相关类的PHP代码\n' +
        '> * 权限模块整合进个人信息类和数据收集类的PHP代码中\n' +
        '\n' +
        '## 2019-10-7：\n' +
        '* 重构的数据库链接的PHP代码\n' +
        '* 重构了个人信息相关类的PHP代码\n' +
        '* 重构了日期时间工具类的PHP代码\n' +
        '## 2019-6-5：网站v1.5完成\n' +
        '* 增加了组员空课表的修改\n' +
        '* Python部分增加了组员空课表的导出\n' +
        '## 2019-6-1：\n' +
        '* 修复了代查系统录入混乱的问题\n' +
        '* 代查系统暂时仅支持每人每天每种类型（早自习、查课）代查最多帮忙一次\n' +
        '## 2019-5-29：网站v1.4完成\n' +
        '* 加入了代查系统，功能较为单一\n' +
        '* 组长可以申请代查、接受代查\n' +
        '* 组员可以查看自己申请或接受的代查，或查看未有接受者的代查\n' +
        '* 组员有页面填写代查数据\n' +
        '## 2019-5-25：网站v1.3完成\n' +
        '* 组长的组员信息展示页更改为成员信息展示，可以查看其他骨干数据和自己组组员数据\n' +
        '## 2019-5-24：\n' +
        '* 修复了展示组员早自习数据错位的问题\n' +
        '* 相应的修改了提示哪些组员没录入数据的JS判断条件\n' +
        '* 增加了组长获取组员查课数据的展示页\n' +
        '* 增加了组长获取上一周组员数据的展示页\n' +
        '* 调整了侧边栏样式，加入水平线让侧边栏视觉效果更好\n' +
        '* 增加了队长/副队的相关页面，可以查看所有组员信息，以及当前周的所有组员的录入数据\n' +
        '## 2019-5-18：\n' +
        '* 更新了录入数据的显示效果，从弹出框变动为标签页\n' +
        '* 更新了数据展示的显示效果，从弹出框变动为标签页\n' +
        '* 更新组员数据展示的显示效果，从弹出框变动为标签页\n' +
        '## 2019-5-10：\n' +
        '* 解决了组员和组长看到不同早自习教室应到人数的问题\n' +
        '* 解决了查课表中提交数据后，时段和上课周不同但同名的两个教室，数据同时被改变的问题\n' +
        '## 2019-5-6：\n' +
        '* 优化组长查看组员早自习数据的查询方式，查询速度从5-10秒，提升为1秒内完成\n' +
        '## 2019-5-5：\n' +
        '* 开放查课数据录入\n' +
        '## 2019-4-30：\n' +
        '* 缺勤人员名单表单可以删除中间某一行\n' +
        '## 2019-4-28：\n' +
        '* 组长的个人中心主页展示两周内所有组员的查早排班\n' +
        '## 2019-4-24：网站v1.2完成\n' +
        '* 调整了组员管理系统的展示效果，组员数据一人一张“卡片”\n' +
        '* 数据录入系统加入了记名表录入，后期仍需优化代码结构\n' +
        '* 取消了数据展示表格内的“教室”“学院”两列，内容调整至标题\n' +
        '* 数据录入系统v1.2完成\n' +
        '## 2019-4-21：网站v1.1完成\n' +
        '* 个人中心改版,使用对移动端更友好的界面\n' +
        '* 数据录入系统加入默认数据功能，已提交过数据的表单会自动根据提交数据填写，只用改变待修改的数据即可\n' +
        '* 数据录入系统v1.1完成\n' +
        '* 组员管理系统v1.1完成\n' +
        '* 个人系统v1.1完成\n' +
        '* 个人中心v1.1完成\n' +
        '## 2019-4-16：数据录入系统完善、登录页、网站主页完善\n' +
        '* 早自习数据录入增加了请假人数的填写\n' +
        '* 备注栏支持多行\n' +
        '* 密码改为长期保存（策略为：每次登录刷新保存时间为两周）\n' +
        '* 网站主页去除占位符、加入文字介绍等\n' +
        '* 数据录入系统v0.2完成\n' +
        '## 2019-4-8：组员管理系统更新\n' +
        '* 组长可以重置组员密码为组员学号\n' +
        '* 组员管理系统v0.2完成\n' +
        '## 2019-4-7：个人系统更新\n' +
        '* 组员可以在个人信息页变更密码\n' +
        '* 个人系统v0.2完成\n' +
        '## 2019-3-31：组员管理系统更新\n' +
        '* 组长可以查看组员的个人信息\n' +
        '* 系统可以展示现场组组员的查早教室，数据提交功能不完善\n' +
        '* 组员管理系统v0.1完成\n' +
        '## 2019-3-29：允许注册\n' +
        '* 注册系统数据验证策略变更：寝室号允许范围扩大\n' +
        '* 注册系统v0.2完成\n' +
        '* 个人系统v0.1完成\n' +
        '## 2019-3-25：网站v0.1初步建成\n' +
        '* 网站主页内容未设计\n' +
        '* 数据录入v0.1完成\n' +
        '* 不支持查课数据、代查数据录入\n' +
        '* 注册系统v0.1完成\n');
    html = converter.makeHtml("");
    var post = document.getElementById('markdown_test');
    post.innerHTML = html;
</script>
</body>
</html>
