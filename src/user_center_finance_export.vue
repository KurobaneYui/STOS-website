<script setup>
import { ref } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"

const formData = ref({
    date: '',
    teacherName: '',
    teacherPhone: '',
    teacherEmail: '',
    teamLeaderName: '',
    teamLeaderPhone: '',
    teamLeaderEmail: '',
    workPlace: '',
    firstWage: '',
    secondWage: '',
    thirdWage: '',
    numForSubsidy: ''
})

const loading = ref(false)

async function download_EXCEL() {
    loading.value = true
    try {
        const { data } = await axios.post('/Ajax/TeamManager/download_finance_EXCEL', formData.value)

        const returnCode = data.code

        if (returnCode === 400) {
            swal({
                title: "提供的数据错误，请联系管理员",
                text: data['message'],
                icon: "error",
            });
        } else if (returnCode === 401) {
            swal({
                title: "权限错误",
                text: "仅队长可下载。",
                icon: "error",
            });
        } else if (returnCode === 404) {
            swal({
                title: "功能不存在，请联系管理员",
                icon: "warning",
            });
        } else if (returnCode === 417) {
            swal({
                title: "功能错误，请联系管理员",
                icon: "warning",
            });
        } else if (returnCode === 498) {
            swal({
                title: "数据库异常，请联系管理员",
                icon: "warning",
            });
        } else if (returnCode === 499) {
            swal({
                title: "功能维护中，暂不允许下载财务报表",
                icon: "warning",
            });
        } else if (returnCode === 200 || returnCode === 301) {
            if (returnCode === 301) window.console.log('下载财务报表函数移至新位置');
            window.open(data["data"]);
        }
    } catch (error) {
        swal({
            title: "网络异常",
            text: "请检查网络状况或稍后再试。",
            icon: "error",
        });
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <aside class="layout-menu menu-vertical menu bg-menu-theme">
                <Sidebar />
            </aside>
            <div class="layout-page">
                <nav
                    class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme rounded-pill">
                    <Topbar />
                </nav>
                <div>
                    <LoginWork />
                </div>
                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                            aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="./index.html">个人中心</a></li>
                                <li class="breadcrumb-item"><a href="./index.html">其他数据</a></li>
                                <li class="breadcrumb-item active" aria-current="page">财务报表导出</li>
                            </ol>
                        </nav>
                        <div class="alert alert-primary" role="alert">
                            * 导出Excel的第一个表单为空表单，用于整理信息后提交。<br />
                            * 导出Excel的第二个表单为信息表单，请对各项信息确认后移至第一个表单，并删除此表单。
                        </div>
                        <div class="alert alert-danger" role="alert">
                            * 导出数据中，一人任多职会导出多条记录，请使用前确认。<br />
                            * 导出数据可能由于组长、队长或组员的任命不符合规范而出现不确定性，如：组长将自己添加进组员列表，导致导出表中出现两次该成员。再次提醒，检查数据！
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <h5 class="card-header">财务报表导出</h5>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="month">选择年月</label>
                                                <input class="form-control" type="month" id="month"
                                                    v-model="formData.date" required />
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="teacher-name">指导老师姓名</label>
                                                <input class="form-control" type="text" id="teacher-name"
                                                    v-model="formData.teacherName" required />
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="teacher-phone">指导老师电话</label>
                                                <input class="form-control" type="tel" id="teacher-phone"
                                                    v-model="formData.teacherPhone" required />
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="teacher-email">指导老师邮箱</label>
                                                <input class="form-control" type="email" id="teacher-email"
                                                    v-model="formData.teacherEmail" required>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="groupLeader-name">骨干姓名</label>
                                                <input class="form-control" type="text" id="groupLeader-name"
                                                    v-model="formData.teamLeaderName" required>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="groupLeader-phone">骨干电话</label>
                                                <input class="form-control" type="tel" id="groupLeader-phone"
                                                    v-model="formData.teamLeaderPhone" required>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="groupLeader-email">骨干邮箱</label>
                                                <input class="form-control" type="email" id="groupLeader-email"
                                                    v-model="formData.teamLeaderEmail" required>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="work-place">办公地点</label>
                                                <input class="form-control" type="text" id="work-place"
                                                    v-model="formData.workPlace" required>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="first-wage">第一档工资</label>
                                                <input class="form-control" type="number" id="first-wage"
                                                    v-model="formData.firstWage" required>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="second-wage">第二档工资</label>
                                                <input class="form-control" type="number" id="second-wage"
                                                    v-model="formData.secondWage" required>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="third-wage">第三档工资</label>
                                                <input class="form-control" type="number" id="third-wage"
                                                    v-model="formData.thirdWage" required>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                                <label class="form-label" for="num-for-subsidy">建档立卡岗位数量</label>
                                                <input class="form-control" type="number" id="num-for-subsidy"
                                                    v-model="formData.numForSubsidy" required>
                                            </div>
                                            <div class="col-12 d-flex justify-content-center">
                                                <button type="button" class="btn btn-primary rounded-pill"
                                                    :disabled="loading" @click="download_EXCEL">{{ loading ? '下载中...' :
                                                    '下载' }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</template>
