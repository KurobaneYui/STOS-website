<script setup>
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'

const currentPath = window.location.pathname

const userInfo = ref({
    name: '',
    departmentId: 0,
    departmentName: '',
    job: 'member'
})
const formalMember = ref('')
const badges = ref([])

async function getTopbarInfo() {
    try {
        const { data } = await axios.get('/Ajax/Users/topbarInfo')
        const code = data.code
        if ([400, 401, 404, 417, 498, 499].includes(code)) {
            if (data.msg) swal({ title: data.msg, icon: "warning" })
            else swal({ title: '出错了，如刷新无效请尝试重新登录', icon: 'error' })
            return
        }
        if (code === 200 || code === 301) {
            const info = data.data
            userInfo.value = info
            updateFormalMember(info)
            updateBadges(info)
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

function updateFormalMember(info) {
    if (info.department_id === 0) {
        formalMember.value = info.department_name
    } else if (info.department_id === 1) {
        formalMember.value = `${info.department_name} - ${info.job === "manager" ? '队长' : '副队长'}`
    } else {
        formalMember.value = `${info.department_name} - ${info.job === "manager" ? '组长' : '组员'}`
    }
}

function updateBadges(info) {
    badges.value = [
        { text: '查早：完成', type: 'success' },
        { text: '查课：未确认', type: 'warning' },
        // ...
    ]
}


$(function () {
    $.get(
        "/Ajax/Users/get_contact",
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
                        text: "预备队员无通讯录查看权限。",
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
                        title: "功能维护中，暂不允许获取通讯录",
                        icon: "warning",
                    });
                }
                else if (returnCode === 200 || returnCode === 301) {
                    //状态码301，提醒转移函数
                    if (returnCode === 301) { window.console.log('获取通讯录函数移至新位置'); }
                    //状态码200，处理data
                    fill_contact(data['data']);
                }
            }
            else
                alert("请检查网络状况。");
        })
})

function fill_contact(data) {
    let table_body = $("#contact-table-body");

    for (let one_contact of data) {
        table_body.append(`
        <tr>
            <td>${one_contact['id']}</td>
            <td>${one_contact['department']}</td>
            <td>${one_contact['name']}</td>
            <td>${render_gender(one_contact['gender'])}</td>
            <td>${one_contact['phone']}</td>
            <td>${one_contact['qq']}</td>
            <td>${one_contact["job"] == 1 ? (one_contact["department_id"] == 1 ? "队长" : "组长") : (one_contact["department_id"] == 1 ? "副队长" : "组员")}</td>
            
        </tr>
        `)
    }
}

function render_gender(gender) {
    if (gender === '男') return "<span class='badge bg-label-info'>男</span>";
    else if (gender === "女") return "<span class='badge bg-label-danger'>女</span>";
    else return "<span class='badge bg-label-dark'>-</span>";
}

onMounted(getTopbarInfo);
</script>


<template>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <aside class="layout-menu menu-vertical menu bg-menu-theme">
                <Sidebar :current-path="currentPath" :user-info="userInfo" />
            </aside>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <nav
                    class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme rounded-pill">
                    <Topbar :user-info="userInfo" :formal-member="formalMember" :badges="badges" />
                </nav>
                <div>
                    <LoginWork />
                </div>

                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <!-- Breadcrumb -->
                        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                            aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="./index.html">个人中心</a></li>
                                <li class="breadcrumb-item active" aria-current="page">通讯录</li>
                            </ol>
                        </nav>
                        <!-- main content -->
                        <div class="card">
                            <h5 class="card-header">通讯录</h5>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>所属组</th>
                                            <th>姓名</th>
                                            <th>性别</th>
                                            <th>电话</th>
                                            <th>QQ</th>
                                            <th>岗位</th>
                                        </tr>
                                    </thead>
                                    <tbody id="contact-table-body">
                                    </tbody>
                                    <tfoot class="table-border-bottom-0">
                                        <tr>
                                            <th>#</th>
                                            <th>所属组</th>
                                            <th>姓名</th>
                                            <th>性别</th>
                                            <th>电话</th>
                                            <th>QQ</th>
                                            <th>岗位</th>
                                        </tr>
                                    </tfoot>
                                </table>
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
                                <script>
                                    document.write(new Date().getFullYear());
                                </script>
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
