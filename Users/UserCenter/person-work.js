// please import this document when jQuery has been imported

// 获取小圆点图标
$.ajax({url:"/assets/images/users/small-point.js",dataType:"script",async:false});

// 初始化表格提示内容
function table_init(){
    let $table_illustration = $("#illustration");
    $table_illustration.prepend(`<img height="15" width="15" src="${window.green_point}"/> 为空闲，<img height="15" width="15" src="${window.red_point}"/> 为有课`);
    $table_illustration.append(`<img height="15" width="15" src="${window.red_point}"/>`);
}

// 空课信息直接用对应颜色的圆点放入表格对应位置即可
// 岗位信息填入 id = work-table 的岗位展示表的 tbody 中，每一个岗位信息占一行，添加html内容的代码如下
// $("#work-table tbody").append(
// <tr>
//     <th><h3 class="m-b-0 font-light"></h3></th>
//     <th><h3 class="m-b-0 font-light"></h3></th>
//     <th><h3 class="m-b-0 font-light"></h3></th>
//     <th onclick="show_work_remark(0)"><a data-toggle="modal" data-target="#work-remark" href="javascript:void(0)"><h3 class="m-b-0 font-light"><i class="sl-icon-eye"></i></h3></a></th>
// </tr>
// );
// 每个岗位信息的备注存入全局变量：window.workRemarks里，按获取到的岗位顺序存放，如：
// window.workRemarks[0]='the first remark'
// window.workRemarks[1]='the second remark'
// 注意在第一次存放之前，需要先定义全局变量为数组，window.workRemarks = Array()


function addPoint(element,color){
    //element为要添加小圆点的元素，color=0/1,0代表有课，添加红点，1为无课，添加绿点
    if (color==0){
        element.innerHTML=`<img height="15" width="15" src="${window.red_point}"/>`;
    }
    if (color==1){
        element.innerHTML=`<img height="15" width="15" src="${window.green_point}"/>`;
    }
}


// 空课表信息获取函数和岗位信息获取函数
window.workRemarks = Array()
function get_info() {
    $.post("/Ajax/Users/personalWorks.php", {
            'requestFunction': 'getPersonalWorks'
        },
        function handleData(data, status) {
            if (status === "success") {
                let tempdata = JSON.parse(data);
                let returnCode = tempdata['ReturnCode'];
                data = tempdata['Data'];
                if (returnCode === '400') {
                    alert("参数错误，请联系管理员");
                } else if (returnCode === '401') {
                    alert('无权查看个人岗位信息，请联系管理员处理');
                } else if (returnCode === '404') {
                    alert('功能不存在，请联系管理员更正文件调用');
                } else if (returnCode === '417') {
                    alert('功能错误，请联系管理员处理');
                } else if (returnCode === '200' || returnCode === '301') {
                    //状态码301，提醒转移函数
                    if (returnCode === '301') {
                        window.console.log('个人基本岗位信息获取函数移至新位置');
                    }
                    //状态码200，处理data
                    let tempdata = JSON.parse(data);
                    //岗位信息部分
                    let info = tempdata['基本岗位信息'];
                    //先判断有几个岗位
                    workNums=info.length;
                    if (workNums==1){//一个岗位
                        //备注存入全局变量
                        window.workRemarks[0]=info[0]['备注'];
                        $("#work-table tbody").append(
                            `<tr>
                                <th><h4 class="m-b-0 font-light"></h4></th>
                                <th><h4 class="m-b-0 font-light"></h4></th>
                                <th><h4 class="m-b-0 font-light"></h4></th>
                                <th onclick="show_work_remark(0)"><a data-toggle="modal" data-target="#work-remark" href="javascript:void(0)"><h4 class="m-b-0 font-light"><i class="sl-icon-eye"></i></h4></a></th>
                            </tr>`
                        );
                        let transferList=['部门名称','岗位','基本工资']
                        for(i=0;i<3;i++){
                            $("#work-table tbody").children()[0].children[i].children[0].innerHTML=info[0][transferList[i]]
                        }
                    }
                    if(workNums==2){//两个岗位
                        //备注存入全局变量
                        window.workRemarks[0]=info[0]['备注'];
                        window.workRemarks[1]=info[1]['备注'];
                        $("#work-table tbody").append(//第一个岗位
                            `<tr>
                                <th><h3 class="m-b-0 font-light"></h3></th>
                                <th><h3 class="m-b-0 font-light"></h3></th>
                                <th><h3 class="m-b-0 font-light"></h3></th>
                                <th onclick="show_work_remark(0)"><a data-toggle="modal" data-target="#work-remark" href="javascript:void(0)"><h3 class="m-b-0 font-light"><i class="sl-icon-eye"></i></h3></a></th>
                            </tr>`
                        );
                        let transferList=['部门名称','岗位','基本工资']
                        for(i=0;i<3;i++){
                            $("#work-table tbody").children()[0].children[i].children[0].innerHTML=info[0][transferList[i]]
                        }
                        $("#work-table tbody").append(//第二个岗位
                            `<tr>
                                <th><h3 class="m-b-0 font-light"></h3></th>
                                <th><h3 class="m-b-0 font-light"></h3></th>
                                <th><h3 class="m-b-0 font-light"></h3></th>
                                <th onclick="show_work_remark(0)"><a data-toggle="modal" data-target="#work-remark" href="javascript:void(0)"><h3 class="m-b-0 font-light"><i class="sl-icon-eye"></i></h3></a></th>
                            </tr>`
                        );
                        for(i=0;i<3;i++){
                            $("#work-table tbody").children()[1].children[i].children[0].innerHTML=info[0][transferList[i]]
                        }

                    }
                    //然后填写空课表
                    //找到每天的空课,五个数字对应1-2 3-4 5-6 7-8 9-11
                    let table = tempdata['空课表'];
                    let days=Array();
                    let tableRemark=table['备注']
                    days[0] = table['周一空课'];
                    days[1] = table['周二空课'];
                    days[2] = table['周三空课'];
                    days[3] = table['周四空课'];
                    days[4] = table['周五空课'];
                    days[5] = table['周六空课'];
                    days[6] = table['周日空课'];
                    for (j=0;j<5;j++){//5个时段
                        for (i=0;i<7;i++){//7天
                            let dayOdd = $('#odd-week').children()[1].children;//返回一个tbody，tbody有5个children，代表5个时段
                            let dayEven = $('#even-week').children()[1].children;
                            if (days[i][j]=='0'){//该时段、当天、单双周都有课
                                addPoint(dayOdd[j].children[i+1],0);//i+1是因为第一列有表头
                                addPoint(dayEven[j].children[i+1],0);
                            }
                            else if (days[i][j]=='1'){//单周没课
                                addPoint(dayOdd[j].children[i+1],1);
                                addPoint(dayEven[j].children[i+1],0);
                            }
                            else if (days[i][j]=='2'){//双周没课
                                addPoint(dayOdd[j].children[i+1],0);
                                addPoint(dayEven[j].children[i+1],1);
                            }
                            else if (days[i][j]=='3'){//都没课
                                addPoint(dayOdd[j].children[i+1],1);
                                addPoint(dayEven[j].children[i+1],1);
                            }
                        }
                    }
                    //填写空课表下的备注信息
                    $('#table-remark').prop('value',tableRemark)
                    
                }
            } else {
                alert("请检查网络连接，或稍后再试");
            }
        })
}

// 岗位备注信息显示函数
function show_work_remark(idx) {
    $("#work-remark-content").text(window.workRemarks[idx]);
}

$(function(){
    table_init();
    get_info();
});