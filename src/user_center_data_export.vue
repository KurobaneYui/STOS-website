<script setup>
import { ref } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"

// 表单数据（使用ref进行数据双向绑定）
const selfstudyStartDate = ref('')
const selfstudyEndDate = ref('')
const coursesStartDate = ref('')
const coursesEndDate = ref('')

// 按钮loading状态
const selfstudyLoading = ref(false)
const coursesLoading = ref(false)
const emptytimeLoading = ref(false)

// 统一处理返回的状态码和消息
function handleReturnCode(data, context) {
    const returnCode = data.code
    switch (returnCode) {
        case 400:
            swal({
                title: "提供的数据错误，请联系管理员",
                text: data.message,
                icon: "error",
            });
            break;
        case 401:
            swal({
                title: "权限错误",
                text: "仅队长和数据组组长与组员可下载。",
                icon: "error",
            });
            break;
        case 404:
            swal({
                title: "功能不存在，请联系管理员",
                icon: "warning",
            });
            break;
        case 417:
            swal({
                title: "功能错误，请联系管理员",
                icon: "warning",
            });
            break;
        case 498:
            swal({
                title: "数据库异常，请联系管理员",
                icon: "warning",
            });
            break;
        case 499:
            swal({
                title: context === "selfstudy"
                    ? "功能维护中，暂不允许下载早自习检查数据"
                    : context === "courses"
                        ? "功能维护中，暂不允许下载查课检查数据"
                        : "功能维护中，暂不允许下载空课表",
                icon: "warning",
            });
            break;
        case 301:
            if (context === "selfstudy") {
                window.console.log('下载早自习检查数据函数移至新位置')
            } else if (context === "courses") {
                window.console.log('下载查课检查数据函数移至新位置')
            } else if (context === "emptytime") {
                window.console.log('下载空课表函数移至新位置')
            }
        // 301与200一样处理内容
        // eslint-disable-next-line no-fallthrough
        case 200:
            window.open(data.data)
            break;
        default:
            swal({
                title: "未知错误，请联系管理员",
                icon: "error",
            });
            break;
    }
}

// 早自习数据下载
async function downloadSelfstudyAllData() {
    if (!selfstudyStartDate.value || !selfstudyEndDate.value) {
        swal({ title: "请填写完整的日期区间", icon: "error" })
        return
    }
    selfstudyLoading.value = true
    try {
        const response = await axios.post("/Ajax/DataManager/download_selfstudy_all_data", {
            startDate: selfstudyStartDate.value,
            endDate: selfstudyEndDate.value
        })
        handleReturnCode(response.data, "selfstudy")
    } catch (e) {
        swal({ title: "请检查网络状况", icon: "error" })
    }
    selfstudyLoading.value = false
}

// 查课数据下载
async function downloadCoursesCheckAllData() {
    if (!coursesStartDate.value || !coursesEndDate.value) {
        swal({ title: "请填写完整的日期区间", icon: "error" })
        return
    }
    coursesLoading.value = true
    try {
        const response = await axios.post("/Ajax/DataManager/download_courses_all_data", {
            startDate: coursesStartDate.value,
            endDate: coursesEndDate.value
        })
        handleReturnCode(response.data, "courses")
    } catch (e) {
        swal({ title: "请检查网络状况", icon: "error" })
    }
    coursesLoading.value = false
}

// 空课表下载
async function downloadEmptyTimeAllData() {
    emptytimeLoading.value = true
    try {
        const response = await axios.get("/Ajax/DataManager/download_empty_time_all_data")
        handleReturnCode(response.data, "emptytime")
    } catch (e) {
        swal({ title: "请检查网络状况", icon: "error" })
    }
    emptytimeLoading.value = false
}
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
                                <li class="breadcrumb-item active" aria-current="page">数据导出</li>
                            </ol>
                        </nav>
                        <!-- Layout Demo -->
                        <div class="alert alert-primary" role="alert">
                            * 空课表导出暂只支持清水河校区现场组，沙河现场组查课自行安排
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card">
                                    <h5 class="card-header">早自习数据导出</h5>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <label class="form-label" for="selfstudy-start-date">
                                                    选择起始日期
                                                </label>
                                                <input class="form-control" type="date" id="selfstudy-start-date"
                                                    v-model="selfstudyStartDate" required />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="selfstudy-end-date">
                                                    选择结束日期
                                                </label>
                                                <input class="form-control" type="date" id="selfstudy-end-date"
                                                    v-model="selfstudyEndDate" required />
                                            </div>
                                            <div class="col-12 d-flex justify-content-center">
                                                <button type="button" class="btn btn-primary rounded-pill"
                                                    :disabled="selfstudyLoading" @click="downloadSelfstudyAllData">
                                                    <span v-if="selfstudyLoading"
                                                        class="spinner-border spinner-border-sm"></span>
                                                    下载
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card">
                                    <h5 class="card-header">查课数据导出</h5>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <label class="form-label" for="courses-start-date">
                                                    选择起始日期
                                                </label>
                                                <input class="form-control" type="date" id="courses-start-date"
                                                    v-model="coursesStartDate" required />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="courses-end-date">
                                                    选择结束日期
                                                </label>
                                                <input class="form-control" type="date" id="courses-end-date"
                                                    v-model="coursesEndDate" required />
                                            </div>
                                            <div class="col-12 d-flex justify-content-center">
                                                <button type="button" class="btn btn-primary rounded-pill"
                                                    :disabled="coursesLoading" @click="downloadCoursesCheckAllData">
                                                    <span v-if="coursesLoading"
                                                        class="spinner-border spinner-border-sm"></span>
                                                    下载
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card">
                                    <h5 class="card-header">空课表</h5>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-12 d-flex justify-content-center">
                                                <button type="button" class="btn btn-primary rounded-pill"
                                                    :disabled="emptytimeLoading" @click="downloadEmptyTimeAllData">
                                                    <span v-if="emptytimeLoading"
                                                        class="spinner-border spinner-border-sm"></span>
                                                    下载
                                                </button>
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
