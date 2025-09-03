<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"

const departments = ref([])

async function get_department() {
    try {
        const { data } = await axios.get('/Ajax/TeamManager/get_department')
        const code = data.code
        if ([400, 401, 404, 417, 498, 499].includes(code)) {
            if (code === 400) swal({ title: "提供的数据错误，请联系管理员", icon: "error" })
            else if (code === 401) swal({ title: "权限错误", text: "仅队长可查看和编辑。", icon: "error" })
            else if (code === 404) swal({ title: "功能不存在，请联系管理员", icon: "warning" })
            else if (code === 417) swal({ title: "功能错误，请联系管理员", icon: "warning" })
            else if (code === 498) swal({ title: "数据库异常，请联系管理员", icon: "warning" })
            else if (code === 499) swal({ title: "功能维护中，暂不允许获取部门信息", icon: "warning" })
            return
        }
        if (code === 200 || code === 301) {
            // map data into reactive rows, add editing helpers
            departments.value = data.data.map(d => ({
                ...d,
                editing: false,
                _tmp: {
                    group_leader: d.student_id ? `${d.student_name}--${d.student_id}` : '',
                    remark: d.remark ?? ''
                }
            }))
            if (code === 301) { console.log('获取部门信息函数移至新位置'); }
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

function change_to_editable_row(dept) {
    dept.editing = true
    dept._tmp = {
        group_leader: dept.student_id ? `${dept.student_name}--${dept.student_id}` : '',
        remark: dept.remark ?? ''
    }
}

async function upload_department(dept) {
    // validate & prepare
    const department_id = dept.department_id
    let group_leader_id = (dept._tmp.group_leader || '').toString()
    let remark = dept._tmp.remark || ''

    if (department_id === "") {
        swal({ title: "请检查编号", icon: "error" })
        return
    }
    const tmp = group_leader_id.indexOf("-")
    if (tmp !== -1) {
        group_leader_id = group_leader_id.slice(tmp + 2)
    }
    if (group_leader_id === 'null') group_leader_id = ""

    try {
        const { data } = await axios.post('/Ajax/TeamManager/update_department', {
            department_id, group_leader_id, remark
        })
        const returnCode = data.code
        if (returnCode === 400) {
            showToast('error', "提供的数据有误", data.message)
        } else if (returnCode === 401) {
            showToast('error', "权限错误", data.message)
        } else if (returnCode === 404) {
            swal({ title: "功能不存在，请联系管理员", icon: "warning" })
        } else if (returnCode === 417) {
            swal({ title: "功能错误，请联系管理员", icon: "warning" })
        } else if (returnCode === 498) {
            swal({ title: "数据库异常，请联系管理员", icon: "warning" })
        } else if (returnCode === 499) {
            swal({ title: "功能维护中，暂不允许修改部门信息", icon: "warning" })
        } else if (returnCode === 200 || returnCode === 301) {
            if (returnCode === 301) { console.log('修改部门信息函数移至新位置'); }
            showToast('success', "成功", "数据已修改，如有问题可刷新重试。")
            await get_department()
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

function showToast(status, title, text) {
    const container = document.getElementById('toast-container')
    if (!container) return
    const wrapper = document.createElement('div')
    if (status === 'success') {
        wrapper.innerHTML =
            `<div class="bs-toast toast m-2 fade bg-success" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="1000">
                <div class="toast-header">
                    <i class="bx bx-check me-2"></i>
                    <div class="me-auto fw-semibold">${title}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">${text}</div>
            </div>`
    } else {
        wrapper.innerHTML =
            `<div class="bs-toast toast m-2 fade bg-danger" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
                <div class="toast-header">
                    <i class="bx bx-x me-2"></i>
                    <div class="me-auto fw-semibold">${title}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">${text}</div>
            </div>`
    }
    const el = wrapper.firstElementChild
    container.appendChild(el)
    const toast = new bootstrap.Toast(el)
    toast.show()
}

// call both on mount
onMounted(() => {
    get_department()
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
                                <li class="breadcrumb-item"><a href="./index.html">其他数据</a></li>
                                <li class="breadcrumb-item active" aria-current="page">部门管理</li>
                            </ol>
                        </nav>
                        <!-- main content -->
                        <div class="alert alert-danger" role="alert">
                            由于权限系统设计，本页面暂不支持增删功能，只可修改备注、调整组长。
                        </div>

                        <div aria-live="polite" aria-atomic="true" class="position-fixed top-1 end-0 p-3 zindex-5"
                            id="toast-container"></div>

                        <div class="card">
                            <h5 class="card-header">部门管理</h5>
                            <div class="card-body">
                                <p class="card-subtitle text-muted">
                                    人数上限修改：<span class="text-primary fw-bold">注意：人数上限包括组长</span><br />
                                    组长修改：点击“编辑”后输入完整学号即可，请勿输入其他内容<br />
                                    备注修改：只能输入单行内容<br />
                                    保存反馈：修改成功与否会通过右侧气泡展示
                                </p>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover table-striped mb-3 text-center">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>名称</th>
                                            <th>组长</th>
                                            <th>备注</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="department-table-body">
                                        <!-- replaced manual DOM fill with Vue rendering -->
                                        <tr v-for="dept in departments" :key="dept.department_id">
                                            <td>{{ dept.department_id }}</td>
                                            <td>{{ dept.department_name }}</td>
                                            <td>
                                                <span v-if="!dept.editing">{{ dept.student_name }}<span
                                                        v-if="dept.student_id">--{{ dept.student_id }}</span></span>
                                                <input v-else type="text" class="form-control text-center"
                                                    style="min-width: 120px;" v-model="dept._tmp.group_leader" />
                                            </td>
                                            <td>
                                                <span v-if="!dept.editing">{{ dept.remark }}</span>
                                                <input v-else type="text" class="form-control text-center"
                                                    style="min-width: 150px;" v-model="dept._tmp.remark" />
                                            </td>
                                            <td>
                                                <button v-if="!dept.editing" class="btn btn-warning btn-sm rounded-pill"
                                                    @click="change_to_editable_row(dept)">编辑</button>
                                                <button v-else class="btn btn-primary btn-sm rounded-pill"
                                                    @click="upload_department(dept)">提交</button>
                                                <button class="btn btn-danger btn-sm rounded-pill" disabled>删除</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button class="btn btn-primary btn-sm rounded-pill mb-3 ms-3">添加</button>
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
