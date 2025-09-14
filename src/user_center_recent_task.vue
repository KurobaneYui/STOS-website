<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"

import RedPoint from './components/icons/RedPoint.vue'
import GreenPoint from './components/icons/GreenPoint.vue'
import YellowPoint from './components/icons/YellowPoint.vue'

const selfstudyHistory = ref([])
const coursesHistory = ref([])

const isLoading = ref(true)

function getStatusComponent(row) {
    if (row.status === "checked") return GreenPoint
    if (row.status === "pending") return YellowPoint
    if (row.status === null) return RedPoint
    return RedPoint
}

async function fetchTaskHistory() {
    try {
        const resp = await axios.get('/Ajax/Users/get_recent_schedule')
        const data = resp.data
        const returnCode = data.code
        switch (returnCode) {
            case 400:
                swal({ title: "提供的数据错误，请联系管理员", icon: "error" }); break
            case 401:
                swal({ title: "权限错误", text: "非现场组组员无历史任务查看权限。", icon: "error" }); break
            case 404:
                swal({ title: "功能不存在，请联系管理员", icon: "warning" }); break
            case 417:
                swal({ title: "功能错误，请联系管理员", icon: "warning" }); break
            case 498:
                swal({ title: "数据库异常，请联系管理员", icon: "warning" }); break
            case 499:
                swal({ title: "功能维护中，暂不允许获取历史任务", icon: "warning" }); break
            case 200:
            case 301:
                if (returnCode === 301) window.console.log('获取历史任务函数移至新位置')
                // 赋值表格数据
                selfstudyHistory.value = data.data?.selfstudy || []
                coursesHistory.value = data.data?.courses || []
                break
        }
    } catch (e) {
        swal({ title: "请检查网络状况。", icon: "error" })
    } finally {
        isLoading.value = false
    }
}

onMounted(() => {
    fetchTaskHistory()
})
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
                                <li class="breadcrumb-item active" aria-current="page">近期任务总览</li>
                            </ol>
                        </nav>
                        <!-- Layout Demo -->
                        <div class="col-12 alert alert-primary" role="alert">
                            * 历史任务展示过去10日至未来5日的任务记录，按日期由新到旧排列。
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-5">
                                <div class="card">
                                    <h5 class="card-header">查早</h5>
                                    <div class="card-body pb-0">
                                        <p class="card-subtitle text-muted">
                                            <GreenPoint style="vertical-align:middle;" height="15" width="15" />
                                            组长已确认，
                                            <YellowPoint style="vertical-align:middle;" height="15" width="15" />
                                            等待组长确认，
                                            <RedPoint style="vertical-align:middle;" height="15" width="15" />
                                            未提交
                                        </p>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>日期</th>
                                                    <th>教室</th>
                                                    <th>状态</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="isLoading">
                                                    <td colspan="3" class="text-center text-secondary">数据加载中...</td>
                                                </tr>
                                                <template v-else>
                                                    <tr v-for="row in selfstudyHistory"
                                                        :key="row.date + row.classroom_name">
                                                        <td>{{ row.date }}</td>
                                                        <td>{{ row.classroom_name }}</td>
                                                        <td>
                                                            <component :is="getStatusComponent(row)" height="15"
                                                                width="15" style="vertical-align:middle;" />
                                                        </td>
                                                    </tr>
                                                    <tr v-if="selfstudyHistory.length === 0">
                                                        <td colspan="3" class="text-center text-muted">暂无任务数据</td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>日期</th>
                                                    <th>教室</th>
                                                    <th>状态</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-7">
                                <div class="card">
                                    <h5 class="card-header">查课</h5>
                                    <div class="card-body pb-0">
                                        <p class="card-subtitle text-muted">
                                            <GreenPoint style="vertical-align:middle;" height="15" width="15" />
                                            为组长已确认，
                                            <YellowPoint style="vertical-align:middle;" height="15" width="15" />
                                            等待组长确认，
                                            <RedPoint style="vertical-align:middle;" height="15" width="15" />
                                            为未提交
                                        </p>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>日期</th>
                                                    <th>时段</th>
                                                    <th>教室</th>
                                                    <th>状态</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="isLoading">
                                                    <td colspan="5" class="text-center text-secondary">数据加载中...</td>
                                                </tr>
                                                <template v-else>
                                                    <tr v-for="row in coursesHistory"
                                                        :key="`${row.date}-${row.period}-${row.course_order}-${row.classroom_name}`">
                                                        <td>{{ row.date }}</td>
                                                        <td>{{ row.period }}</td>
                                                        <td>{{ row.classroom_name }}</td>
                                                        <td>
                                                            <component :is="getStatusComponent(row)" height="15"
                                                                width="15" style="vertical-align:middle;" />
                                                        </td>
                                                    </tr>
                                                    <tr v-if="coursesHistory.length === 0">
                                                        <td colspan="5" class="text-center text-muted">暂无任务数据</td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>日期</th>
                                                    <th>时段</th>
                                                    <th>教室</th>
                                                    <th>状态</th>
                                                </tr>
                                            </tfoot>
                                        </table>
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
