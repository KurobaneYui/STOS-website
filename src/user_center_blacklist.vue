<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"

const formData = ref({
    date: '',
    name: '',
    gender: 'male',
    student_id: '',
    reason: '',
})
const blacklist = ref([])

const loading = ref(false)

async function addToBlacklist() {
    loading.value = true;
    try {
        const postData = { ...formData.value };
        postData.gender = postData.gender === 'male' ? '男' : '女';
        const { data } = await axios.post('/Ajax/TeamManager/add_blocked', postData);
        const returnCode = data.code;

        if (returnCode === 400) {
            swal({ title: "请求参数错误，请联系管理员", icon: "error" });
            return;
        }
        if (returnCode === 401) {
            swal({ title: "权限不足", text: "暂无加入黑名单的权限", icon: "error" });
            return;
        }
        if (returnCode === 404) {
            swal({ title: "功能不存在", text: "请联系管理员", icon: "warning" });
            return;
        }
        if (returnCode === 417) {
            swal({ title: "功能错误", text: "请联系管理员", icon: "warning" });
            return;
        }
        if (returnCode === 498) {
            swal({ title: "数据库异常", text: "请联系管理员", icon: "warning" });
            return;
        }
        if (returnCode === 499) {
            swal({ title: "功能维护中", text: "暂不允许操作黑名单", icon: "warning" });
            return;
        }
        if (returnCode === 200) {
            await getBlacklist();
            // 只清空以下三个字段
            formData.value.name = '';
            formData.value.student_id = '';
            formData.value.reason = '';
        } else {
            // 其它未覆盖的异常
            swal({
                title: "添加失败",
                text: data.msg || "未知错误，请联系管理员",
                icon: "error"
            });
        }
    } catch (e) {
        swal({
            title: "网络出错",
            text: e.message || "请检查网络或稍后重试",
            icon: "error"
        });
    } finally {
        loading.value = false;
    }
}

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

async function delete_blocked(blockedOne) {
    loading.value = true;
    if (!(await swal({
        title: "确定要删除？",
        text: "删除后不可恢复，确认？",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }))) {
        loading.value = false;
        return;
    }
    try {
        const { data } = await axios.post('/Ajax/TeamManager/delete_blocked', { student_id: blockedOne.student_id });
        const returnCode = data.code;

        if (returnCode === 400) {
            swal({ title: "请求参数错误，请联系管理员", icon: "error" });
            return;
        }
        if (returnCode === 401) {
            swal({ title: "权限不足", text: "暂无删除黑名单的权限", icon: "error" });
            return;
        }
        if (returnCode === 404) {
            swal({ title: "功能不存在", text: "请联系管理员", icon: "warning" });
            return;
        }
        if (returnCode === 417) {
            swal({ title: "功能错误", text: "请联系管理员", icon: "warning" });
            return;
        }
        if (returnCode === 498) {
            swal({ title: "数据库异常", text: "请联系管理员", icon: "warning" });
            return;
        }
        if (returnCode === 499) {
            swal({ title: "功能维护中", text: "暂不允许操作黑名单", icon: "warning" });
            return;
        }
        if (returnCode === 200) {
            // 删除成功，从列表移除
            blacklist.value = blacklist.value.filter(row => row.student_id !== blockedOne.student_id);
            swal({ title: "删除成功", icon: "success" });
        } else {
            swal({
                title: "删除失败",
                text: data.msg || "未知错误，请联系管理员",
                icon: "error"
            });
        }
    } catch (e) {
        swal({
            title: "网络出错",
            text: e.message || "请检查网络或稍后重试",
            icon: "error"
        });
    } finally {
        loading.value = false;
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
                            * 按队伍规范，原则上被清退（不包含请假、自行退出、因事离队等）人员两年内不再招入队伍。此处记录相关事由以供参考。<br />
                            * 添加人员仅供查阅，此处记录不影响人员在网站中的功能。<br />
                            * 添加人员如已注册，则姓名和性别会同步已有信息而非本页提交的信息。<br />
                            * 添加学号已存在条目，则更新时间、事由的记录，请添加前校对学号。
                        </div>
                        <div class="row mb-2">
                            <div class="card">
                                <h5 class="card-header">添加人员</h5>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-lg-3 col-xl-3 mb-3">
                                            <label class="form-label" for="date">选择年月日</label>
                                            <input class="form-control" type="date" id="date" v-model="formData.date"
                                                required />
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                            <label class="form-label" for="name">姓名</label>
                                            <input class="form-control" type="text" id="name" v-model="formData.name"
                                                required>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-4 col-xl-2 mb-3">
                                            <label class="form-label">性别</label>
                                            <div class="row">
                                                <div class="form-check col-2 offset-1">
                                                    <label for="gender_male" class="form-check-label">男</label>
                                                    <input type="radio" class="form-check-input" id="gender_male"
                                                        name="gender" value="male" v-model="formData.gender" />
                                                </div>
                                                <div class="form-check col-2">
                                                    <label for="gender_female" class="form-check-label">女</label>
                                                    <input type="radio" class="form-check-input" id="gender_female"
                                                        name="gender" value="female" v-model="formData.gender" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                                            <label class="form-label" for="student_id">学号</label>
                                            <input class="form-control" type="text" id="student_id"
                                                v-model="formData.student_id" required>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-3 mb-3">
                                            <label class="form-label" for="reason">事由</label>
                                            <input class="form-control" type="text" id="reason"
                                                v-model="formData.reason" required>
                                        </div>
                                        <div class="col-12 d-flex justify-content-center">
                                            <button type="button" class="btn btn-primary rounded-pill"
                                                :disabled="loading" @click="addToBlacklist">添加</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
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
                                                <th>时间</th>
                                                <th>操作</th>
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
                                                <td>{{ blockedOne.start_time }}</td>
                                                <td><button class="btn btn-danger btn-sm rounded-pill"
                                                        @click="delete_blocked(blockedOne)">删除</button></td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-border-bottom-0">
                                            <tr>
                                                <th>#</th>
                                                <th>姓名</th>
                                                <th>性别</th>
                                                <th>学号</th>
                                                <th>事由</th>
                                                <th>时间</th>
                                                <th>操作</th>
                                            </tr>
                                        </tfoot>
                                    </table>
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
