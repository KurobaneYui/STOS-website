// please import this document when jQuery has been imported

// 全局变量用于统计表格数量
var fullMemberTableCounter = 0;

// 添加一个表格（内部功能，不建议直接调用）
function add_table(table_rows, table_cols, table_name, ID_J, ID) {
    // required
    if (table_rows < 0)
        throw new RangeError("table_rows in add_nav_tab <0");
    if (table_cols < 0)
        throw new RangeError("table_cols in add_nav_tab <0");

    // add table
    let table_head_tr = `<tr class="bg-light-info">` + `<th scope="col">#</th>`.repeat(table_cols) + `</tr>`;
    let table_body_trs = (`<tr><th scope="row">a</th>` + `<td></td>`.repeat(table_cols - 1) + `</tr>`).repeat(table_rows);
    let table_content =
        `<h4>${table_name}</h4>
        <div class="table-responsive">
            <table class="table table-striped" id=${ID+"-table"}>
                <thead>
                    ${table_head_tr}
                </thead>
                <tbody>
                    ${table_body_trs}
                </tbody>
            </table>
        </div>`;
    ID_J.after(table_content);
    return ID+"-table";
}

// 在网页中添加表格
function add_table_fullMember(table_rows, table_cols) {
    fullMemberTableCounter++;
    return add_table(table_rows, table_cols, $("#fullMember"), "fullMember");
}

/* ************************* */
//   不使用，就是拿来给你参考的   //
/* ************************* */
function getGroupMembersFunction() {
    $.post("/Ajax/Users/contact.php", {
            'requestFunction': 'getContact'
        },
        function handleData(data,status) {
            if(status==="success"){
                let tempdata=JSON.parse(data);
                let returnCode=tempdata['ReturnCode'];
                data=tempdata['Data'];
                if(returnCode==='400') {
                    alert("参数错误，请联系管理员");
                }
                else if(returnCode==='401') {
                    alert('无权查看全队成员信息，请联系管理员处理');
                }
                else if(returnCode==='404') {
                    alert('功能不存在，请联系管理员更正文件调用');
                }
                else if(returnCode==='417') {
                    alert('功能错误，请联系管理员处理');
                }
                else if (returnCode==='200' || returnCode==='301') {
                    //状态码301，提醒转移函数
                    if(returnCode==='301'){window.console.log('全队成员信息获取函数移至新位置');}
                    //状态码200，处理data
                    let tempdata = JSON.parse(data); //'行数'，'列数'，'表头'，'数据'
                    let row = Number(tempdata['行数']);
                    let col = Number(tempdata['列数']);
                    let tableId = add_table(row, col); //建表格
                    let table=document.getElementById(tableId);
                    let hed = tempdata['表头'];
                    for (let i = 0; i < col; i++) { //填写表头
                        table.children[0].children[0].children[i].innerText = hed[i];
                    }
                    //先提取队员数据
                    let teamData = tempdata['数据'];
                    //格式： {序号：1，姓名：xxx，性别：男，QQ：1111，电话：111，所属组：现场1组，岗位：组员}
                    for (let i = 0; i < row; i++) {
                        for (let j = 0; j < col; j++)
                            table.children[1].children[i].children[j].innerText = teamData[i][hed[j]]; //以hed[j]为键
                    }
                }
            }
            else{
                alert("请检查网络连接，或稍后再试");
            }
        })
}
/* ***************** */

// 删除页面中的表格
function freshAllTables() {
    while (fullMemberTableCounter > 0) {
        let a = $("#fullMember-" + fullMemberTableCounter.toString() + "-table");
        a.parent().prev().remove();
        a.remove()
        fullMemberTableCounter--;
    }
    completePage();
    return 0;
}

// 这个函数用于补全页面，一般会在页面刚加载完成时自动调用
// 使用Ajax将信息提交至：/Ajax/Users/changeMemberOrAuth.php
// 提交内容为(1)
// 'requestFunction': 'getFullMembers'
//
// 对于提交1而言，返回编码与之前一样
// 如果错误则alert弹窗提醒
// 如果操作正确，则调用add_table_fullMember函数
// 函数需要三个参数，第一个时表格行数，第二个是表格列数，第三个填入组名称
// 函数返回值为新添加的表格的id，可以用于id搜索
// 以上需要传入函数的信息均提供与返回的json格式串内
// 如果有多个组，则多次调用函数
function completePage() {
    return 0;
}

/* **********************************
这里给你两个函数，函数返回值是字符串
需要在某个位置放下拉菜单或按钮的时候，直接把返回的字符串放进去就行
下拉菜单这个函数输入一个参数，字符串，可以选"组员""组长""队长"三选一，会把对应的菜单设为selected
function LYS_Selector(work) {
    let b = `<select class="form-control  input-sm">
                <option value="group-member">组员</option>
                <option value="group-leader">组长</option>
                <option value="team-leader">队长</option>
            </select>`;
    b = $(b);
    if (work==="组员") {
        b.find("[value=group-member]").attr("selected",true);
    }
    if (work==="组长") {
        b.find("[value=group-leader]").attr("selected",true);
    }
    if (work==="队长") {
        b.find("[value=team-leader]").attr("selected",true);
    }
    return b[0];
}
function LYS_Confirm_Button() {
    let b = `<button onClick="changeAuth(this)" type="button" class="btn btn-info btn-rounded btn-sm">确认</button>`;
    return b;
}
   ********************************** */

// 这个函数用于完成修改成员权限的功能
// 队员列表的每行末尾有一个添加按钮，点击后触发此函数
// 函数利用传入的参数获取所在行的信息，包括：学号、所属组名称和下拉列表里的岗位
// 将信息用Ajax提交至：/Ajax/Users/changeMemberOrAuth.php
// 提交内容为
// 'requestFunction': 'changeAuth'
// 'personID': 'xxx'
// 'groupName': 'xxx'
// 'work': 'xxx'
// Ajax返回值部分和之前一样
// 不论返回错误代码还是操作成功，均弹窗提示（alert）
// 如果返回错误代码则alert后无任何操作，如果操作成功，则调用freshAllTables函数
function changeAuth(Button) {
    return 0;
}