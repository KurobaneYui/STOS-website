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
    html = converter.makeHtml(---
	    title: 督导队网站-手册
	    ---

	    网站结构
========

-   /

    -   404.html

    -   ROOT_PATH.php

    -   assets/

    -   css/

    -   js/

    -   scss/

    -   ajax/

        -   user/

        -   presentation/

        -   event/

    -   frame/

        -   html5/

        -   php/

    -   config/

        -   DataBase_CollectionData.conf

        -   DataBase_Information.conf

    -   program/

        -   python3/

    -   STOSFileBackup/

    -   tempFile/

    -   user/

        -   authentication/

        -   userCenter/

网页布局
========

php的设计和使用
===============

php接口工具
-----------

### 周起始日起计算函数：
#
#### 早自习数据查询函数：
#
#### 查课数据查询函数：
#
#### 人员岗位调动函数：
#
#### 权限管理器：
#
#php类的设计
#-----------
#
#### 数据库连接类：
#
##### 类名称：
#
#>   SQL_connector
#
##### 类属性：
#
#>   连接配置变量：config = array( '信息库'=\>array( ), “数据库”=\>array( ) )
#
#>   mysqli返回的连接状态：conn = mysqli-\>connect( \$config[“”] )
#
#>   连接游标：cursor = conn-\>cursor()
#
#>   上一次SQL语句执行状态（连接初始化状态）：status = ( ‘success’ / ‘failed’ )
#
#>   上一次SQL语句执行结果：message = conn-\>query( \$sql )
#
##### 类方法：
#
#构造函数：__construct( “数据库”/“信息库” )
#
#1、利用连接配置变量建立数据库连接，连接保存在连接状态中
#
#2、连接建立成功则执行状态设为success，否则设为failed
#
#3、获取连接游标
#
#析构函数：__destruct( )
#
#1、关闭连接游标
#
#2、关闭连接状态
#
#返回连接状态变量：get_conn( )
#
#如果连接不存在，则返回false
#
#执行SQL语句：SQL_execute( \$sql )
#
#1、直接将输入语句执行并提交，
#
#2、连接建立成功则执行状态设为succes，否则设为failed
#
#3、如果语句有返回结果则结果保存在执行结果中
#
#查询数据库：search( \$table_name, \$keys = array(“\*”), \$condi_pos = array(0),
#\$condi_neg = array(1) )
#
#1、拼接keys变量中的值，拼接为搜索关键字
#
#2、将两个条件变量的键值对转换为“key=/!=valve”字符串数组，再拼接为查询语句的条件部分
#
#3、组合SQL语句存于函数内的\$sql变量内
#
#4、执行语句
#
#5、执行成功则执行状态设为success，否则设为failed
#
#6、成功则返回结果存于执行结果中
#
#插入数据库：insert( \$table_name, \$keys_values )
#
#1、将keys_values的键拼接为插入字段
#
#2、将keys_values的值拼接为插入值
#
#3、组合SQL语句存于函数内的\$sql变量内
#
#4、执行语句
#
#5、执行成功则执行状态设为success，否则设为failed
#
#更新数据库：update( \$table_name, \$keys_values, \$condi_pos = array(0),
#\$condi_neg = array(1) )
#
#1、将keys_values的键值对转换为“key=/!=valve”字符串数组，再拼接为更新语部分
#
#2、将两个条件变量的键值对转换为“key=/!=valve”字符串数组，再拼接为查询语句的条件部分
#
#3、组合SQL语句存于函数内的\$sql变量内
#
#4、执行语句
#
#5、执行成功则执行状态设为success，否则设为failed
#
#### 个人数据类
#
##### 类名称：
#
#Person_info_container
#
##### 类属性：
#
#数据验证：
#
#存在验证：
#
#姓名：
#
#学号：
#
#学院：
#
#性别：
#
#民族：
#
#电话：
#
#QQ：
#
#寝室_苑：
#
#寝室_楼：
#
#寝室_号：
#
#银行卡号：
#
#密码：
#
#周一空课：
#
#周二空课：
#
#周三空课：
#
#周四空课：
#
#周五空课：
#
#所属组：
#
#岗位：
#
#工资：
#
#备注：
#
#权限：
#
##### 类方法：
#
#设定个人信息：
#
#设定岗位信息：
#
#设定密码：
#
#设定权限：
#
#获取个人信息：
#
#获取岗位信息：
#
#获取权限：
#
#验证数据：
#
#检测存在性：
#
#### 早自习单日数据：
#
##### 类名称：
#
##### 类属性：
#
##### 类方法：
#
#### 单人早自习数据：
#
##### 类名称：
#
##### 类属性：
#
##### 类方法：
#
#### 查课单课程数据：
#
##### 类名称：
#
##### 类属性：
#
##### 类方法：
#
#### 单人查课数据：
#
##### 类名称：
#
##### 类属性：
#
##### 类方法：
#
#Python类的设计
#--------------
#
#### 早自习教室数据导入：
#
#### 早自习排班导入：
#
#### 早自习数据导出：
#
#### 全部查课课程导入：
#
#### 查课排班导入：
#
#### 查课数据导出：
#
#JavaScript的设计和使用
#======================
#
#Python函数的设计和使用
#======================
#
#MySQL数据库的设计和使用
#=======================
#
#表结构
#------
#
#部分表设有id自增字段。有些表利用此字段作为主键，区分不同记录；有些表的id自增字段仅作为唯一字段，用于前端变更数据时定位的便利性。从使用上看，两者没有区别，同等的帮助改进php操作时的便利性。
#
#### 与网站相关
#
##### 网站通知信息
#
#### 与成员相关的表
#
##### 成员基本信息
#
#每个成员一条记录，没有时效性，离队成员可随时删除
#
#| 主键/外键/唯一值 | 字段           | 类型        | 可空 | 备注                                     |
#|------------------|----------------|-------------|------|------------------------------------------|
#| 主               | 学号           | varchar(50) | 否   |                                          |
#|                  | 姓名           | varchar(50) | 否   |                                          |
#|                  | 性别           | varchar(50) | 否   | “男”or“女”                               |
#|                  | 民族           | varchar(50) | 否   | 如：“汉族”                               |
#|                  | 籍贯           | varchar(50) | 否   |                                          |
#|                  | 电话           | varchar(50) | 否   |                                          |
#|                  | QQ             | varchar(50) | 否   |                                          |
#|                  | 校区           | varchar(50) | 否   | “清水河”or“沙河”                         |
#|                  | 学院           | varchar(50) | 否   | 请填全称                                 |
#|                  | 寝室_苑        | varchar(50) | 否   | 清水河填“学知苑”等；沙河填“校内”or“校外” |
#|                  | 寝室_栋        | varchar(50) | 否   |                                          |
#|                  | 寝室_号        | varchar(50) | 否   |                                          |
#|                  | 工资用姓名     | varchar(50) | 否   |                                          |
#|                  | 工资用学号     | varchar(50) | 否   |                                          |
#|                  | 工资用银行卡号 | varchar(50) | 否   |                                          |
#|                  | 建档立卡       | varchar(50) | 否   | 目前只填写“是”or“否”                     |
#|                  | SubmissionTime | datetime    | 否   | 更新时自动改变                           |
#
##### 成员工作信息
#
#每个成员一条记录，没有时效性，记录随成员信息的删除自动删除
#
#| 主键/外键/唯一值 | 字段           | 类型        | 可空 | 默认值 | 备注                                     |
#|------------------|----------------|-------------|------|--------|------------------------------------------|
#| 主/外            | 学号           | varchar(50) | 否   |        | 绑定成员基本信息表“学号”字段             |
#|                  | 周一空课       | varchar(50) | 否   | 0000   | 如：0132                                 |
#|                  | 周二空课       | varchar(50) | 否   | 0000   |                                          |
#|                  | 周三空课       | varchar(50) | 否   | 0000   |                                          |
#|                  | 周四空课       | varchar(50) | 否   | 0000   |                                          |
#|                  | 周五空课       | varchar(50) | 否   | 0000   |                                          |
#| 外               | 所属组         | varchar(50) | 是   | null   | 所属组绑定部门信息表                     |
#|                  | 岗位           | varchar(50) | 是   | null   | “组员”or“组长”or“队长”                   |
#|                  | 基础工资       | float       | 否   | 0      |                                          |
#|                  | 考评           | varchar(50) | 否   | 合格   | 对组长有“合格”or“警告”，对组员保持“合格” |
#|                  | 计分           | float       | 否   | 5      | 对组员有用，对组长保持5.0                |
#|                  | 备注           | json        | 否   |        | json内容可空                             |
#|                  | SubmissionTime | datetime    | 否   |        | 更新时自动改变                           |
#
##### 权限密码登陆信息
#
#每个成员一条记录，没有时效性，随成员信息的删除同步删除
#
#| 主键/外键/唯一值 | 字段           | 类型        | 可空 | 备注                                               |
#|------------------|----------------|-------------|------|----------------------------------------------------|
#| 主/外            | 学号           | varchar(50) | 否   | 绑定成员基本信息表“学号”字段                       |
#|                  | 密码           | longblob    | 否   | 使用数据库函数AES_ENCRYPT                          |
#|                  | 权限           | varchar(50) | 否   | 字符串意义参见php设计文档                          |
#|                  | 登录信息       | json        | 否   | 包含：浏览器、操作系统、访问IP、访问地址、访问时间 |
#|                  | SubmissionTime | datetime    | 否   | 更新时自动改变                                     |
#
##### 考评记录
#
#每条记录相互独立，有时间对应，超过4年的记录会被删除。每条记录均绑定某一个成员，该成员从成员信息中移除时，对应记录会被删除
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 默认值 | 备注                                     |
#|------------------|----------------|--------------------|------|--------|------------------------------------------|
#| 外               | 学号           | varchar(50)        | 否   |        | 绑定成员基本信息表“学号”字段             |
#|                  | 事件记录       | json               | 否   |        | json内容可空                             |
#|                  | 计分变动       | float              | 否   | 0      | 对组员有用，对组长保持0                  |
#|                  | 考评变动       | varchar(50)        | 否   | 合格   | 对组长有“合格”or“警告”，对组员保持“合格” |
#|                  | 日期           | date               | 否   |        |                                          |
#| 主               | id             | mediumint unsigned | 否   |        | 自增                                     |
#|                  | SubmissionTime | datetime           | 否   |        | 更新时自动改变                           |
#
##### 黑名单
#
#*每个学生对应一个记录，有时效性，超过4年的记录会被删除。可以修改内容，日期记录第一次添加该记录时的日期，SubmissionTime记录最近一次修改或添加的时间*
#
#| 主键/外键/唯一值 | 字段           | 类型        | 可空 | 备注           |
#|------------------|----------------|-------------|------|----------------|
#| 主               | 学号           | varchar(50) | 否   |                |
#|                  | 姓名           | varchar(50) | 否   |                |
#|                  | 原因           | json        | 否   | json内容可空   |
#|                  | 日期           | date        | 否   |                |
#|                  | SubmissionTime | datetime    | 否   | 更新时自动改变 |
#
#### 与部门相关的表
#
##### 部门信息
#
#每个部门一个记录，没有时效性。日期记录公费最近一次变动对应的日期，需软件维护。公费也请软件维护，考虑到账目记录会过期删除，故不在数据库中添加触发变更机制
#
#| 主键/外键/唯一值 | 字段           | 类型         | 可空 | 默认值 | 备注                   |
#|------------------|----------------|--------------|------|--------|------------------------|
#| 主               | 部门           | varchar(50)  | 否   |        |                        |
#|                  | 人数           | int unsigned | 否   | 0      |                        |
#|                  | 公费           | float        | 否   | 0      |                        |
#|                  | 日期           | date         | 否   |        | 最近一次公费变动的日期 |
#|                  | SubmissionTime | datetime     | 否   |        | 更新时自动改变         |
#
##### 部门账本
#
#部门账目绑定一个部门，名称变动时回做相应变化，部门取消时，会随部门信息对应部门删除而删除。每条记录有时间，超过4年会被删除。软件添加、修改或删除任何记录时，请同时变更部门信息中的公费
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 备注                     |
#|------------------|----------------|--------------------|------|--------------------------|
#| 外               | 部门           | varchar(50)        | 否   | 绑定部门信息表“部门”字段 |
#|                  | 事由           | json               | 否   | json内容可空             |
#|                  | 金额           | float              | 否   | 正：收入；负：支出       |
#|                  | 日期           | date               | 否   |                          |
#| 主               | id             | mediumint unsigned | 否   | 自增                     |
#|                  | SubmissionTime | datetime           | 否   | 更新时自动改变           |
#
#### 与工作任务相关的表
#
##### 教室信息
#
#每个教室一条记录，由于两校区教学楼名称不同，故没有将校区作为主键，如以后有需要可以增加主键。
#
#教室用途为json格式数据，键为用途，值为用途相关的信息；每个用途仍为键值对，键为信息名称，值为对应的信息。例如{'早自习':{
#'学院': '医学院', '应到人数': '98'}, '某用途':{ '第一个信息': '值',
#'第二个信息': '值'}}
#
#记录没有时效性，可随时变更
#
#| 主键/外键/唯一值 | 字段           | 类型         | 可空 | 默认值 | 备注                       |
#|------------------|----------------|--------------|------|--------|----------------------------|
#|                  | 校区           | varchar(50)  | 否   |        | “清水河”or“沙河”           |
#| 主               | 教学楼         | varchar(50)  | 否   |        | 如：“品学楼”“第二教学楼”等 |
#| 主               | 区号           | varchar(50)  | 否   |        | 沙河填“-”；清水河选填ABC   |
#| 主               | 教室编号       | varchar(50)  | 否   |        |                            |
#|                  | 容纳人数       | int unsigned | 否   | 0      |                            |
#|                  | 教室用途       | json         | 否   |        | json内容可为空             |
#|                  | SubmissionTime | datetime     | 否   |        | 更新时自动改变             |
#
##### 课程信息
#
#每学期的每节可查课程均有唯一的一条记录，记录通过以下特征保证课程的非重复：
#
#课程对应的学年学期、名称、周几上课、从第几周到第几周的跨度、什么时段上课、单双周情况、上课的教室
#
#每个课程在一学期内应该只有一个记录，每学期导入当前学期的课程信息，学期中可随时增删改。记录有时效性，超过4年的记录将会被删除
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 备注                                            |
#|------------------|----------------|--------------------|------|-------------------------------------------------|
#| 主               | 学年学期       | varchar(50)        | 否   | 如：“2018-2019-1”。1表示第一学期；2表示第二学期 |
#|                  | 校区           | varchar(50)        | 否   | “清水河”or“沙河”                                |
#| 主               | 课程名称       | varchar(50)        | 否   |                                                 |
#| 主               | 周             | tinyint unsigned   | 否   | 如：“1”。1表示周一，7表示周日，其他类推         |
#| 主               | 时段           | varchar(50)        | 否   | 如：“1-2”                                       |
#| 主               | 上课周         | varchar(50)        | 否   | 如：“1-17”                                      |
#| 主               | 单双           | varchar(50)        | 否   | “单”or“双”or“全”                                |
#| 主               | 教学楼         | varchar(50)        | 否   | 如：“品学楼”“第二教学楼”等                      |
#| 主               | 区号           | varchar(50)        | 否   | 沙河填“-”；清水河选填ABC                        |
#| 主               | 教室编号       | varchar(50)        | 否   |                                                 |
#|                  | 学院           | varchar(50)        | 否   | 全称，单一学院                                  |
#|                  | 年级           | varchar(50)        | 否   | 单一年级                                        |
#|                  | 应到人数       | int unsigned       | 否   |                                                 |
#| 唯               | id             | mediumint unsigned | 否   | 自增                                            |
#|                  | SubmissionTime | datetime           | 否   | 更新时自动改变                                  |
#
##### 辅导员基本信息
#
#每个学院每个年级的辅导员都有记录，考虑到重名等现象，我们包括了姓名、性别、管理年级、所在学院，四个特征作为区分，如果仍无法区分，可以考虑增加联系方式作为主键等方法。职位字段考虑到存在学院书记签到的情况，一同记录以备后用。记录具有时效性，超过4年的记录会被删除
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 备注                 |
#|------------------|----------------|--------------------|------|----------------------|
#| 主               | 姓名           | varchar(50)        | 否   |                      |
#| 主               | 性别           | varchar(50)        | 否   | “男”or“女”           |
#| 主               | 年级           | varchar(50)        | 否   | “2019”               |
#| 主               | 学院           | varchar(50)        | 否   | 全称                 |
#|                  | 职位           | varchar(50)        | 否   | “辅导员”or“非辅导员” |
#|                  | 联系方式       | varchar(50)        | 是   |                      |
#| 唯               | id             | mediumint unsigned | 否   | 自增                 |
#|                  | SubmissionTime | datetime           | 否   | 更新时自动改变       |
#
##### 辅导员签到数据
#
#辅导员签到数据和辅导员基本信息一样，有同样的主键，考虑到每天的签到，增加签到时间作为主键，以此确保一天一记录。备注可以选择“请假”“活动”“代签”或者不填。职位字段考虑到存在学院书记签到情况，一同记录以备后用。记录具有时效性，超过4年的记录会被删除
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 备注                                                           |
#|------------------|----------------|--------------------|------|----------------------------------------------------------------|
#| 主               | 姓名           | varchar(50)        | 否   |                                                                |
#| 主               | 性别           | varchar(50)        | 否   | “男”or“女”                                                     |
#| 主               | 年级           | varchar(50)        | 否   | “2019”                                                         |
#| 主               | 学院           | varchar(50)        | 否   | 全称                                                           |
#|                  | 职位           | varchar(50)        | 否   | “辅导员”or“非辅导员”                                           |
#| 主               | 时间           | datetime           | 否   |                                                                |
#|                  | 备注           | varchar(50)        | 是   | “请假”or“代签”or“活动”，学院多样化早自习时可以选“活动”比如跑操 |
#| 唯               | id             | mediumint unsigned | 否   | 自增                                                           |
#|                  | SubmissionTime | datetime           | 否   | 更新时自动改变                                                 |
#
##### 代查情况记录
#
#每一次申请均留存一条记录，每位申请者一天只能申请两种类型代查各一次（某一天的某个类型的工作，只允许完整的交给另一个人，不应存在拆成两个人代查的情况）。申请类型暂时分类“查早”“查课”两类，后期可根据需要添加新的内容。记录具有时效性，超过4年的记录会被删除
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 备注           |
#|------------------|----------------|--------------------|------|----------------|
#|                  | 申请者所属组   | varchar(50)        | 否   |                |
#|                  | 申请者姓名     | varchar(50)        | 否   |                |
#| 主               | 申请者学号     | varchar(50)        | 否   |                |
#|                  | 接受者所属组   | varchar(50)        | 是   |                |
#|                  | 接受者姓名     | varchar(50)        | 是   |                |
#|                  | 接受者学号     | varchar(50)        | 是   |                |
#| 主               | 申请类型       | varchar(50)        | 否   | “查早”or“查课” |
#| 主               | 代查日期       | varchar(50)        | 否   |                |
#| 唯               | id             | mediumint unsigned | 否   | 自增           |
#|                  | SubmissionTime | datetime           | 否   | 更新时自动改变 |
#
##### 查早排班
#
#查早排班和查课不同，排班为范围性排班，一次排班安排一段时间的工作。要求软件维护起始日期和结束日期不重叠。在不重叠的条件下，日期部分仅需要起始日期就可作为其中一个主键以维护唯一性。记录具有时效性，超过4年的记录会被删除
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 备注                                                     |
#|------------------|----------------|--------------------|------|----------------------------------------------------------|
#| 主               | 起始日期       | date               | 否   | 注意：起始日期到结束日期的区间不应重叠，录入时需程序判断 |
#|                  | 结束日期       | date               | 否   |                                                          |
#|                  | 校区           | varchar(50)        | 否   | “清水河”or“沙河”                                         |
#| 主               | 教学楼         | varchar(50)        | 否   | 如：“品学楼”“第二教学楼”等                               |
#| 主               | 区号           | varchar(50)        | 否   | 沙河填“-”；清水河选填ABC                                 |
#| 主               | 教室编号       | varchar(50)        | 否   |                                                          |
#|                  | 学院           | varchar(50)        | 否   | 全称，单一学院                                           |
#|                  | 应到人数       | int unsigned       | 否   |                                                          |
#|                  | 组员姓名       | varchar(50)        | 否   |                                                          |
#|                  | 组员学号       | varchar(50)        | 否   |                                                          |
#| 唯               | id             | mediumint unsigned | 否   | 自增                                                     |
#|                  | SubmissionTime | datetime           | 否   | 更新时自动改变                                           |
#
##### 查早数据
#
#每天每个教室一条记录，数据具有时效性，超过4年的记录会被删除
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 备注                       |
#|------------------|----------------|--------------------|------|----------------------------|
#| 主               | 日期           | date               | 否   |                            |
#|                  | 校区           | varchar(50)        | 否   | “清水河”or“沙河”           |
#| 主               | 教学楼         | varchar(50)        | 否   | 如：“品学楼”“第二教学楼”等 |
#| 主               | 区号           | varchar(50)        | 否   | 沙河填“-”；清水河选填ABC   |
#| 主               | 教室编号       | varchar(50)        | 否   |                            |
#|                  | 学院           | varchar(50)        | 否   | 全称，单一学院             |
#|                  | 应到人数       | int unsigned       | 否   |                            |
#|                  | 数据           | json               | 否   |                            |
#|                  | 组员姓名       | varchar(50)        | 否   |                            |
#|                  | 组员学号       | varchar(50)        | 否   |                            |
#| 唯               | id             | mediumint unsigned | 否   | 自增                       |
#|                  | SubmissionTime | datetime           | 否   | 更新时自动改变             |
#
##### 查课排班
#
#每天每个待查课程都应有组员对应。数据具有时效性，超过4年的记录会被删除
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 备注                       |
#|------------------|----------------|--------------------|------|----------------------------|
#| 主               | 日期           | date               | 否   |                            |
#|                  | 校区           | varchar(50)        | 否   | “清水河”or“沙河”           |
#|                  | 课程名称       | varchar(50)        | 否   |                            |
#| 主               | 时段           | varchar(50)        | 否   | 如：“1-2”                  |
#| 主               | 上课周         | varchar(50)        | 否   | 如：“1-17”or“1-8双”        |
#| 主               | 单双           | varchar(50)        | 否   | “单”or“双”or“全”           |
#| 主               | 教学楼         | varchar(50)        | 否   | 如：“品学楼”“第二教学楼”等 |
#| 主               | 区号           | varchar(50)        | 否   | 沙河填“-”；清水河选填ABC   |
#| 主               | 教室编号       | varchar(50)        | 否   |                            |
#|                  | 学院           | varchar(50)        | 否   | 全称，单一学院             |
#|                  | 年级           | varchar(50)        | 否   | 单一年级                   |
#|                  | 应到人数       | int unsigned       | 否   |                            |
#|                  | 组员姓名       | varchar(50)        | 是   |                            |
#|                  | 组员学号       | varchar(50)        | 是   |                            |
#| 唯               | id             | mediumint unsigned | 否   | 自增                       |
#|                  | SubmissionTime | datetime           | 否   | 更新时自动改变             |
#
##### 查课数据
#
#每天每个课程一条记录，数据具有时效性，超过4年的数据会被删除
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 备注                       |
#|------------------|----------------|--------------------|------|----------------------------|
#| 主               | 日期           | varchar(50)        | 否   | 如：“2018-2019上”          |
#|                  | 校区           | varchar(50)        | 否   | “清水河”or“沙河”           |
#|                  | 课程名称       | varchar(50)        | 否   |                            |
#| 主               | 时段           | varchar(50)        | 否   | 如：“1-2”                  |
#| 主               | 上课周         | varchar(50)        | 否   | 如：“1-17”or“1-8双”        |
#| 主               | 单双           | varchar(50)        | 否   | “单”or“双”or“全”           |
#| 主               | 教学楼         | varchar(50)        | 否   | 如：“品学楼”“第二教学楼”等 |
#| 主               | 区号           | varchar(50)        | 否   | 沙河填“-”；清水河选填ABC   |
#| 主               | 教室编号       | varchar(50)        | 否   |                            |
#|                  | 学院           | varchar(50)        | 否   | 全称，单一学院             |
#|                  | 年级           | varchar(50)        | 否   | 单一年级                   |
#|                  | 应到人数       | int unsigned       | 否   |                            |
#|                  | 数据           | json               | 否   |                            |
#|                  | 组员姓名       | varchar(50)        | 否   |                            |
#|                  | 组员学号       | varchar(50)        | 否   |                            |
#| 唯               | id             | mediumint unsigned | 否   | 自增                       |
#|                  | SubmissionTime | datetime           | 否   | 更新时自动改变             |
#
##### 早餐组排班
#
#每人每天一条记录，记录具有时效性，超过4年的记录会被删除
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 备注                        |
#|------------------|----------------|--------------------|------|-----------------------------|
#| 主               | 学号           | varchar(50)        | 否   |                             |
#|                  | 姓名           | varchar(50)        | 否   |                             |
#|                  | 位置           | varchar(50)        | 否   | “A主”or“A-C”or“B主”or“巡查” |
#| 主               | 日期           | date               | 否   |                             |
#| 唯               | id             | mediumint unsigned | 否   | 自增                        |
#|                  | SubmissionTime | datetime           | 否   | 更新时自动改变              |
#
##### 早餐组数据
#
#每个地点每天一条记录，记录具有时效性，超过4年的记录会被删除
#
#| 主键/外键/唯一值 | 字段           | 类型               | 可空 | 备注                        |
#|------------------|----------------|--------------------|------|-----------------------------|
#| 主               | 位置           | varchar(50)        | 否   | “A主”or“A-C”or“B主”or“巡查” |
#| 主               | 日期           | date               | 否   |        );
    var post = document.getElementById('markdown_test');
    post.innerHTML = html;
</script>
</body>
</html>
