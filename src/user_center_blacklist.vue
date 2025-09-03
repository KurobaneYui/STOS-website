<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"

const blacklist = ref([])

function getGenderBadge(gender) {
    if (gender === '男') return 'bg-label-info'
    if (gender === '女') return 'bg-label-danger'
    return 'bg-label-dark'
}

async function getBlacklist() {
    try {
        const { data } = await axios.get('/Ajax/TeamManager/get_blacklist')
        const code = data.code
        if ([400, 401, 404, 417, 498, 499].includes(code)) {
            let title = '出错了'
            let text = '如刷新无效请尝试重新登录'
            if (code === 400) title = "提供的数据错误，请联系管理员"
            else if (code === 401) {
                title = "权限错误"
                text = "预备队员无通讯录查看权限。"
            }
            else if (code === 404) title = "功能不存在，请联系管理员"
            else if (code === 417) title = "功能错误，请联系管理员"
            else if (code === 498) title = "数据库异常，请联系管理员"
            else if (code === 499) title = "功能维护中，暂不允许获取通讯录"

            swal({ title: title, text: text, icon: "error" })
            return
        }
        if (code === 200 || code === 301) {
            if (code === 301) { window.console.log('获取通讯录函数移至新位置'); }
            blacklist.value = data.data
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

onMounted(() => {
    getBlacklist()
});
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
                                <li class="breadcrumb-item active" aria-current="page">清退记录</li>
                            </ol>
                        </nav>
                        <!-- main content -->
                        <div class="col-12 alert alert-primary" role="alert">
                            * 按队伍规范，原则上被清退（不包含请假、自行退出、因事离队等）人员两年内不再招入队伍。此处记录相关事由以供参考。
                        </div>
                        <div class="card">
                            <h5 class="card-header">清退记录</h5>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>姓名</th>
                                            <th>性别</th>
                                            <th>学号</th>
                                            <th>事由</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="blockedOne in blacklist" :key="blockedOne.rowNum">
                                            <td>{{ blockedOne.rowNum }}</td>
                                            <td>{{ blockedOne.name }}</td>
                                            <td>
                                                <span class="badge" :class="getGenderBadge(blockedOne.gender)">
                                                    {{ blockedOne.gender || '-' }}
                                                </span>
                                            </td>
                                            <td>{{ blockedOne.student_id }}</td>
                                            <td>{{ blockedOne.reason }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-border-bottom-0">
                                        <tr>
                                            <th>#</th>
                                            <th>姓名</th>
                                            <th>性别</th>
                                            <th>学号</th>
                                            <th>事由</th>
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
