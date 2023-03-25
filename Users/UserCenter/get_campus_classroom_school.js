const sleep = (delay) => new Promise((resolve) => setTimeout(resolve, delay));

function get_campus(campus_element) {
    let originalSelect = campus_element.val();
    let existsInNewOptions = false;
    campus_element.html("");
    campus_element.prop("getting", true);
    $.get("/Ajax/DataManager/get_campus_for_form", function (data, status) {
        campus_element.prop("getting", false);
        if (status === 'success') {
            let returnCode = data['code'];
            if (returnCode === 400) {
                swal({
                    title: "参数错误，请联系管理员",
                    icon: "warning",
                });
            }
            else if (returnCode === 401) {
                swal({
                    title: "权限错误，请联系管理员",
                    icon: "warning",
                });
            }
            else if (returnCode === 404) {
                swal({
                    title: "功能不存在，请联系管理员",
                    icon: "warning",
                });
            }
            else if (returnCode === 417) {
                swal({
                    title: "功能错误，请联系管理员",
                    icon: "warning",
                });
            }
            else if (returnCode === 498) {
                swal({
                    title: "数据库异常，请联系管理员",
                    icon: "warning",
                });
            }
            else if (returnCode === 499) {
                swal({
                    title: "功能维护中，暂不允许获取校区信息",
                    icon: "warning",
                });
            }
            else if (returnCode === 200 || returnCode === 301) {
                //状态码301，提醒转移函数
                if (returnCode === 301) { window.console.log('获取校区信息函数移至新位置'); }
                //状态码200，处理data
                for (let i of data['data']) {
                    campus_element.append(`<option value="${i['campus']}">${i['campus']}</option>`);
                    if (originalSelect === i['campus']) existsInNewOptions = true;
                }
                if (existsInNewOptions) { campus_element.val(originalSelect); }
                else { campus_element.val(""); }
            }
        }
        else {
            alert('请检查浏览器网络连接，建议刷新后重试');
        }
    })
}

async function get_school(school_element, campus_element) {
    let originalSelect = school_element.val();
    let existsInNewOptions = false;
    school_element.html("");
    while (campus_element.prop("getting")) { await sleep(50); };
    $.post("/Ajax/DataManager/get_school_for_form",
        { campus: campus_element.val() },
        function (data, status) {
            if (status === 'success') {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "参数错误，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 404) {
                    swal({
                        title: "功能不存在，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 417) {
                    swal({
                        title: "功能错误，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 498) {
                    swal({
                        title: "数据库异常，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 499) {
                    swal({
                        title: "功能维护中，暂不允许获取学院信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取学院信息函数移至新位置'); }
                    //状态码200，处理data
                    for (let i of data['data']) {
                        school_element.append(`<option value="${i['name']}">${i['name']}</option>`);
                        if (originalSelect === i['name']) existsInNewOptions = true;
                    }
                    if (existsInNewOptions) { school_element.val(originalSelect); }
                    else { school_element.val(""); }
                }
            }
            else {
                alert('请检查浏览器网络连接，建议刷新后重试');
            }
        })
}

async function get_classroom(classroom_element, campus_element) {
    let originalSelect = classroom_element.val();
    let existsInNewOptions = false;
    classroom_element.html("");
    while (campus_element.prop("getting")) { await sleep(50); };
    $.post("/Ajax/DataManager/get_classroom",
        { campus: campus_element.val() },
        function (data, status) {
            if (status === 'success') {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "参数错误，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 404) {
                    swal({
                        title: "功能不存在，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 417) {
                    swal({
                        title: "功能错误，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 498) {
                    swal({
                        title: "数据库异常，请联系管理员",
                        icon: "warning",
                    });
                }
                else if (returnCode === 499) {
                    swal({
                        title: "功能维护中，暂不允许获取教室信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取教室信息函数移至新位置'); }
                    //状态码200，处理data
                    for (let i of data['data']) {
                        classroom_element.append(`<option data-sit-available=${i["sit_available"]} value="${i['building']+i['area']+i['room']}">${i['building']+i['area']+i['room']}</option>`);
                        if (originalSelect === (i['building']+i['area']+i['room'])) existsInNewOptions = true;
                    }
                    if (existsInNewOptions) { classroom_element.val(originalSelect); }
                    else { classroom_element.val(""); }
                }
            }
            else {
                alert('请检查浏览器网络连接，建议刷新后重试');
            }
        })
}