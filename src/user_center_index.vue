<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"

const currentPath = window.location.pathname

const userInfo = ref({
    name: '',
    department_id: 0,
    department_name: '',
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
        { text: '查早：XXX', type: 'success' },
        { text: '查课：XXX', type: 'warning' },
        // ...
    ]
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
                        <nav style="
                --bs-breadcrumb-divider: url(
                  &#34;data:image/svg + xml,
                  %3Csvgxmlns='http://www.w3.org/2000/svg'width='8'height='8'%3E%3Cpathd='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z'fill='%236c757d'/%3E%3C/svg%3E&#34;
                );
              " aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="./index.html">个人中心</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">首页</li>
                            </ol>
                        </nav>
                        <!-- Layout Demo -->
                        <div class="row g-2">
                            <div class="col-12 col-lg-6 col-xxl-4">
                                <div class="card">
                                    <h5 class="card-header">今日任务</h5>
                                    <div class="card-body">
                                        <div class="row">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 col-xxl-8">
                                <div class="card">
                                    <h5 class="card-header">队内通知</h5>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- 可以用v-for循环v-if等将通知数据结构化到vue管理 -->
                                            <!-- <div class="col-12 col-md-6">
                                                <div class="card border border-primary">
                                                    <h6 class="card-header">通知1</h6>
                                                    <div class="card-body">
                                                        <p class="card-text">sdafsdfasdf sdfasd fsfsad asd adsdfasdf dsf</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="card border border-primary">
                                                    <h6 class="card-header">通知2</h6>
                                                    <div class="card-body">
                                                        <p class="card-text">sdafsdfasdf sdfasd fsfsad asd adsdfasdf dsf</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="card border border-primary">
                                                    <h6 class="card-header">通知3</h6>
                                                    <div class="card-body">
                                                        <p class="card-text">sdafsdfasdf sdfasd fsfsad asd adsdfasdf dsf</p>
                                                    </div>
                                                </div>
                                            </div> -->
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
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</template>
