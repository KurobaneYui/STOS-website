<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"




function get_selfstudy_classroom_details(date) {
    $.post(
        "/Ajax/DataManager/get_selfstudy_classroom_details",
        { "date": date },
        function (data, status) {
            if (status === "success") {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "提供的数据错误，请联系管理员",
                        icon: "error",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误",
                        text: "仅数据组可查看。",
                        icon: "error",
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
                        title: "功能维护中，暂不允许获取早自习教室信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取早自习教室函数移至新位置'); }
                    //状态码200，处理data
                    $("#form-date").val(date);
                    fill_selfstudy_classroom_table(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function fill_selfstudy_classroom_table(data) {
    let table_body = $(`#selfstudy-classroom-editor-table-body`);

    table_body.html("");
    table_body.parent().parent().next().html(`<button class="btn btn-sm btn-warning rounded-pill" onclick="change_to_editable_row(this)">编辑</button>`)

    let counter = 1;
    for (let one_record of data) {
        table_body.append(`
        <tr data-item-id='${one_record["selfstudy_id"]}'>
            <td>${counter}</td>
            <td>${render_campus(one_record["campus"])}</td>
            <td>${one_record["building"] + one_record["area"] + one_record["room"]}</td>
            <td>${one_record["sit_available"]}</td>
            <td>${one_record["school_name"]}</td>
            <td>${one_record["student_supposed"]}</td>
            <td>${one_record["remark"]}</td>
            <td><button class="btn btn-sm btn-danger rounded-pill"
                    onclick="delete_row(this)">删除</button></td>
        </tr>
        `);
        counter++;
    }
}

function render_campus(campus) {
    if (campus === "清水河") return `<span class='badge bg-label-primary'>${campus}</span>`;
    else if (campus === "沙河") return `<span class='badge bg-label-warning'>${campus}</span>`;
}

function add_editable_row() {
    let table_body = $(`#selfstudy-classroom-editor-table-body`);
    table_body.append(`
    <tr data-item-id='-1'>
        <td></td>
        <td>
            <select class="form-control" style="min-width: 80px;" required></select>
        </td>
        <td>
            <select class="form-control" style="min-width: 150px;" required></select>
        </td>
        <td>-</td>
        <td>
            <select class="form-select" style="min-width: 170px;" required></select>
        </td>
        <td><input class="form-control" type="number" style="min-width: 70px;" required/></td>
        <td><input class="form-control" type="text" style="min-width: 120px;" required/></td>
        <td><button class="btn btn-sm btn-danger rounded-pill" onclick="delete_row(this)">删除</button></td>
    </tr>
    `);
    let campusElement = table_body.children().last().children().first().next().children().first();
    let classroomElement = campusElement.parent().next().children().first();
    let schoolElement = classroomElement.parent().next().next().children().first();
    get_campus(campusElement);
    campusElement.change(function () { get_school(schoolElement, campusElement); get_classroom(classroomElement, campusElement); });
    classroomElement.change(function () { $(this).parent().next().text($(this).find("option:selected").first().attr("data-sit-available")) });
}

function change_to_editable_row(button) {
    $(button).parent().append(`<button class="btn btn-sm btn-success rounded-pill" onclick="add_editable_row()">新增</button>`);
    $(button).remove();

    let table_body = $("#selfstudy-classroom-editor-table-body");
    let rows = table_body.children();

    rows.each(function () {
        let row = $(this);
        let cells = row.children();

        // 序号跳过
        let cell = cells.first();
        let tmp = cell.text();
        // 改校区格式
        cell = cell.next();
        tmp = cell.text();
        cell.html(`
        <select class="form-control" style="min-width: 80px;" required>
            <option value="${tmp}">${tmp}</option>
        </select>`);
        let campusElement = cell.children().first();
        campusElement.val(tmp);
        get_campus(campusElement);
        // 改教室格式
        cell = cell.next();
        tmp = cell.text();
        cell.html(`
        <select class="form-control" style="min-width: 150px;" required>
            <option value="${tmp}">${tmp}</option>
        </select>`);
        let classroomElement = cell.children().first();
        classroomElement.val(tmp);
        get_classroom(classroomElement, campusElement);
        // 容纳人数跳过
        cell = cell.next();
        tmp = cell.text();
        // 改学院格式
        cell = cell.next();
        tmp = cell.text();
        cell.html(`
        <select class="form-select" style="min-width: 170px;" required>
            <option value="${tmp}">${tmp}</option>
        </select>`);
        let schoolElement = cell.children().first();
        schoolElement.val(tmp);
        get_school(schoolElement, campusElement);
        // 改应到人数格式
        cell = cell.next();
        tmp = cell.text();
        cell.html(`<input class="form-control" type="number" style="min-width: 70px;" required/>`);
        cell.children().first().val(tmp);
        // 改备注格式
        cell = cell.next();
        tmp = cell.text();
        cell.html(`<input class="form-control" type="text" style="min-width: 120px;" required/>`);
        cell.children().first().val(tmp);

        // 绑定校区改变则改变教室和学院
        campusElement.change(function () { get_school(schoolElement, campusElement); get_classroom(classroomElement, campusElement); });
        classroomElement.change(function () { $(this).parent().next().text($(this).find("option:selected").first().attr("data-sit-available")) });
    })
}

function delete_row(element) {
    let table_body = $(element).parent().parent();
    table_body.remove();
}

function get_submitted_selfstudy_date() {
    $.get(
        "/Ajax/DataManager/get_submitted_selfstudy_date",
        function (data, status) {
            if (status === "success") {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "提供的数据错误，请联系管理员",
                        icon: "error",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误",
                        text: "仅数据组可查看。",
                        icon: "error",
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
                        title: "功能维护中，暂不允许获取已提交早自习教室信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取已提交早自习教室函数移至新位置'); }
                    //状态码200，处理data
                    fill_import_table_body_table(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
}

function fill_import_table_body_table(data) {
    let table_body = $("#import-table-body");

    table_body.html("");

    for (let date of data) {
        table_body.append(`
        <tr>
            <td>${date['date']}</td>
            <td><button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-dismiss="modal"
            aria-label="Close" onclick="import_data(this)">导入</button></td>
        </tr>`);
    }
}

function import_data(button) {
    get_selfstudy_classroom_details($(button).parent().prev().text());
}

function submit() {
    let table_content = $("#selfstudy-classroom-editor-table-body").children();
    let classrooms = Array();
    for (row of table_content) {
        let current_cell = $(row).children().first().next();
        campus = current_cell.children().first().val();
        current_cell = current_cell.next();
        classroom_name = current_cell.children().first().val();
        current_cell = current_cell.next().next();
        school_name = current_cell.children().first().val();
        current_cell = current_cell.next();
        student_supposed = parseInt(current_cell.children().first().val());
        current_cell = current_cell.next();
        remark = current_cell.children().first().val();

        classrooms.push({
            campus: campus,
            classroom_name: classroom_name,
            school_name: school_name,
            student_supposed: student_supposed,
            remark: remark
        })
    }
    let selfstudy_classrooms_data = JSON.stringify({
        "date": $("#form-date").val(),
        "data": classrooms
    });
    $.ajax({
        url: "/Ajax/DataManager/upload_selfstudy_classroom",
        method: "POST",
        data: selfstudy_classrooms_data,
        // data: JSON.stringify(selfstudy_classrooms_data),
        contentType: 'application/json',
        success: function (data, status) {
            if (status === "success") {
                let returnCode = data['code'];
                if (returnCode === 400) {
                    swal({
                        title: "提供的数据错误，请联系管理员",
                        icon: "error",
                    });
                }
                else if (returnCode === 401) {
                    swal({
                        title: "权限错误",
                        text: "仅数据组可编辑。",
                        icon: "error",
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
                        title: "功能维护中，暂不允许提交早自习教室信息",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('提交早自习教室函数移至新位置'); }
                    //状态码200，处理data
                    swal({
                        title: "提交成功",
                        icon: "success",
                    });
                    get_selfstudy_classroom_details($("#form-date").val());
                }
            }
            else
                alert("请检查网络状况。");
        }
    })
}



/*
这个vue代码用于作为模板样例参考，我在下一次输入时会提供一个用jquery技术栈的老页面代码，需要重构至axios、vue、bootstrap技术栈，并满足如下要求：

- 参考样例允许表格行的拖动和禁用逻辑；

- 校区获取和学院获取已经解耦独立获取；

- “导入已有数据”“编辑”“提交”按钮横向排列，统一位于表格上方

- 选择日期的部件不应出现在表格上方，而是点击“提交”后在swal弹窗中选择

- 请直接重构代码并完整给出，便于复制，无需提供任何说明。*/

</script>

<template>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <aside class="layout-menu menu-vertical menu bg-menu-theme">
                <Sidebar />
            </aside>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <nav
                    class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme rounded-pill">
                    <Topbar />
                </nav>
                <div>
                    <LoginWork />
                </div>

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-fluid flex-grow-1 container-p-y">
                        <!-- Breadcrumb -->
                        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                            aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="./index.html">个人中心</a></li>
                                <li class="breadcrumb-item"><a href="./index.html">后台数据管理</a></li>
                                <li class="breadcrumb-item active" aria-current="page">早自习教室信息</li>
                            </ol>
                        </nav>

                        <div class="modal fade" id="select-saved-selfstudy-classroom" tabindex="-1"
                            data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="exampleModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">选择源日期数据</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted">
                                            只列出最近15次提交记录
                                        </p>
                                        <form>
                                            <div class="table-responsive text-nowrap mb-3">
                                                <table class="table table-sm table-hover table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>日期</th>
                                                            <th>操作</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="import-table-body">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Layout Demo -->
                        <div class="row">
                            <div class="col-12 alert alert-primary" role="alert">
                                * 直接添加新教室、选择日期，在全部添加完毕后点击提交即可。如果选择的日期下已有提交数据则覆盖。<br />
                                * 如果已有过往日期的数据，则点击“导入已有数据”按钮，选择记录导入即可。导入完成后按需修改并选择日期，点击提交即可完成<br />
                                * 请为每日查早均提交一份教室数据。
                            </div>
                            <div class="col-12 alert alert-danger" role="alert">
                                * 任何数据的修改都会实时反映在组员查早表单中。<br />
                                * 请尽量选择修改数据而不是删除再添加数据，删除数据会导致对应的组员排班被删除，哪怕重新添加了一份一样的数据。
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <h5 class="card-header">编辑早自习教室</h5>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col mb-3">
                                                <button class="btn btn-sm btn-info rounded-pill" data-bs-toggle="modal"
                                                    data-bs-target="#select-saved-selfstudy-classroom"
                                                    onclick="get_submitted_selfstudy_date()">导入已有数据</button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-auto">
                                                <!-- <div class="col-8 col-sm-6 col-md-4 col-xl-3"> -->
                                                <input id="form-date" type="date" class="form-control" />
                                            </div>
                                            <div class="col-4 col-md-3 col-lg-2">
                                                <button type="button" class="btn btn-sm btn-primary rounded-pill"
                                                    onclick="submit()">提交</button>
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-12 table-responsive text-nowrap">
                                                <table class="table table-striped table-hover text-center">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>校区</th>
                                                            <th>教室</th>
                                                            <th>容纳人数</th>
                                                            <th>学院</th>
                                                            <th>应到人数</th>
                                                            <th>备注</th>
                                                            <th>操作</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="selfstudy-classroom-editor-table-body">
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>校区</th>
                                                            <th>教室</th>
                                                            <th>容纳人数</th>
                                                            <th>学院</th>
                                                            <th>应到人数</th>
                                                            <th>备注</th>
                                                            <th>操作</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                            <div class="col">
                                                <button class="btn btn-sm btn-warning rounded-pill"
                                                    onclick="change_to_editable_row(this)">编辑</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ Layout Demo -->
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div
                            class="container-fluid d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                &copy;
                                <span>{{ new Date().getFullYear() }}</span>
                                <a href="javascript:void(0);"
                                    class="footer-link fw-bolder">学工部学风督导队：罗寅松、赵创日、涂芷荇、张舒涵、谢骁巍</a>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->
</template>
