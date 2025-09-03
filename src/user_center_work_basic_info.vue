<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"

import GreenPoint from "./components/icons/GreenPoint.vue"
import YellowPoint from "./components/icons/YellowPoint.vue"
import RedPoint from "./components/icons/RedPoint.vue"

const randomImgs = ref([])
const cardImgCount = ref(2)
const cardImgMax = 11
const cardImgMin = 0

function genRandomImgs() {
    randomImgs.value = []
    for (let i = 0; i < cardImgCount; i++) {
        let n = Math.floor(Math.random() * (cardImgMax - cardImgMin + 1) + cardImgMin)
        randomImgs.value.push(n)
    }
}

const empty_table = ref({
    odd: [],
    even: [],
})

async function get_empty_time_info() {
    try {
        const { data } = await axios.get('/Ajax/Users/get_empty_time_info')
        const returnCode = data.code
        if (returnCode === 400) {
            swal({ title: "提供的数据错误，请联系管理员", icon: "error" })
            return
        }
        if (returnCode === 401) {
            swal({ title: "权限错误", text: "预备队员无通讯录查看权限。", icon: "error" })
            return
        }
        if (returnCode === 404) {
            swal({ title: "功能不存在，请联系管理员", icon: "warning" })
            return
        }
        if (returnCode === 417) {
            swal({ title: "功能错误，请联系管理员", icon: "warning" })
            return
        }
        if (returnCode === 498) {
            swal({ title: "数据库异常，请联系管理员", icon: "warning" })
            return
        }
        if (returnCode === 499) {
            swal({ title: "功能维护中，暂不允许获取空课表", icon: "warning" })
            return
        }
        if (returnCode === 200 || returnCode === 301) {
            if (returnCode === 301) { console.log('获取空课表函数移至新位置') }
            // ensure expected structure { odd: [...], even: [...] }
            const payload = data.data || {}
            empty_table.value = {
                odd: payload.odd || [],
                even: payload.even || [],
            }
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

const workBasicInfos = ref([])

function getJobLabel(item) {
    const jobIsLeader = item.job === 1 || item.job === 'manager' || item.job === 'manager' // handle both number/string cases
    const deptIsFirst = item.department_id === 1 || item.departmentId === 1
    if (jobIsLeader) {
        return deptIsFirst ? '队长' : '组长'
    } else {
        return deptIsFirst ? '副队长' : '组员'
    }
}

async function get_work_basic_info() {
    try {
        const { data } = await axios.get('/Ajax/Users/get_work_basic_info')
        const returnCode = data.code
        if (returnCode === 400) {
            swal({ title: "提供的数据错误，请联系管理员", icon: "error" })
            return
        }
        if (returnCode === 401) {
            swal({ title: "权限错误", text: "请先登录", icon: "error" })
            return
        }
        if (returnCode === 404) {
            swal({ title: "功能不存在，请联系管理员", icon: "warning" })
            return
        }
        if (returnCode === 417) {
            swal({ title: "功能错误，请联系管理员", icon: "warning" })
            return
        }
        if (returnCode === 498) {
            swal({ title: "数据库异常，请联系管理员", icon: "warning" })
            return
        }
        if (returnCode === 499) {
            swal({ title: "功能维护中，暂不允许获取基本工作信息", icon: "warning" })
            return
        }
        if (returnCode === 200 || returnCode === 301) {
            if (returnCode === 301) { console.log('基本工作信息函数移至新位置') }
            const payload = data.data || []
            // assign an imgIndex per item so cards can show varied images
            workBasicInfos.value = payload.map(item => {
                // ensure consistent field names (department_id vs departmentId)
                const normalized = Object.assign({}, item)
                if (normalized.departmentId !== undefined && normalized.department_id === undefined) {
                    normalized.department_id = normalized.departmentId
                }
                normalized.imgIndex = Math.floor(Math.random() * (cardImgMax - cardImgMin + 1) + cardImgMin)
                return normalized
            })
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

onMounted(async () => {
    genRandomImgs();
    get_empty_time_info();
    get_work_basic_info();
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
                                <li class="breadcrumb-item"><a href="./index.html">岗位信息</a></li>
                                <li class="breadcrumb-item active" aria-current="page">基本信息</li>
                            </ol>
                        </nav>
                        <!-- Layout Demo -->
                        <div class="alert alert-primary" role="alert">
                            空课时间如有错误，请及时联系组长对数据进行更正，否则会影响查课排班。
                        </div>

                        <div class="row g-3" id="page-container">
                            <div class="col-12">
                                <div class="card">
                                    <h5 class="card-header">空课时间表</h5>
                                    <div class="card-body pb-0">
                                        <p>
                                            <GreenPoint height="15" width="15" />
                                            空闲，
                                            <RedPoint height="15" width="15" />
                                            有课
                                        </p>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="table-responsive text-nowrap">
                                                <table
                                                    class="table table-sm table-hover table-striped mb-3 text-center">
                                                    <thead>
                                                        <tr>
                                                            <th><span class="text-primary fw-bold">单</span></th>
                                                            <th>周一</th>
                                                            <th>周二</th>
                                                            <th>周三</th>
                                                            <th>周四</th>
                                                            <th>周五</th>
                                                            <th>周六</th>
                                                            <th>周日</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(rowData, rowIndex) in (empty_table.odd || [])"
                                                            :key="'odd-' + rowIndex">
                                                            <td>{{ rowIndex * 2 + 1 }}-{{ rowIndex * 2 + 3 }}节</td>
                                                            <td v-for="(cell, colIndex) in rowData" :key="colIndex"
                                                                class="align-middle text-center">
                                                                <GreenPoint v-if="cell === 1" height="15" width="15" />
                                                                <RedPoint v-else-if="cell === 0" height="15"
                                                                    width="15" />
                                                                <span v-else>—</span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="table-responsive text-nowrap">
                                                <table
                                                    class="table table-sm table-hover table-striped mb-3 text-center">
                                                    <thead>
                                                        <tr>
                                                            <th><span class="text-primary fw-bold">双</span></th>
                                                            <th>周一</th>
                                                            <th>周二</th>
                                                            <th>周三</th>
                                                            <th>周四</th>
                                                            <th>周五</th>
                                                            <th>周六</th>
                                                            <th>周日</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(rowData, rowIndex) in (empty_table.even || [])"
                                                            :key="'even-' + rowIndex">
                                                            <td>{{ rowIndex * 2 + 1 }}-{{ rowIndex * 2 + 3 }}节</td>
                                                            <td v-for="(cell, colIndex) in rowData" :key="colIndex"
                                                                class="align-middle text-center">
                                                                <GreenPoint v-if="cell === 1" height="15" width="15" />
                                                                <RedPoint v-else-if="cell === 0" height="15"
                                                                    width="15" />
                                                                <span v-else>—</span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-for="(one, idx) in workBasicInfos" :key="'work-' + idx"
                                class="col-12 col-sm-6 col-md-4 col-lg-4 col-xxl-3">
                                <div class="card position-relative">
                                    <span
                                        :class="['position-absolute top-0 start-100 translate-middle p-2', (one.loginWork === true ? 'bg-success' : 'bg-secondary'), 'border border-light rounded-circle']">
                                        <span class="visually-hidden">New alerts</span>
                                    </span>
                                    <img class="card-img-top random-card-personInfoImg"
                                        :src="`/imgs/personInfoImg${one.imgIndex ?? 0}.png`" alt="UESTC campus" />
                                    <div class="card-body">
                                        <h3 class="card-title text-center mb-3">{{ one.name }}</h3>
                                        <div class="row fs-5 g-3 ms-2">
                                            <span class="col-1 col-lg-2"></span>
                                            <p class="col-auto badge bg-label-primary">岗位</p>
                                            <p class="col">{{ getJobLabel(one) }}</p>
                                            <span class="col-1 col-lg-2"></span>
                                        </div>
                                        <div class="row fs-5 g-3 ms-2">
                                            <span class="col-1 col-lg-2"></span>
                                            <p class="col-auto badge bg-label-primary">工资</p>
                                            <p class="col">&yen;{{ one.wage }}</p>
                                            <span class="col-1 col-lg-2"></span>
                                        </div>
                                        <div class="row fs-5 g-3 ms-2">
                                            <span class="col-1 col-lg-2"></span>
                                            <p class="col-auto badge bg-label-primary">备注</p>
                                            <p class="col">{{ one.remark }}</p>
                                            <span class="col-1 col-lg-2"></span>
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
