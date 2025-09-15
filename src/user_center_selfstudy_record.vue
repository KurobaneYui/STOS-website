<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"

const AllAbsentListData = reactive({})
const AllLeaveListData = reactive({})
const selfstudyRecords = ref([])

const modal = ref(null)
const modalTab = ref('fill-data')

const modalShow = ref(false)
const modalTaskId = ref(0)
const modalSubtitle = ref('')
const modalForm = reactive({
    first_count: 0,
    late: 0,
    second_count: 0,
    early_leave: 0,
    leave: 0,
    absentee: 0,
    remark: ''
})

const absentListTable = ref([])
const leaveListTable = ref([])

const absentForm = reactive({
    student_name: '',
    student_id: ''
})
const leaveForm = reactive({
    student_name: '',
    student_id: '',
    reason: ''
})

const currentDate = computed(() => {
    const d = new Date()
    const pad = (n) => (n > 9 ? n : '0' + n)
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
})

function switchTab(tabId) {
    modalTab.value = tabId
}

async function getRecords() {
    try {
        const resp = await axios.get('/Ajax/Users/get_selfstudy_check_data')
        const data = resp.data
        const code = data.code
        if ([400, 401, 404, 417, 498, 499].includes(code)) {
            swal({
                400: { title: "提供的数据错误，请联系管理员", icon: "error" },
                401: { title: "权限错误", text: "非现场组组员无早自习数据查看权限。", icon: "error" },
                404: { title: "功能不存在，请联系管理员", icon: "warning" },
                417: { title: "功能错误，请联系管理员", icon: "warning" },
                498: { title: "数据库异常，请联系管理员", icon: "warning" },
                499: { title: "功能维护中，暂不允许获取早自习数据", icon: "warning" }
            }[code])
            return
        }
        fillSelfstudyCheckData(data.data)
    } catch (e) {
        alert("请检查网络状况。")
    }
}

function fillSelfstudyCheckData(data) {
    selfstudyRecords.value = []
    data.forEach(one_record => {
        AllAbsentListData[one_record.task_id] = one_record.absent_list
        AllLeaveListData[one_record.task_id] = one_record.leave_list
        selfstudyRecords.value.push({
            task_id: one_record.task_id,
            date: one_record.date,
            classroom_id: one_record.classroom_id,
            classroom_name: one_record.classroom_name,
            expected_headcount: one_record.expected_headcount,
            first_count: one_record.first_count ?? '',
            late: one_record.late ?? '',
            second_count: one_record.second_count ?? '',
            early_leave: one_record.early_leave ?? '',
            leave: one_record.leave ?? '',
            absentee: one_record.absentee ?? '',
            absent_list: one_record.absent_list ?? '',
            leave_list: one_record.leave_list ?? '',
            task_remark: one_record.task_remark ?? '',
            remark: one_record.remark ?? '',
            status: one_record.status ?? '',
        })
    })
}

function openModal(row) {
    modalTaskId.value = row.task_id
    modalSubtitle.value = `${row.date} ${row.classroom_name}`
    modalForm.first_count = parseInt(row.first_count) || 0
    modalForm.late = parseInt(row.late) || 0
    modalForm.second_count = parseInt(row.second_count) || 0
    modalForm.early_leave = parseInt(row.early_leave) || 0
    modalForm.leave = parseInt(row.leave) || 0
    modalForm.absentee = parseInt(row.absentee) || 0
    modalForm.remark = row.remark || ''

    // 缺勤名单
    absentForm.student_name = ''
    absentForm.student_id = ''
    absentListTable.value = []
    const absentList = AllAbsentListData[row.task_id]
    absentListTable.value = Array.isArray(absentList) ? absentList : []

    // 请假名单
    leaveForm.student_name = ''
    leaveForm.student_id = ''
    leaveForm.reason = ''
    leaveListTable.value = []
    const leaveList = AllLeaveListData[row.task_id]
    leaveListTable.value = Array.isArray(leaveList) ? leaveList : []

    modalTab.value = 'fill-data'
    modalShow.value = true
    nextTick(() => {
        if (!modal.value) {
            modal.value = new bootstrap.Modal(document.getElementById("update-selfstudy-record"))
        }
        modal.value.show()
    })
}

// 缺勤操作
function addAbsentStudent() {
    if (!absentForm.student_name || !absentForm.student_id) return
    // 检查是否已经存在相同学号
    const trimmedId = absentForm.student_id.trim()
    if (absentListTable.value.some(item => item.student_id === trimmedId)) {
        swal({ title: "该学号已存在于缺勤名单中！", icon: "warning" })
        return
    }
    absentListTable.value.push({
        student_name: absentForm.student_name.trim(),
        student_id: trimmedId,
    })
    absentForm.student_name = ''
    absentForm.student_id = ''
}
function removeAbsentStudent(idx) {
    absentListTable.value.splice(idx, 1)
}

// 请假操作
function addLeaveStudent() {
    if (!leaveForm.student_name || !leaveForm.student_id) return
    // 检查是否已经存在相同学号
    const trimmedId = leaveForm.student_id.trim()
    if (leaveListTable.value.some(item => item.student_id === trimmedId)) {
        swal({ title: "该学号已存在于请假名单中！", icon: "warning" })
        return
    }
    leaveListTable.value.push({
        student_name: leaveForm.student_name.trim(),
        student_id: trimmedId,
        reason: leaveForm.reason ? leaveForm.reason.trim() : ''
    })
    leaveForm.student_name = ''
    leaveForm.student_id = ''
    leaveForm.reason = ''
}
function removeLeaveStudent(idx) {
    leaveListTable.value.splice(idx, 1)
}

async function submitModal() {
    try {
        const task_id = Number(modalTaskId.value)
        if (!task_id) throw "Task ID Illegal !"
        await axios.post('/Ajax/Users/submit_selfstudy_record', {
            task_id,
            first_count: Number(modalForm.first_count),
            late: Number(modalForm.late),
            second_count: Number(modalForm.second_count),
            early_leave: Number(modalForm.early_leave),
            leave: Number(modalForm.leave),
            absentee: Number(modalForm.absentee),
            remark: modalForm.remark,
            absent_list: absentListTable.value,
            leave_list: leaveListTable.value
        }, { headers: { 'Content-Type': 'application/json' } })
            .then((resp) => {
                const data = resp.data
                const code = data.code
                if ([400, 401, 404, 417, 498, 499].includes(code)) {
                    swal({
                        400: { title: "提供的数据错误，请联系管理员", icon: "error" },
                        401: { title: "权限错误", text: "仅现场组可编辑。", icon: "error" },
                        404: { title: "功能不存在，请联系管理员", icon: "warning" },
                        417: { title: "功能错误，请联系管理员", icon: "warning" },
                        498: { title: "数据库异常，请联系管理员", icon: "warning" },
                        499: { title: "功能维护中，暂不允许提交早自习记录信息", icon: "warning" }
                    }[code])
                    return
                }
                if (code === 200 || code === 301) {
                    if (code === 301) window.console.log('提交早自习记录函数移至新位置')
                    getRecords()
                    swal({ title: "提交成功", icon: "success" })
                    modalShow.value = false
                    modal.value?.hide()
                }
            })
    } catch {
        swal({ title: "提供的数据错误，请检查或联系管理员。", icon: "error" })
    }
}

onMounted(() => {
    getRecords()
})
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
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="./index.html">个人中心</a></li>
                                <li class="breadcrumb-item"><a href="./index.html">数据填写</a></li>
                                <li class="breadcrumb-item active" aria-current="page">查早</li>
                            </ol>
                        </nav>
                        <!-- Modal -->
                        <div class="modal fade" id="update-selfstudy-record" tabindex="-1" data-bs-backdrop="static"
                            data-bs-keyboard="false" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <ul class="nav nav-pills" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" :class="{ active: modalTab === 'fill-data' }"
                                                    type="button" @click="switchTab('fill-data')">数据</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" :class="{ active: modalTab === 'fill-absent' }"
                                                    type="button" @click="switchTab('fill-absent')">缺勤</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" :class="{ active: modalTab === 'fill-leave' }"
                                                    type="button" @click="switchTab('fill-leave')">请假</button>
                                            </li>
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            @click="modalShow = false"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p id="modal-subtitle">{{ modalSubtitle }}</p>
                                        <div class="tab-content p-1">
                                            <!-- tab1 数据 -->
                                            <div class="tab-pane fade"
                                                :class="{ show: modalTab === 'fill-data', active: modalTab === 'fill-data' }"
                                                id="fill-data">
                                                <div class="mb-3"><label class="form-label">第一次出勤</label>
                                                    <input type="number" min="0" max="140" class="form-control"
                                                        v-model="modalForm.first_count" required />
                                                </div>
                                                <div class="mb-3"><label class="form-label">迟到</label>
                                                    <input type="number" min="0" max="140" class="form-control"
                                                        v-model="modalForm.late" required />
                                                </div>
                                                <div class="mb-3"><label class="form-label">第二次出勤</label>
                                                    <input type="number" min="0" max="140" class="form-control"
                                                        v-model="modalForm.second_count" required />
                                                </div>
                                                <div class="mb-3"><label class="form-label">早退</label>
                                                    <input type="number" min="0" max="140" class="form-control"
                                                        v-model="modalForm.early_leave" required />
                                                </div>
                                                <div class="mb-3"><label class="form-label">请假</label>
                                                    <input type="number" min="0" max="140" class="form-control"
                                                        v-model="modalForm.leave" required />
                                                </div>
                                                <div class="mb-3"><label class="form-label">缺勤</label>
                                                    <input type="number" min="0" max="140" class="form-control"
                                                        v-model="modalForm.absentee" required />
                                                </div>
                                                <div class="mb-3"><label class="form-label">备注</label>
                                                    <input type="text" class="form-control" v-model="modalForm.remark"
                                                        required />
                                                </div>
                                            </div>
                                            <!-- tab2 缺勤名单 -->
                                            <div class="tab-pane fade"
                                                :class="{ show: modalTab === 'fill-absent', active: modalTab === 'fill-absent' }"
                                                id="fill-absent">
                                                <div class="row">
                                                    <div class="col-12 col-md-5">
                                                        <div class="mb-3"><label class="form-label">姓名</label>
                                                            <input type="text" class="form-control"
                                                                v-model="absentForm.student_name" autofocus required />
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-5">
                                                        <div class="mb-3"><label class="form-label">学号</label>
                                                            <input type="text" class="form-control"
                                                                v-model="absentForm.student_id" autofocus required />
                                                        </div>
                                                    </div>
                                                    <div class="col-auto d-flex align-items-center">
                                                        <button class="btn btn-primary btn-sm rounded-pill"
                                                            @click="addAbsentStudent" type="button">添加</button>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-12 table-responsive text-nowrap">
                                                        <table class="table table-striped table-hover text-center">
                                                            <thead>
                                                                <tr>
                                                                    <th>姓名</th>
                                                                    <th>学号</th>
                                                                    <th>操作</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr v-for="(item, idx) in absentListTable" :key="idx">
                                                                    <td>{{ item.student_name }}</td>
                                                                    <td>{{ item.student_id }}</td>
                                                                    <td>
                                                                        <button
                                                                            class="btn btn-danger btn-sm rounded-pill"
                                                                            @click="removeAbsentStudent(idx)">删除</button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th>姓名</th>
                                                                    <th>学号</th>
                                                                    <th>操作</th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- tab3 请假名单 -->
                                            <div class="tab-pane fade"
                                                :class="{ show: modalTab === 'fill-leave', active: modalTab === 'fill-leave' }"
                                                id="fill-leave">
                                                <div class="row">
                                                    <div class="col-12 col-md-3">
                                                        <div class="mb-3"><label class="form-label">姓名</label>
                                                            <input type="text" class="form-control"
                                                                v-model="leaveForm.student_name" autofocus required />
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-3">
                                                        <div class="mb-3"><label class="form-label">学号</label>
                                                            <input type="text" class="form-control"
                                                                v-model="leaveForm.student_id" autofocus required />
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <div class="mb-3"><label class="form-label">事由</label>
                                                            <input type="text" class="form-control"
                                                                v-model="leaveForm.reason" autofocus required />
                                                        </div>
                                                    </div>
                                                    <div class="col-auto d-flex align-items-center">
                                                        <button class="btn btn-primary btn-sm rounded-pill"
                                                            @click="addLeaveStudent" type="button">添加</button>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-12 table-responsive text-nowrap">
                                                        <table class="table table-striped table-hover text-center">
                                                            <thead>
                                                                <tr>
                                                                    <th>姓名</th>
                                                                    <th>学号</th>
                                                                    <th>事由</th>
                                                                    <th>操作</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr v-for="(item, idx) in leaveListTable" :key="idx">
                                                                    <td>{{ item.student_name }}</td>
                                                                    <td>{{ item.student_id }}</td>
                                                                    <td>{{ item.reason }}</td>
                                                                    <td>
                                                                        <button
                                                                            class="btn btn-danger btn-sm rounded-pill"
                                                                            @click="removeLeaveStudent(idx)">删除</button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th>姓名</th>
                                                                    <th>学号</th>
                                                                    <th>事由</th>
                                                                    <th>操作</th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary rounded-pill"
                                            data-bs-dismiss="modal" @click="submitModal">确定</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- 其余页面结构/表格不变 -->
                        <div class="row">
                            <div class="col-12 alert alert-primary" role="alert">
                                * 已填数据提供过去5天和未来3天的录入信息（包括已录入的和未录入的）。<br />
                                * 点击某条数据，在弹出框中录入或修改数据。<br />
                                * 数据有输入检查。可选在早自习结束后统一录入；亦可先提交部分数据，其余以0代替等待更新。
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <h5 class="card-header">已填数据</h5>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-12 table-responsive text-nowrap">
                                                <table class="table table-hover table-striped text-center">
                                                    <thead>
                                                        <tr>
                                                            <th>日期</th>
                                                            <th>教室</th>
                                                            <th>应到</th>
                                                            <th>排班备注</th>
                                                            <th>第一次出勤</th>
                                                            <th>迟到</th>
                                                            <th>第二次出勤</th>
                                                            <th>早退</th>
                                                            <th>请假</th>
                                                            <th>缺勤</th>
                                                            <th>组长确认</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="item in selfstudyRecords" :key="item.task_id"
                                                            :class="{ 'bg-label-primary': item.date === currentDate }"
                                                            style="cursor:pointer" @click="openModal(item)">
                                                            <td>{{ item.date }}</td>
                                                            <td>{{ item.classroom_name }}</td>
                                                            <td>{{ item.expected_headcount }}</td>
                                                            <td>{{ item.task_remark }}</td>
                                                            <td>{{ item.first_count }}</td>
                                                            <td>{{ item.late }}</td>
                                                            <td>{{ item.second_count }}</td>
                                                            <td>{{ item.early_leave }}</td>
                                                            <td>{{ item.leave }}</td>
                                                            <td>{{ item.absentee }}</td>
                                                            <td>
                                                                <span v-if="item.status === 'checked'"
                                                                    class="badge bg-success">已确认</span>
                                                                <span v-else-if="item.status === 'pending'"
                                                                    class="badge bg-warning">未确认</span>
                                                                <span v-else class="badge bg-secondary">未提交</span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th>日期</th>
                                                            <th>教室</th>
                                                            <th>应到</th>
                                                            <th>排班备注</th>
                                                            <th>第一次出勤</th>
                                                            <th>迟到</th>
                                                            <th>第二次出勤</th>
                                                            <th>早退</th>
                                                            <th>请假</th>
                                                            <th>缺勤</th>
                                                            <th>组长确认</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <footer class="content-footer footer bg-footer-theme" style="margin-top:2em;">
                        <div
                            class="container-fluid d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                &copy; <span>{{ new Date().getFullYear() }}</span>
                                <a href="javascript:void(0);" class="footer-link fw-bolder">
                                    学工部学风督导队
                                </a>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</template>
