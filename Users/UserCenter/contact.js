// please import this document when jQuery has been imported

function add_table(table_rows, table_cols) {
    // required
    if (table_rows < 0)
        throw new RangeError("table_rows in add_nav_tab <0");
    if (table_cols < 0)
        throw new RangeError("table_cols in add_nav_tab <0");

    // add table
    let table_head_tr = `<tr class="table-info">` + `<th scope="col">#</th>`.repeat(table_cols) + `</tr>`;
    let table_body_trs = (`<tr><th scope="row">a</th>` + `<td></td>`.repeat(table_cols - 1) + `</tr>`).repeat(table_rows);
    let table_content =
        `<div class="table-responsive">
            <table class="table table-striped" id="team-member-info-table">
                <thead>
                    ${table_head_tr}
                </thead>
                <tbody>
                    ${table_body_trs}
                </tbody>
            </table>
        </div>`;
    $("#team-member-info div").append(table_content);
    return `team-member-info-table`;
}

function contactFunction() {
    $.post("/Ajax/Users/contact.php", {
            'requestFunction': 'getContact'
        },
        function handleData(data,status) {
            if(status==="success"){
                let tempdata=JSON.parse(data);
                let returnCode=tempdata['ReturnCode'];
                let data=tempdata['Data'];
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
                    let tempdata = JSON.parse(data); //'行数'，‘列数’，‘表头’，‘数据’
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


window.onload=function(){
    contactFunction();
};