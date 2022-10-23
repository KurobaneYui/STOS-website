function get_empty_time_info() {
  $.get(
    "/Ajax/Users/get_empty_time_info",
    function(data,status){
        if(status === "success"){
            let returnCode=data['code'];
                if(returnCode===400) {
                    swal({
                        title: "提供的数据错误，请联系管理员",
                        icon: "error",
                    });
                }
                else if(returnCode===401) {
                    swal({
                        title: "权限错误",
                        text: "预备队员无通讯录查看权限。",
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
                    title: "功能维护中，暂不允许获取空课表",
                    icon: "warning",
                    });
                }
                else if (returnCode===200 || returnCode===301) {
                    //状态码301，提醒转移函数
                    if(returnCode===301){window.console.log('获取空课表函数移至新位置');}
                    //状态码200，处理data
                    fill_empty_table(data['data']);
                    replace_point();
                }
        }
        else
            alert("请检查网络状况。");
    })
}

function fill_empty_table(data) {
  let o_e_list = ['odd','even'];
  for (o_e of o_e_list) {
    for (let row in data[o_e]) {
      let emptyTableRow = `<tr>`;
      
      if (row==4) emptyTableRow += `<td>${row*2+1}-${row*2+3}节</td>`;
      else emptyTableRow += `<td>${row*2+1}-${row*2+3}节</td>`;
      
      for (let col in data[o_e][row]) {
        emptyTableRow += `<td>${render_point(data[o_e][row][col])}</td>`;
      }
      emptyTableRow += "</tr>";
      $(`#${o_e}-empty-table-body`).append(emptyTableRow);
    }
  }
  $("#empty-table-remark").text(data['remark'])
}

function render_point(num) {
  if (num==1) return `<img height="15" width="15" src-data="green_point">`;
  if (num==0) return `<img height="15" width="15" src-data="red_point">`;
}

function replace_point() {
  $("img[src-data='red_point']").prop("src",window.red_point);
  $("img[src-data='green_point']").prop("src",window.green_point);
  $("img[src-data='yellow_point']").prop("src",window.yellow_point);
}

function get_work_basic_info() {
  $.get(
    "/Ajax/Users/get_work_basic_info",
    function(data,status){
        if(status === "success"){
            let returnCode=data['code'];
                if(returnCode===400) {
                    swal({
                        title: "提供的数据错误，请联系管理员",
                        icon: "error",
                    });
                }
                else if(returnCode===401) {
                    swal({
                        title: "权限错误",
                        text: "请先登录",
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
                    title: "功能维护中，暂不允许获取基本工作信息",
                    icon: "warning",
                    });
                }
                else if (returnCode===200 || returnCode===301) {
                    //状态码301，提醒转移函数
                    if(returnCode===301){window.console.log('基本工作信息函数移至新位置');}
                    //状态码200，处理data
                    fill_work_basic_info_card(data['data']);
                }
        }
        else
            alert("请检查网络状况。");
    })
}

function add_work_basic_info_card(data) {
  let card =
    `<div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xxl-3">
      <div class="card">
        <img class="card-img-top random-card-personInfoImg" src="/assets/img/users/personInfoImg" alt="UESTC campus" />
        <div class="card-body">
          <h3 class="card-title text-center mb-3">${data["name"]}</h3>
          <div class="row fs-5 g-3 ms-2">
            <span class="col-1 col-lg-2"></span>
            <p class="col-auto badge bg-label-info">岗位</p>
            <p class="col">${data["job"]}</p>
            <span class="col-1 col-lg-2"></span>
          </div>
          <div class="row fs-5 g-3 ms-2">
            <span class="col-1 col-lg-2"></span>
            <p class="col-auto badge bg-label-info">工资</p>
            <p class="col">&yen;${data["wage"]}</p>
            <span class="col-1 col-lg-2"></span>
          </div>
          <div class="row fs-5 g-3 ms-2">
            <span class="col-1 col-lg-2"></span>
            <p class="col-auto badge bg-label-info">备注</p>
            <p class="col">${data["remark"]}</p>
            <span class="col-1 col-lg-2"></span>
          </div>
        </div>
      </div>
    </div>`;
  $("#page-container").append(card);
}

function fill_work_basic_info_card(data) {
  for (let one_work of data) {
    add_work_basic_info_card(one_work);
  }
  set_random_card_img();
}

$(function(){
  get_empty_time_info();
  get_work_basic_info();
})