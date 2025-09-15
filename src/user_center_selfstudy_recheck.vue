<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"

const allAbsentListData = ref({})
const allAskForLeaveListData = ref({})
const records = ref([])
const loading = ref(false)

const modal = ref(null)
const modalSubtitle = ref('')
const modalSelfstudyId = ref(0)
const recheck = ref(false)
const remark = ref('')
const firstPresent = ref(0)
const absent = ref(0)
const secondPresent = ref(0)
const leaveEarly = ref(0)
const askForLeave = ref(0)
const late = ref(0)
const absentList = ref([])
const askForLeaveList = ref([])

const studentName = ref('')
const studentId = ref('')
const studentNameAskForLeave = ref('')
const studentIdAskForLeave = ref('')
const reasonAskForLeave = ref('')

const getCurrentDate = () => {
    // yyyy-MM-dd
    const date = new Date()
    const year = date.getFullYear()
    const month = (`0${date.getMonth() + 1}`).slice(-2)
    const day = (`0${date.getDate()}`).slice(-2)
    return `${year}-${month}-${day}`
}
const currentDate = getCurrentDate()

const fetchRecords = async () => {
    loading.value = true
    try {
        const { data } = await axios.get('/Ajax/GroupManager/get_group_selfstudy_check_data')
        switch (data.code) {
            case 400:
                swal({ title: "提供的数据错误，请联系管理员", icon: "error" }); break
            case 401:
                swal({ title: "权限错误", text: "非现场组组员无早自习数据查看权限。", icon: "error" }); break
            case 404:
                swal({ title: "功能不存在，请联系管理员", icon: "warning" }); break
            case 417:
                swal({ title: "功能错误，请联系管理员", icon: "warning" }); break
            case 498:
                swal({ title: "数据库异常，请联系管理员", icon: "warning" }); break
            case 499:
                swal({ title: "功能维护中，暂不允许获取早自习数据", icon: "warning" }); break
            case 301:
                window.console.log('获取早自习数据函数移至新位置')
            case 200:
                fillSelfstudyData(data.data)
                break
        }
    } catch (err) {
        swal({ title: "请检查网络状况。", icon: "error" })
    }
    loading.value = false
}

function fillSelfstudyData(data) {
    // data 格式: { 组id1: {department_id, department_name, data:{}}, 组id2: {...}, ... }
    const arr = []
    allAbsentListData.value = {}
    allAskForLeaveListData.value = {}
    for (const depId in data) {
        const dep = data[depId]
        const group_name = dep.department_name
        for (const dateStr in dep.data) {
            const dailyList = dep.data[dateStr]
            for (const row of dailyList) {
                arr.push({
                    ...row,
                    date: row.date,
                    student_name: row.student_name,
                    department_id: dep.department_id,
                    department_name: dep.department_name,
                    classroom_name: `${row.classroom_building}${row.classroom_area}${row.classroom_room_number}`,
                    school_name: row.classroom_building,
                    student_supposed: row.classroom_capacity ?? '',
                    status: (row.status !== null && row.status !== undefined) ? row.status :
                        (row.first_count === null ? 'unsubmitted' : 'pending'),
                    selfstudy_id: row.task_id,
                    record: {
                        firstPresent: row.first_count,
                        absent: row.absentee,
                        secondPresent: row.second_count,
                        leaveEarly: row.early_leave,
                        askForLeave: row.leave,
                        late: row.late,
                        remark: row.remarks ?? ''
                    }
                })
                allAbsentListData.value[row.task_id] = row.absent_list
                allAskForLeaveListData.value[row.task_id] = row.leave_list
            }
        }
    }
    records.value = arr
}

const groupedByDepartment = computed(() => {
    // { 部门ID: { department_name: '', records: [ ] } }
    const groups = {}
    records.value.forEach(item => {
        if (!groups[item.department_id]) {
            groups[item.department_id] = {
                department_name: item.department_name,
                records: [],
            }
        }
        groups[item.department_id].records.push(item)
    })
    // 按时间分组
    for (const dep in groups) {
        const dateMap = {}
        groups[dep].records.forEach(rec => {
            if (!dateMap[rec.date]) dateMap[rec.date] = []
            dateMap[rec.date].push(rec)
        })
        // 排序日期（降序）
        groups[dep].datesList = Object.keys(dateMap).sort((a, b) => b.localeCompare(a))
        groups[dep].recordsByDate = dateMap
    }
    return groups
})

function openModal(record) {
    modalSubtitle.value = `${record.date} ${record.school_name} ${record.classroom_name}`
    modalSelfstudyId.value = record.selfstudy_id

    firstPresent.value = parseInt(record.record.firstPresent) || 0
    absent.value = parseInt(record.record.absent) || 0
    secondPresent.value = parseInt(record.record.secondPresent) || 0
    leaveEarly.value = parseInt(record.record.leaveEarly) || 0
    askForLeave.value = parseInt(record.record.askForLeave) || 0
    late.value = parseInt(record.record.late) || 0
    remark.value = record.record.remark || ''

    studentName.value = ''
    studentId.value = ''
    studentNameAskForLeave.value = ''
    studentIdAskForLeave.value = ''
    reasonAskForLeave.value = ''
    recheck.value = false

    absentList.value = allAbsentListData.value[record.selfstudy_id] || [];
    askForLeaveList.value = allAskForLeaveListData.value[record.selfstudy_id] || [];

    const modalEl = document.getElementById('recheck-selfstudy-record')
    modal.value = new bootstrap.Modal(modalEl)
    modal.value.show()
}
function closeModal() {
    if (modal.value) modal.value.hide()
}

function deleteAbsent(idx) {
    absentList.value.splice(idx, 1)
}
function addAbsentStudent() {
    if (!studentName.value || !studentId.value) return
    // 检查学号是否重复
    if (absentList.value.some(stu => stu.student_id === studentId.value)) {
        swal({ title: "该学号已存在于缺勤名单中", icon: "warning" })
        return
    }
    absentList.value.push({ student_name: studentName.value, student_id: studentId.value })
    studentName.value = ''
    studentId.value = ''
}
function deleteAskForLeave(idx) {
    askForLeaveList.value.splice(idx, 1)
}
function addAskForLeaveStudent() {
    if (!studentNameAskForLeave.value || !studentIdAskForLeave.value) return
    // 检查学号是否重复
    if (askForLeaveList.value.some(stu => stu.student_id === studentIdAskForLeave.value)) {
        swal({ title: "该学号已存在于请假名单中", icon: "warning" })
        return
    }
    askForLeaveList.value.push({
        student_name: studentNameAskForLeave.value,
        student_id: studentIdAskForLeave.value,
        reason: reasonAskForLeave.value ? reasonAskForLeave.value.trim() : ''
    })
    studentNameAskForLeave.value = ''
    studentIdAskForLeave.value = ''
    reasonAskForLeave.value = ''
}

async function submitRecord() {
    try {
        if (!recheck.value) throw new Error("请先确认数据无误")
        const payload = {
            task_id: modalSelfstudyId.value,
            record: {
                first_count: firstPresent.value,
                absentee: absent.value,
                second_count: secondPresent.value,
                early_leave: leaveEarly.value,
                leave: askForLeave.value,
                late: late.value,
                remarks: remark.value,
            },
            absent_list: absentList.value,
            leave_list: askForLeaveList.value,
        }
        const { data, status } = await axios.post('/Ajax/GroupManager/submit_selfstudy_record_recheck', payload)
        if (status !== 200) throw new Error()
        switch (data.code) {
            case 400: swal({ title: "提供的数据错误，请联系管理员", icon: "error" }); break
            case 401: swal({ title: "权限错误", text: "仅现场组可编辑。", icon: "error" }); break
            case 404: swal({ title: "功能不存在，请联系管理员", icon: "warning" }); break
            case 417: swal({ title: "功能错误，请联系管理员", icon: "warning" }); break
            case 498: swal({ title: "数据库异常，请联系管理员", icon: "warning" }); break
            case 499: swal({ title: "功能维护中，暂不允许提交早自习记录信息", icon: "warning" }); break
            case 301:
                window.console.log('提交早自习记录函数移至新位置')
            case 200:
                fetchRecords()
                swal({ title: "提交成功", icon: "success" })
                closeModal()
                break
        }
    } catch (error) {
        swal({
            title: "提供的数据错误，请检查或联系管理员。",
            icon: "error",
        });
    }
}

onMounted(() => {
    fetchRecords()
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
                        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                            aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="./index.html">个人中心</a></li>
                                <li class="breadcrumb-item"><a href="./index.html">数据确认</a></li>
                                <li class="breadcrumb-item active" aria-current="page">查早</li>
                            </ol>
                        </nav>

                        <div class="modal fade" id="recheck-selfstudy-record" tabindex="-1" data-bs-backdrop="static"
                            data-bs-keyboard="false" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5>数据确认</h5>
                                        <button type="button" class="btn-close" @click="closeModal()"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p id="modal-subtitle">{{ modalSubtitle }}</p>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" v-model="recheck"
                                                id="recheck">
                                            <label class="form-check-label" for="recheck">
                                                确认数据无误
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-6">
                                                <label class="form-label">第一次出勤</label>
                                                <input type="number" class="form-control" v-model.number="firstPresent"
                                                    min="0" />
                                            </div>
                                            <div class="mb-3 col-6">
                                                <label class="form-label">第二次出勤</label>
                                                <input type="number" class="form-control" v-model.number="secondPresent"
                                                    min="0" />
                                            </div>
                                            <div class="mb-3 col-6">
                                                <label class="form-label">缺勤</label>
                                                <input type="number" class="form-control" v-model.number="absent"
                                                    min="0" />
                                            </div>
                                            <div class="mb-3 col-6">
                                                <label class="form-label">请假</label>
                                                <input type="number" class="form-control" v-model.number="askForLeave"
                                                    min="0" />
                                            </div>
                                            <div class="mb-3 col-6">
                                                <label class="form-label">早退</label>
                                                <input type="number" class="form-control" v-model.number="leaveEarly"
                                                    min="0" />
                                            </div>
                                            <div class="mb-3 col-6">
                                                <label class="form-label">迟到</label>
                                                <input type="number" class="form-control" v-model.number="late"
                                                    min="0" />
                                            </div>
                                            <div class="mb-3 col-12">
                                                <label for="remark" class="form-label">备注</label>
                                                <input type="text" class="form-control" id="remark" v-model="remark"
                                                    placeholder="" autofocus required />
                                            </div>
                                        </div>
                                        <hr />
                                        <h6>缺勤名单</h6>
                                        <div class="mb-3 input-group">
                                            <input type="text" class="form-control" v-model="studentName"
                                                placeholder="学生姓名" />
                                            <input type="text" class="form-control" v-model="studentId"
                                                placeholder="学号" />
                                            <button class="btn btn-outline-secondary"
                                                @click="addAbsentStudent">添加</button>
                                        </div>
                                        <table class="table table-sm table-striped table-hover text-center">
                                            <thead>
                                                <tr>
                                                    <th>姓名</th>
                                                    <th>学号</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(stu, idx) in absentList" :key="`absent-${idx}`">
                                                    <td>{{ stu.student_name }}</td>
                                                    <td>{{ stu.student_id }}</td>
                                                    <td>
                                                        <button class="btn btn-danger btn-sm rounded-pill"
                                                            @click="deleteAbsent(idx)">删除</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <hr />
                                        <h6>请假名单</h6>
                                        <div class="mb-3 input-group">
                                            <input type="text" class="form-control" v-model="studentNameAskForLeave"
                                                placeholder="学生姓名" />
                                            <input type="text" class="form-control" v-model="studentIdAskForLeave"
                                                placeholder="学号" />
                                            <input type="text" class="form-control" v-model="reasonAskForLeave"
                                                placeholder="事由" />
                                            <button class="btn btn-outline-secondary"
                                                @click="addAskForLeaveStudent">添加</button>
                                        </div>
                                        <table class="table table-sm table-striped table-hover text-center">
                                            <thead>
                                                <tr>
                                                    <th>姓名</th>
                                                    <th>学号</th>
                                                    <th>原因</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(stu, idx) in askForLeaveList" :key="`askForLeave-${idx}`">
                                                    <td>{{ stu.student_name }}</td>
                                                    <td>{{ stu.student_id }}</td>
                                                    <td>{{ stu.reason }}</td>
                                                    <td>
                                                        <button class="btn btn-danger btn-sm rounded-pill"
                                                            @click="deleteAskForLeave(idx)">删除</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="text-end mt-3">
                                            <button type="button" class="btn btn-primary rounded-pill"
                                                :disabled="!recheck" @click="submitRecord">确定</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 alert alert-primary" role="alert">
                                * 组员数据提供过去7天和未来3天的录入信息（包括已录入的和未录入的）。<br />
                                * 点击某条数据，在弹出框中确认数据。<br />
                                * 数据背景颜色含义：红色-组员未提交数据、黄色-组长未确认数据、绿色-数据已确认。<br />
                                * 组员修改数据后数据会重置为未确认状态。<br />
                                * 备注实例：组员迟到第一次出勤未记录、数据错误且已无法核实……
                            </div>
                        </div>
                        <div class="row">
                            <template v-for="(group, depId) in groupedByDepartment" :key="depId">
                                <div class="col-12 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">{{ group.department_name }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <template v-for="date in group.datesList" :key="date">
                                                <h5 class="mb-0 mt-0">{{ date }}</h5>
                                                <div class="table-responsive text-nowrap mb-3">
                                                    <table class="table table-hover table-striped text-center">
                                                        <thead>
                                                            <tr>
                                                                <th>日期</th>
                                                                <th>教室</th>
                                                                <th>排班备注</th>
                                                                <th>分配组员</th>
                                                                <th>应到</th>
                                                                <th>第一次出勤</th>
                                                                <th>迟到</th>
                                                                <th>第二次出勤</th>
                                                                <th>早退</th>
                                                                <th>请假</th>
                                                                <th>缺勤</th>
                                                                <th>状态</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="record in group.recordsByDate[date]"
                                                                :key="record.selfstudy_id + record.student_id" :class="{
                                                                    'bg-label-primary': currentDate === record.date
                                                                }" style="cursor:pointer" @click="openModal(record)">
                                                                <td>{{ record.date }}</td>
                                                                <td>{{ record.classroom_name }}</td>
                                                                <td>{{ record.task_remark }}</td>
                                                                <td>{{ record.student_name }}</td>
                                                                <td>{{ record.student_supposed }}</td>
                                                                <td>{{ record.record.firstPresent ?? '' }}</td>
                                                                <td>{{ record.record.late ?? '' }}</td>
                                                                <td>{{ record.record.secondPresent ?? '' }}</td>
                                                                <td>{{ record.record.leaveEarly ?? '' }}</td>
                                                                <td>{{ record.record.askForLeave ?? '' }}</td>
                                                                <td>{{ record.record.absent ?? '' }}</td>
                                                                <td>
                                                                    <span v-if="record.status === 'checked'"
                                                                        class="badge bg-success">已确认</span>
                                                                    <span v-else-if="record.status === 'pending'"
                                                                        class="badge bg-warning">未确认</span>
                                                                    <span v-else class="badge bg-secondary">未提交</span>
                                                                </td>
                                                            </tr>
                                                            <tr v-if="group.recordsByDate[date].length === 0">
                                                                <td colspan="11" class="text-center text-muted">暂无数据
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <th>日期</th>
                                                                <th>教室</th>
                                                                <th>排班备注</th>
                                                                <th>分配组员</th>
                                                                <th>应到</th>
                                                                <th>第一次出勤</th>
                                                                <th>迟到</th>
                                                                <th>第二次出勤</th>
                                                                <th>早退</th>
                                                                <th>请假</th>
                                                                <th>缺勤</th>
                                                                <th>状态</th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <footer class="content-footer footer bg-footer-theme">
                        <div
                            class="container-fluid d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                &copy; <span>{{ new Date().getFullYear() }}</span>
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
