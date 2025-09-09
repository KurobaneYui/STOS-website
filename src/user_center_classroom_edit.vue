<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import { VueDraggable } from 'vue-draggable-plus'
import "/src/assets/demo.css"

const classrooms = ref([])
const draggable_disable = ref(false)

async function get_classroom() {
    try {
        const { data } = await axios.get('/Ajax/DataManager/get_classroom')
        const code = data.code
        if ([400, 401, 404, 417, 498, 499].includes(code)) {
            if (code === 400) swal({ title: "提供的数据错误，请联系管理员", icon: "error" })
            else if (code === 401) swal({ title: "权限错误", text: "仅队长可查看和编辑。", icon: "error" })
            else if (code === 404) swal({ title: "功能不存在，请联系管理员", icon: "warning" })
            else if (code === 417) swal({ title: "功能错误，请联系管理员", icon: "warning" })
            else if (code === 498) swal({ title: "数据库异常，请联系管理员", icon: "warning" })
            else if (code === 499) swal({ title: "维护中，暂不允许获取教室信息", icon: "warning" })
            return
        }
        if (code === 200 || code === 301) {
            if (code === 301) console.log('获取教室信息函数移至新位置')
            classrooms.value = data.data.map(r => ({
                ...r,
                editing: false,
                isNew: false,
                old_id: r.id,
                _tmp: {
                    id: r.id,
                    campus: r.campus ?? '',
                    building: r.building ?? '',
                    area: r.area ?? '',
                    room_number: r.room_number ?? '',
                    capacity: r.capacity ?? '',
                }
            }))
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

function change_to_editable_row(classroom) {
    draggable_disable.value = true
    classroom.editing = true
    classroom._tmp = {
        id: classroom.id,
        campus: classroom.campus ?? '',
        building: classroom.building ?? '',
        area: classroom.area ?? '',
        room_number: classroom.room_number ?? '',
        capacity: classroom.capacity ?? ''
    }
}

async function upload_classroom(classroom) {
    const id = classroom._tmp.id
    const campus = (classroom._tmp.campus || '').toString()
    const building = (classroom._tmp.building || '').toString()
    const area = (classroom._tmp.area || '').toString()
    const room_number = (classroom._tmp.room_number || '').toString()
    const capacity = Number(classroom._tmp.capacity)
    const old_id = classroom.old_id

    if (!id || !campus || !building || !area || !room_number || isNaN(capacity) || capacity < 0) {
        swal({ title: "请完整填写教室信息且容量非负", icon: "error" })
        return
    }

    draggable_disable.value = false
    try {
        const { data } = await axios.post('/Ajax/DataManager/update_classroom', {
            id, campus, building, area, room_number, capacity, old_id
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
            swal({ title: "维护中，暂不允许修改教室信息", icon: "warning" })
        } else if (returnCode === 200 || returnCode === 301) {
            if (returnCode === 301) console.log('修改教室信息函数移至新位置')
            showToast('success', "成功", "数据已修改，如有问题可刷新重试。")
            await get_classroom()
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

function add_row_for_add_classroom() {
    draggable_disable.value = true
    classrooms.value.push({
        id: null,
        campus: '',
        building: '',
        area: '',
        room_number: '',
        capacity: null,
        editing: true,
        isNew: true,
        old_id: null,
        _tmp: { id: '', campus: '', building: '', area: '', room_number: '', capacity: '' }
    })
}

function cancel_add_classroom(classroom) {
    classrooms.value = classrooms.value.filter(r => r !== classroom)
    const stillAdding = classrooms.value.some(r => r.isNew && r.editing)
    if (!stillAdding) {
        draggable_disable.value = false
    }
}

async function add_classroom(classroom) {
    const id = classroom._tmp.id
    const campus = (classroom._tmp.campus || '').toString()
    const building = (classroom._tmp.building || '').toString()
    const area = (classroom._tmp.area || '').toString()
    const room_number = (classroom._tmp.room_number || '').toString()
    const capacity = Number(classroom._tmp.capacity)

    if (!id || !campus || !building || !area || !room_number || isNaN(capacity) || capacity < 0) {
        swal({ title: "请完整填写教室信息且容量非负", icon: "error" })
        return
    }

    draggable_disable.value = false
    try {
        const { data } = await axios.post('/Ajax/DataManager/add_classroom', { id, campus, building, area, room_number, capacity })
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
            swal({ title: "维护中，暂不允许添加教室信息", icon: "warning" })
        } else if (returnCode === 200 || returnCode === 301) {
            if (returnCode === 301) console.log('添加教室信息函数移至新位置')
            showToast('success', "成功", "数据已添加，如有问题可刷新重试。")
            await get_classroom()
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

async function delete_classroom(classroom) {
    const willDel = await swal({
        title: "确认要删除该教室？",
        text: "删除后不可恢复！注意：删除教室将同步删除与之关联的所有排班数据和打卡数据。",
        icon: "warning",
        buttons: ["取消", "确定删除"],
        dangerMode: true
    })
    if (!willDel) return

    const id = classroom._tmp.id
    const old_id = classroom.old_id

    if (!id) {
        swal({ title: "教室ID不能为空", icon: "error" })
        return
    }

    draggable_disable.value = false
    try {
        const { data } = await axios.post('/Ajax/DataManager/delete_classroom', { id, old_id })
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
            swal({ title: "维护中，暂不允许删除教室信息", icon: "warning" })
        } else if (returnCode === 200 || returnCode === 301) {
            if (returnCode === 301) console.log('删除教室信息函数移至新位置')
            showToast('success', "成功", "数据已删除，如有问题可刷新重试。")
            await get_classroom()
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

function calcClassroomId({ campus, building, area, room_number }) {
    // 校区编码
    const campus_code = campus === '清水河' ? 1 : 2;
    // 楼宇编码
    const building_code = (building === '品学楼' || building === '一教') ? 1 : 2;
    // 区域编码
    const area_map = { 'A': 1, 'B': 2, 'C': 3, '-': 0 };
    const area_code = area_map[area] ?? 0;
    // 房间号编码
    let roomDigits = (room_number ?? '').replace(/\D/g, '').slice(0, 3).padEnd(3, '0');
    // 是否有字母
    const letterMatch = (room_number ?? '').match(/[a-zA-Z]/);
    let letter_num = 0;
    if (letterMatch) {
        const letter = letterMatch[0].toUpperCase();
        letter_num = letter.charCodeAt(0) - 'A'.charCodeAt(0) + 1;
    }
    // 拼接
    return `${campus_code}${building_code}${area_code}${roomDigits}${letter_num}`;
}

function updateTmpId(room) {
    if (room.editing) {
        room._tmp.id = calcClassroomId(room._tmp)
    }
}

// 监听 classrooms，自动设置字段监听
watch(classrooms, (rooms) => {
    rooms.forEach(room => {
        if (room.editing && !room._tmp._isWatching) {
            room._tmp._isWatching = true // 防止重复 watch
            watch(
                () => [room._tmp.campus, room._tmp.building, room._tmp.area, room._tmp.room_number],
                () => updateTmpId(room),
                { immediate: true }
            )
        }
    })
}, { deep: true })

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
    get_classroom()
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
                                <li class="breadcrumb-item"><a href="./index.html">后台数据管理</a></li>
                                <li class="breadcrumb-item active" aria-current="page">编辑全校教室信息</li>
                            </ol>
                        </nav>

                        <!-- Layout Demo -->
                        <div class="row">
                            <div class="col-12 alert alert-primary" role="alert">
                                * 点击“编辑”按钮以修改教室信息；<br />
                                * 点击“提交”按钮以提交变动。<br />
                                * 教室排序自动依以下字段排序：校区、教学楼、区域、编号
                            </div>
                            <div class="col-12 alert alert-danger" role="alert">
                                * 教室信息直接关联查早、查课数据。请尽量以修改代替删除后新增；<br />
                                * 比如一个教室信息已经关联了查早且有数据提交，删除此教室将同步删除相应查早排班和数据，哪怕重新添加了同样的教室。此时应选择修改教室信息以避免上述情况发生。
                            </div>
                            <div class="col-12">
                                <div aria-live="polite" aria-atomic="true"
                                    class="position-fixed top-1 end-0 p-3 zindex-5" id="toast-container"></div>
                                <div class="card">
                                    <h5 class="card-header">编辑早自习教室</h5>
                                    <div class="card-body">
                                        <div class="form-check form-switch ms-3 mb-3">
                                            <label for="draggableButton" class="form-check-label">禁用拖动</label>
                                            <input type="checkbox" class="form-check-input" id="draggableButton"
                                                name="draggableButton" required v-model="draggable_disable" />
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-12 table-responsive text-nowrap">
                                                <VueDraggable v-model="classrooms" target=".sort-target"
                                                    :animation="150" :disabled="draggable_disable">
                                                    <table class="table table-striped table-hover text-center">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>校区</th>
                                                                <th>教学楼</th>
                                                                <th>区域</th>
                                                                <th>编号</th>
                                                                <th>容纳人数</th>
                                                                <th>操作</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="sort-target">
                                                            <tr v-for="(room, idx) in classrooms"
                                                                :key="room.isNew ? `new-${idx}` : room.id">
                                                                <td>
                                                                    <span v-if="!room.editing">{{ room.id }}</span>
                                                                    <input v-else type="text"
                                                                        class="form-control text-center"
                                                                        style="min-width: 40px;" v-model="room._tmp.id"
                                                                        disabled />
                                                                </td>
                                                                <td>
                                                                    <span v-if="!room.editing">{{ room.campus }}</span>
                                                                    <input v-else type="text"
                                                                        class="form-control text-center"
                                                                        style="min-width: 60px;"
                                                                        v-model="room._tmp.campus" />
                                                                </td>
                                                                <td>
                                                                    <span v-if="!room.editing">{{ room.building
                                                                        }}</span>
                                                                    <input v-else type="text"
                                                                        class="form-control text-center"
                                                                        style="min-width: 60px;"
                                                                        v-model="room._tmp.building" />
                                                                </td>
                                                                <td>
                                                                    <span v-if="!room.editing">{{ room.area }}</span>
                                                                    <input v-else type="text"
                                                                        class="form-control text-center"
                                                                        style="min-width: 60px;"
                                                                        v-model="room._tmp.area" />
                                                                </td>
                                                                <td>
                                                                    <span v-if="!room.editing">{{ room.room_number
                                                                        }}</span>
                                                                    <input v-else type="text"
                                                                        class="form-control text-center"
                                                                        style="min-width: 40px;"
                                                                        v-model="room._tmp.room_number" />
                                                                </td>
                                                                <td>
                                                                    <span v-if="!room.editing">{{ room.capacity
                                                                        }}</span>
                                                                    <input v-else type="number" min="0" max="300"
                                                                        class="form-control text-center"
                                                                        style="min-width: 50px;"
                                                                        v-model.number="room._tmp.capacity" />
                                                                </td>
                                                                <td>
                                                                    <template v-if="!room.editing">
                                                                        <button type="button"
                                                                            class="btn btn-warning btn-sm rounded-pill"
                                                                            @click="change_to_editable_row(room)">编辑</button>
                                                                        <button type="button"
                                                                            class="btn btn-danger btn-sm rounded-pill ms-1"
                                                                            @click="delete_classroom(room)">删除</button>
                                                                    </template>
                                                                    <template v-else>
                                                                        <button
                                                                            class="btn btn-primary btn-sm rounded-pill me-1"
                                                                            @click="room.isNew ? add_classroom(room) : upload_classroom(room)">{{
                                                                                room.isNew ? '提交' : '提交'
                                                                            }}</button>
                                                                        <button v-if="room.isNew"
                                                                            class="btn btn-secondary btn-sm rounded-pill"
                                                                            @click="cancel_add_classroom(room)">取消</button>
                                                                        <button v-else
                                                                            class="btn btn-danger btn-sm rounded-pill"
                                                                            @click="delete_classroom(room)">删除</button>
                                                                    </template>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>校区</th>
                                                                <th>教学楼</th>
                                                                <th>区域</th>
                                                                <th>编号</th>
                                                                <th>容纳人数</th>
                                                                <th>操作</th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </VueDraggable>
                                            </div>
                                            <div class="col">
                                                <div class="form-check form-switch ms-3 mb-3">
                                                    <label for="draggableButton" class="form-check-label">禁用拖动</label>
                                                    <input type="checkbox" class="form-check-input" id="draggableButton"
                                                        name="draggableButton" required v-model="draggable_disable" />
                                                </div>
                                                <button class="btn btn-sm btn-warning rounded-pill mb-3"
                                                    @click="add_row_for_add_classroom()">添加</button>
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
