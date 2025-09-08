<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import { VueDraggable } from 'vue-draggable-plus'
import "/src/assets/demo.css"

const schools = ref([])
const draggable_disable = ref(false)

async function get_school() {
    try {
        const { data } = await axios.get('/Ajax/DataManager/get_school')
        const code = data.code
        if ([400, 401, 404, 417, 498, 499].includes(code)) {
            if (code === 400) swal({ title: "提供的数据错误，请联系管理员", icon: "error" })
            else if (code === 401) swal({ title: "权限错误", text: "仅队长可查看和编辑。", icon: "error" })
            else if (code === 404) swal({ title: "功能不存在，请联系管理员", icon: "warning" })
            else if (code === 417) swal({ title: "功能错误，请联系管理员", icon: "warning" })
            else if (code === 498) swal({ title: "数据库异常，请联系管理员", icon: "warning" })
            else if (code === 499) swal({ title: "功能维护中，暂不允许获取学院信息", icon: "warning" })
            return
        }
        if (code === 200 || code === 301) {
            if (code === 301) console.log('获取学院信息函数移至新位置')
            schools.value = data.data.map(s => ({
                ...s,
                editing: false,
                isNew: false,
                old_school_id: s.school_id,
                _tmp: {
                    school_id: s.school_id,
                    name: s.name ?? '',
                }
            }))
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

function change_to_editable_row(school) {
    draggable_disable.value=true
    school.editing = true
    school._tmp = {
        school_id: school.school_id,
        name: school.name ?? '',
    }
}

async function upload_school(school) {
    draggable_disable.value=false
    const school_id = Number(school._tmp.school_id)
    const name = (school._tmp.name || '').toString()
    const old_school_id = school.old_school_id

    if (isNaN(school_id) || school_id < 1 || school_id > 50) {
        swal({ title: "请检编号应在1~50之间", icon: "error" })
        return
    }

    try {
        const { data } = await axios.post('/Ajax/DataManager/update_school', {
            school_id, name, old_school_id
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
            swal({ title: "功能维护中，暂不允许修改学院信息", icon: "warning" })
        } else if (returnCode === 200 || returnCode === 301) {
            if (returnCode === 301) console.log('修改学院信息函数移至新位置')
            showToast('success', "成功", "数据已修改，如有问题可刷新重试。")
            await get_school()
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

function add_row_for_add_school() {
    draggable_disable.value=true
    schools.value.push({
        school_id: null,
        name: '',
        editing: true,
        isNew: true,
        old_school_id: null,
        _tmp: { school_id: null, name: '' }
    })
}

function cancel_add_school(school) {
    // 去掉当前school
    schools.value = schools.value.filter(s => s !== school)
    // 检查还有没有“新建而且未保存”（isNew 且 editing为true）的行
    const stillAdding = schools.value.some(s => s.isNew && s.editing)
    if (!stillAdding) {
        draggable_disable.value = false
    }
}

async function add_school(school) {
    const school_id = Number(school._tmp.school_id)
    const name = (school._tmp.name || '').toString()

    if (isNaN(school_id) || school_id < 1 || school_id > 50) {
        swal({ title: "请检编号应在1~50之间", icon: "error" })
        return
    }

    try {
        const { data } = await axios.post('/Ajax/DataManager/add_school', { school_id, name })
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
            swal({ title: "功能维护中，暂不允许添加学院信息", icon: "warning" })
        } else if (returnCode === 200 || returnCode === 301) {
            if (returnCode === 301) console.log('添加学院信息函数移至新位置')
            showToast('success', "成功", "数据已添加，如有问题可刷新重试。")
            await get_school()
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

async function delete_school(school) {
    const school_id = Number(school._tmp.school_id)
    const name = (school._tmp.name || '').toString()
    const old_school_id = school.old_school_id

    if (isNaN(school_id) || school_id < 1 || school_id > 50) {
        swal({ title: "请检编号应在1~50之间", icon: "error" })
        return
    }

    try {
        const { data } = await axios.post('/Ajax/DataManager/delete_school', { school_id, name, old_school_id })
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
            swal({ title: "功能维护中，暂不允许删除学院信息", icon: "warning" })
        } else if (returnCode === 200 || returnCode === 301) {
            if (returnCode === 301) console.log('删除学院信息函数移至新位置')
            showToast('success', "成功", "数据已删除，如有问题可刷新重试。")
            await get_school()
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

onMounted(() => {
    get_school()
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
                                <li class="breadcrumb-item"><a href="./index.html">其他数据</a></li>
                                <li class="breadcrumb-item active" aria-current="page">学院管理</li>
                            </ol>
                        </nav>
                        <!-- main content -->
                        <div class="alert alert-danger" role="alert">
                            * 成员学院数据和后台工作数据的学院信息均和本页信息绑定（通过学院id关联）。编辑学院名称会同步影响到成员信息、查课、查早、早读数据等相关的信息。<br />
                            * 除非添加学院信息后发现错误且暂未有其他信息关联到此学院，可以直接删除信息外，请不要随意点击删除按钮。<br />
                            * 如果需要修改信息，请直接修改而不是删除后重新添加，否则会导致所有关联此学院的数据出现不可恢复的错误。<br />
                            * 更新学院ID不会导致关联数据错误，但请尽量避免在数据处理高峰期（早自习、查课期间）修改。
                        </div>

                        <div aria-live="polite" aria-atomic="true" class="position-fixed top-1 end-0 p-3 zindex-5"
                            id="toast-container"></div>

                        <div class="card">
                            <h5 class="card-header">学院管理</h5>
                            <div class="card-body">
                                <p class="card-subtitle text-muted">
                                    学院名称：<span class="text-primary fw-bold">学院名称完整填写，如：英才实验学院（未来技术学院）</span><br />
                                    保存反馈：修改成功与否会通过右侧气泡展示
                                </p>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <VueDraggable v-model="schools" target=".sort-target" :animation="150" :disabled="draggable_disable">
                                    <table class="table table-hover table-striped mb-3 text-center">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>名称</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody id="school-table-body" class="sort-target">
                                            <tr v-for="(school, idx) in schools"
                                                :key="school.isNew ? `new-${idx}` : school.school_id">
                                                <td>
                                                    <span v-if="!school.editing">{{ school.school_id }}</span>
                                                    <input v-else type="number" min="1" max="50"
                                                        class="form-control text-center" style="min-width: 20px;"
                                                        v-model.number="school._tmp.school_id" />
                                                </td>
                                                <td>
                                                    <span v-if="!school.editing">{{ school.name }}</span>
                                                    <input v-else type="text" class="form-control text-center"
                                                        style="min-width: 120px;" v-model="school._tmp.name" />
                                                </td>
                                                <td>
                                                    <template v-if="!school.editing">
                                                        <button class="btn btn-warning btn-sm rounded-pill"
                                                            @click="change_to_editable_row(school)">编辑</button>
                                                    </template>
                                                    <template v-else>
                                                        <button class="btn btn-primary btn-sm rounded-pill me-1"
                                                            @click="school.isNew ? add_school(school) : upload_school(school)">{{
                                                                school.isNew ? '确定' : '提交' }}</button>
                                                        <button v-if="!school.isNew"
                                                            class="btn btn-danger btn-sm rounded-pill"
                                                            @click="delete_school(school)">删除</button>
                                                        <button v-else class="btn btn-secondary btn-sm rounded-pill"
                                                            @click="cancel_add_school(school)">取消</button>
                                                    </template>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </VueDraggable>
                                <button class="btn btn-primary btn-sm rounded-pill mb-3 ms-3"
                                    @click="add_row_for_add_school()">添加</button>
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
