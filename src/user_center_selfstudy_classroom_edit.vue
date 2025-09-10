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

// 业务数据
const campusList = ref([])
const schoolList = ref([])
const classroomList = ref([])
const classroomTable = ref([])
const draggable_disable = ref(false)
const editing = ref(false)
const date = ref('')
const isEditMode = ref(false)

// 校区、学院、教室异步独立获取
async function fetchCampus() {
    try {
        const { data } = await axios.get('/Ajax/DataManager/get_campus')
        if (data.code === 200) {
            campusList.value = data.data
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}
async function fetchSchools(campusVal = '') {
    try {
        const { data } = await axios.get('/Ajax/DataManager/get_school')
        if (data.code === 200) {
            schoolList.value = data.data
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}
async function fetchClassrooms(campusVal = '') {
    try {
        const { data } = await axios.get('/Ajax/DataManager/get_classroom')
        if (data.code === 200) {
            classroomList.value = data.data
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

// 获取表格数据
async function fetchSelfStudyClassroomDetails(targetDate) {
    try {
        const { data } = await axios.post('/Ajax/DataManager/get_selfstudy_classroom_details', { date: targetDate })
        const code = data.code
        if ([400, 401, 404, 417, 498, 499].includes(code)) {
            errorAlert(code)
            return
        }
        if (code === 200 || code === 301) {
            if (code === 301) console.log('获取早自习教室函数移至新位置')
            date.value = targetDate
            classroomTable.value = data.data.map(r => ({
                selfstudy_id: r.selfstudy_id,
                campus: r.campus,
                building: r.building,
                area: r.area,
                room: r.room,
                classroom_name: r.building + r.area + r.room,
                classroom_id: r.classroom_id,
                capacity: r.capacity,
                school_name: r.school_name,
                school_id: r.school_id,
                student_supposed: r.student_supposed,
                remark: r.remark,
                editing: false,
                _tmp: {}
            }))
            isEditMode.value = false
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

// 数据弹窗提醒
function errorAlert(code) {
    if (code === 400) swal({ title: "提供的数据错误，请联系管理员", icon: "error" })
    else if (code === 401) swal({ title: "权限错误", text: "仅数据组可查看/编辑。", icon: "error" })
    else if (code === 404) swal({ title: "功能不存在，请联系管理员", icon: "warning" })
    else if (code === 417) swal({ title: "功能错误，请联系管理员", icon: "warning" })
    else if (code === 498) swal({ title: "数据库异常，请联系管理员", icon: "warning" })
    else if (code === 499) swal({ title: "功能维护中，暂不允许操作", icon: "warning" })
}

// 新增一行
function addEditableRow() {
    if (!isEditMode.value) return
    classroomTable.value.push({
        selfstudy_id: null,
        campus: '',
        classroom_name: '',
        classroom_id: '', // 新增classroom_id字段
        building: '',
        area: '',
        room: '',
        sit_available: '',
        school_name: '',
        school_id: '', // 新增school_id字段
        student_supposed: '',
        remark: '',
        editing: true,
        isNew: true,
        _tmp: {
            campus: '',
            classroom_id: '', // 新增classroom_id字段
            school_id: '', // 新增school_id字段
            student_supposed: '',
            remark: ''
        }
    })
}

// 行转编辑
function changeToEditable() {
    isEditMode.value = true
    draggable_disable.value = true
    classroomTable.value.forEach(row => {
        row.editing = true
        row._tmp = {
            campus: row.campus,
            classroom_id: row.classroom_id, // 修改为classroom_id
            school_id: row.school_id, // 修改为school_id
            student_supposed: row.student_supposed,
            remark: row.remark
        }
    })
}

// 删除行
function deleteRow(idx) {
    classroomTable.value.splice(idx, 1)
}

// 取消新增
function cancelAddRow(idx) {
    classroomTable.value.splice(idx, 1)
    if (!classroomTable.value.some(r => r.isNew && r.editing)) {
        draggable_disable.value = false
    }
}

// 校区选择切换联动学院和教室
function onCampusChange(row) {
    fetchSchools(row._tmp.campus)
    fetchClassrooms(row._tmp.campus)
}

// 导入数据
const importDates = ref([])
async function loadImportDates() {
    try {
        const { data } = await axios.get('/Ajax/DataManager/get_submitted_selfstudy_date')
        if (data.code === 200 || data.code === 301) {
            importDates.value = data.data
        } else {
            errorAlert(data.code)
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}
function importData(d) {
    fetchSelfStudyClassroomDetails(d.date)
    const m = bootstrap.Modal.getInstance(document.getElementById('select-saved-selfstudy-classroom'))
    m && m.hide()
}

// 提交数据，弹窗选择日期
function onSubmitClick() {
    if (!isEditMode.value) return
    if (classroomTable.value.length === 0) {
        swal({ title: "无可提交内容", icon: "warning" })
        return
    }
    swal({
        title: "选择提交日期",
        content: {
            element: "input",
            attributes: {
                type: "date",
                id: "swal-date-input",
                value: date.value
            }
        },
        buttons: {
            cancel: "取消",
            confirm: "确认"
        }
    }).then(async (val) => {
        // 修复：检查用户是否点击了取消按钮
        if (val === null) {
            return // 用户点击了取消，直接返回
        }

        const submitDate = document.getElementById('swal-date-input')?.value
        if (!submitDate) {
            swal({ title: "未选择日期！", icon: "error" })
            return
        }
        await submitTable(submitDate)
    })
}
// 提交数据
async function submitTable(submitDate) {
    // 数据校验
    const classrooms = classroomTable.value.map(row => ({
        campus: row.editing ? row._tmp.campus : row.campus,
        classroom_id: row.editing ? row._tmp.classroom_id : row.classroom_id, // 修改为classroom_id
        school_id: row.editing ? row._tmp.school_id : row.school_id, // 修改为school_id
        student_supposed: Number(row.editing ? row._tmp.student_supposed : row.student_supposed),
        remark: row.editing ? (row._tmp.remark ?? '') : (row.remark ?? '')
    }))
    // 校验主字段
    for (let r of classrooms) {
        if (!r.campus || !r.classroom_id || !r.school_id || !r.student_supposed) {
            swal({ title: "请完善所有必填字段", icon: "error" })
            return
        }
    }
    try {
        const { data } = await axios.post('/Ajax/DataManager/upload_selfstudy_classroom', {
            date: submitDate,
            data: classrooms
        })
        const code = data.code
        if ([400, 401, 404, 417, 498, 499].includes(code)) {
            errorAlert(code)
        } else if (code === 200 || code === 301) {
            if (code === 301) console.log('提交早自习教室函数移至新位置')
            swal({ title: "提交成功", icon: "success" })
            isEditMode.value = false
            draggable_disable.value = false
            await fetchSelfStudyClassroomDetails(submitDate)
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

onMounted(() => {
    fetchCampus()
    fetchSchools()
    fetchClassrooms()
})

// 渲染校区名徽章
function renderCampus(campus) {
    if (campus === "清水河") return `<span class='badge bg-label-primary'>${campus}</span>`;
    else if (campus === "沙河") return `<span class='badge bg-label-warning'>${campus}</span>`;
    else return campus
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

            <div class="layout-page">
                <nav
                    class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme rounded-pill">
                    <Topbar />
                </nav>
                <LoginWork />

                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <!-- Breadcrumb -->
                        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                            aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="./index.html">个人中心</a></li>
                                <li class="breadcrumb-item"><a href="./index.html">后台数据管理</a></li>
                                <li class="breadcrumb-item active" aria-current="page">早自习教室信息</li>
                            </ol>
                        </nav>

                        <div class="modal fade" id="select-saved-selfstudy-classroom" tabindex="-1"
                            data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="exampleModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">选择源日期数据</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted">只列出最近10次提交记录</p>
                                        <form>
                                            <div class="table-responsive text-nowrap mb-3">
                                                <table class="table table-sm table-hover table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>日期</th>
                                                            <th>操作</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="item in importDates" :key="item.date">
                                                            <td>{{ item.date }}</td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-primary rounded-pill"
                                                                    data-bs-dismiss="modal"
                                                                    @click="importData(item)">导入</button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-primary" role="alert">
                            * 直接添加新教室、选择日期后点击提交即可，如果选择的日期下已有提交数据则覆盖。<br />
                            * 如需要编辑以往数据，请点击"导入已有数据"选择并导入所需日期的数据，编辑后提交即可。<br />
                            * 请为每日查早均提交一份教室数据。
                        </div>
                        <div class="alert alert-danger" role="alert">
                            * 修改将实时反映在组员查早表单中。建议修改数据而非删除再添加，否则会导致对应排班被删除。<br />
                            * 修改数据请点击"编辑"按钮，对所需行进行编辑后点击"提交"保存
                        </div>
                        <div class="card">
                            <h5 class="card-header">编辑早自习教室</h5>
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
                                    <button class="btn btn-sm btn-info rounded-pill" data-bs-toggle="modal"
                                        data-bs-target="#select-saved-selfstudy-classroom"
                                        @click="loadImportDates">导入已有数据</button>
                                    <button class="btn btn-sm btn-warning rounded-pill" :disabled="isEditMode"
                                        @click="changeToEditable">编辑</button>
                                    <button class="btn btn-sm btn-success rounded-pill" :disabled="!isEditMode"
                                        @click="onSubmitClick">提交</button>
                                    <span v-if="!isEditMode && date" class="text-muted ms-2">
                                        当前显示日期：ss
                                    </span>
                                </div>

                                <div class="form-check form-switch ms-3 mb-2">
                                    <label for="draggableButton2" class="form-check-label">禁用拖动</label>
                                    <input type="checkbox" class="form-check-input" id="draggableButton2"
                                        name="draggableButton" required v-model="draggable_disable" />
                                </div>
                                <div class="table-responsive text-nowrap">
                                    <VueDraggable v-model="classroomTable" target=".sort-target" :animation="150"
                                        :disabled="draggable_disable || !isEditMode">
                                        <table class="table table-hover table-striped mb-3 text-center">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>校区</th>
                                                    <th>教室</th>
                                                    <th>容纳人数</th>
                                                    <th>学院</th>
                                                    <th>应到人数</th>
                                                    <th>备注</th>
                                                    <th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody class="sort-target">
                                                <tr v-for="(row, idx) in classroomTable" :key="row.selfstudy_id ?? idx">
                                                    <td>{{ idx + 1 }}</td>
                                                    <td v-if="row.editing">
                                                        <select class="form-select campus-min-width"
                                                            v-model="row._tmp.campus" @change="onCampusChange(row)">
                                                            <option value="" disabled>请选择</option>
                                                            <option v-for="c in campusList" :value="c.campus">{{
                                                                c.campus }}
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <td v-else v-html="renderCampus(row.campus)"></td>
                                                    <td v-if="row.editing">
                                                        <select class="form-select classroom-min-width"
                                                            v-model="row._tmp.classroom_id"> <!-- 修改为classroom_id -->
                                                            <option value="" disabled>请选择教室</option>
                                                            <option
                                                                v-for="c in classroomList.filter(r => r.campus === row._tmp.campus)"
                                                                :value="c.id"> <!-- 修改为c.id -->
                                                                {{ c.building + c.area + c.room_number }}
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <td v-else>{{ row.classroom_name }}</td>
                                                    <td>{{row.editing ? (classroomList.find(t => t.id ===
                                                        row._tmp.classroom_id)?.capacity ?? '-') : row.capacity}}</td>
                                                    <td v-if="row.editing">
                                                        <select class="form-select school-min-width"
                                                            v-model="row._tmp.school_id"> <!-- 修改为school_id -->
                                                            <option value="" disabled>请选择学院</option>
                                                            <option v-for="school in schoolList"
                                                                :value="school.school_id"> <!-- 修改为school.school_id -->
                                                                {{ school.name }}</option>
                                                        </select>
                                                    </td>
                                                    <td v-else>{{ row.school_name }}</td>
                                                    <td v-if="row.editing">
                                                        <input class="form-control campus-min-width text-center"
                                                            type="number" min="1" v-model="row._tmp.student_supposed" />
                                                    </td>
                                                    <td v-else>{{ row.student_supposed }}</td>
                                                    <td v-if="row.editing">
                                                        <input class="form-control text-center school-min-width"
                                                            type="text" v-model="row._tmp.remark" />
                                                    </td>
                                                    <td v-else>{{ row.remark }}</td>
                                                    <td>
                                                        <button v-if="isEditMode && (row.editing || row.isNew)"
                                                            class="btn btn-danger btn-sm rounded-pill"
                                                            @click="row.isNew ? cancelAddRow(idx) : deleteRow(idx)">删除</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </VueDraggable>
                                </div>
                                <button class="btn btn-sm btn-success rounded-pill mb-3 ms-3" :disabled="!isEditMode"
                                    @click="addEditableRow">新增</button>
                            </div>
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

<style scoped>
.campus-min-width {
    min-width: 90px;
}

.school-min-width {
    min-width: 180px;
}

.classroom-min-width {
    min-width: 140px;
}
</style>
