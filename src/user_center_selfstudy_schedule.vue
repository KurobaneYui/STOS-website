<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import "/src/assets/demo.css"

// 业务数据
const currentDate = ref('')
const isEditMode = ref(false)
const importDates = ref([])
const qingshuiheList = ref([])
const shaheList = ref([])
// 新增：学号字典，所有可能出现的成员
const sourceStudentDict = ref({})

// 提取所有成员的学号、姓名、组为字典
function buildSourceStudentDict(scheduleArr, unassignedArr) {
    const dict = {}
    scheduleArr.forEach(item => {
        if (item.student_id) {
            dict[item.student_id] = {
                student_id: item.student_id,
                name: item.name,
                group_name: item.group_name
            }
        }
    })
    unassignedArr.forEach(item => {
        if (item.student_id) {
            dict[item.student_id] = {
                student_id: item.student_id,
                name: item.name,
                group_name: item.group_name
            }
        }
    })
    return dict
}

// 实时分配成员ID集合
const assignedStudentIdSet = computed(() => {
    return new Set([
        ...qingshuiheList.value.map(row => row.student_id),
        ...shaheList.value.map(row => row.student_id)
    ]);
})

// 实时未分配成员，按学号排序
const unassignedList = computed(() => {
    const list = []
    for (const sid in sourceStudentDict.value) {
        if (!assignedStudentIdSet.value.has(sid)) {
            list.push(sourceStudentDict.value[sid])
        }
    }
    return list.sort((a, b) => a.student_id.localeCompare(b.student_id))
})

// 错误处理函数
function errorAlert(code) {
    if (code === 400) swal({ title: "提供的数据错误，请联系管理员", icon: "error" })
    else if (code === 401) swal({ title: "权限错误", text: "仅数据组可查看/编辑。", icon: "error" })
    else if (code === 404) swal({ title: "功能不存在，请联系管理员", icon: "warning" })
    else if (code === 417) swal({ title: "功能错误，请联系管理员", icon: "warning" })
    else if (code === 498) swal({ title: "数据库异常，请联系管理员", icon: "warning" })
    else if (code === 499) swal({ title: "功能维护中，暂不允许操作", icon: "warning" })
}

// 加载可导入的日期列表
async function loadImportDates() {
    try {
        const { data } = await axios.get('/Ajax/DataManager/get_submitted_selfstudy_schedule_date')
        if (data.code === 200 || data.code === 301) {
            importDates.value = data.data
        } else {
            errorAlert(data.code)
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

// 导入指定日期的数据
async function importData(d) {
    try {
        const { data: response } = await axios.post('/Ajax/DataManager/get_schedule_on_date', { date: d.date })
        if (response.code === 200) {
            const { date, schedule, unassigned } = response.data
            currentDate.value = date

            // 提取所有成员构建索引字典
            sourceStudentDict.value = buildSourceStudentDict(schedule, unassigned)

            const scheduleArray = schedule;
            // 过滤校区
            qingshuiheList.value = scheduleArray.filter(item => item.campus === '清水河')
            shaheList.value = scheduleArray.filter(item => item.campus === '沙河')
            isEditMode.value = false;
        } else {
            errorAlert(response.code)
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
    // 关闭模态框
    const m = bootstrap.Modal.getInstance(document.getElementById('select-saved-selfstudy-classroom'))
    m && m.hide()
}

// 删除某日排班
async function removeSchedule(item) {
    const willDelete = await swal({
        title: `确定要删除 ${item.date} 的排班记录吗？`,
        text: "此操作不可恢复，是否继续？",
        icon: "warning",
        buttons: ["取消", "删除"],
        dangerMode: true,
    });
    if (!willDelete) return;

    try {
        const { data } = await axios.post('/Ajax/DataManager/remove_schedule_on_date', {
            date: item.date
        });
        if (data.code === 200) {
            swal({ title: "删除成功", icon: "success" });
            await loadImportDates();
        } else {
            errorAlert(data.code);
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" });
    }
}

// 编辑模式
function enterEditMode() {
    isEditMode.value = true
}
function exitEditMode() {
    isEditMode.value = false
}

// 提交按钮点击事件
function onSubmitClick() {
    if (!isEditMode.value) return
    if (!currentDate.value) {
        swal({ title: "请选择日期！", icon: "warning" })
        return
    }
    const postData = {
        date: currentDate.value,
        data: {
            qingshuihe: qingshuiheList.value.map(item => ({
                selfstudy_id: item.schedule_id,
                student_id: item.student_id
            })),
            shahe: shaheList.value.map(item => ({
                selfstudy_id: item.schedule_id,
                student_id: item.student_id
            }))
        }
    }
    submitTable(postData)
}

// 提交数据到后端
async function submitTable(data) {
    try {
        const { data: res } = await axios.post('/Ajax/DataManager/submit_selfstudy_schedule', data)
        if (res.code === 200) {
            swal({ title: "提交成功", icon: "success" }).then(exitEditMode)
        } else {
            errorAlert(res.code)
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

// 随机打乱校区排班
async function randomMember(campus) {
    if (!currentDate.value) {
        swal({ title: "请先选择日期！", icon: "warning" })
        return
    }
    try {
        const { data } = await axios.post('/Ajax/DataManager/random_schedule_on_date', {
            date: currentDate.value,
            campus: campus
        })
        if (data.code === 200) {
            if (campus === '清水河') {
                qingshuiheList.value = data.data
            } else {
                shaheList.value = data.data
            }
            swal({ title: "随机排序成功", icon: "success" })
        } else {
            errorAlert(data.code)
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

// 删除行
function deleteRow(campus, index) {
    if (campus === '清水河') {
        qingshuiheList.value.splice(index, 1)
    } else {
        shaheList.value.splice(index, 1)
    }
}

// 页面加载时设置当前日期
onMounted(() => {
    const today = new Date().toISOString().split('T')[0]
    currentDate.value = today
})

// 渲染校区徽章
function renderCampus(campus) {
    if (campus === "清水河") return `<span class='badge bg-label-primary'>${campus}</span>`;
    else if (campus === "沙河") return `<span class='badge bg-label-warning'>${campus}</span>`;
    else return campus
}

// 自动补全学号对应人员信息（name, group_name）
watch(qingshuiheList, (nv) => {
    nv.forEach(row => {
        if (!row.student_id) {
            row.name = ""
            row.group_name = ""
            return
        }
        const mapping = sourceStudentDict.value[row.student_id]
        if (mapping) {
            row.name = mapping.name
            row.group_name = mapping.group_name
        } else {
            row.name = ""
            row.group_name = ""
        }
    });
}, { deep: true })

watch(shaheList, (nv) => {
    nv.forEach(row => {
        if (!row.student_id) {
            row.name = ""
            row.group_name = ""
            return
        }
        const mapping = sourceStudentDict.value[row.student_id]
        if (mapping) {
            row.name = mapping.name
            row.group_name = mapping.group_name
        } else {
            row.name = ""
            row.group_name = ""
        }
    });
}, { deep: true })
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
                <LoginWork />
                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                            aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="./index.html">个人中心</a></li>
                                <li class="breadcrumb-item"><a href="./index.html">后台数据管理</a></li>
                                <li class="breadcrumb-item active" aria-current="page">早自习排班</li>
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
                                        <p class="text-muted">只列出最近15次排班记录</p>
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
                                                            <td v-if="item.submitted_at">{{ "自习日期：" + item.date +
                                                                "，提交于：" + item.submitted_at }}</td>
                                                            <td v-else>{{ "自习日期：" + item.date }}</td>
                                                            <td class="d-flex gap-2">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-primary rounded-pill"
                                                                    data-bs-dismiss="modal"
                                                                    @click="importData(item)">导入</button>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-danger rounded-pill"
                                                                    @click="removeSchedule(item)">删除</button>
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
                            * 点击导入按钮导入教室和排班<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;* “自习日期+提交于”表明日期已提交排班，导入时会带入排班数据<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;* “自习日期”表明日期仅有早自习安排，无查早排班，导入时仅导入教室信息<br />
                            <!-- 已移除与重置相关注释 -->
                            * 导入信息后点击"随机"按钮打乱排班。清水河校区分别刷新组顺序和组内人员顺序<br />
                            * 由于教室、人员变动， 加载过往排班时会删除当前不存在的教室、人员，并尽量匹配教室与组员。
                        </div>
                        <div class="card">
                            <h5 class="card-header">早自习排班</h5>
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
                                    <button class="btn btn-sm btn-info rounded-pill" data-bs-toggle="modal"
                                        data-bs-target="#select-saved-selfstudy-classroom"
                                        @click="loadImportDates">导入已有数据</button>
                                    <button class="btn btn-sm btn-warning rounded-pill" :disabled="isEditMode"
                                        @click="enterEditMode">编辑</button>
                                    <button class="btn btn-sm btn-success rounded-pill" :disabled="!isEditMode"
                                        @click="onSubmitClick">提交</button>
                                    <span v-if="currentDate" class="text-muted ms-2">
                                        当前显示日期：{{ currentDate }}
                                    </span>
                                </div>
                                <!-- 沙河校区 -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">沙河</h5>
                                        <div>
                                            <!-- 重置按钮已移除 -->
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill ms-2"
                                                :disabled="!isEditMode" @click="randomMember('沙河')">随机</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-hover table-striped mb-3 text-center">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>教室</th>
                                                    <th>备注</th>
                                                    <th>姓名</th>
                                                    <th>学号</th>
                                                    <th>组</th>
                                                    <th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(row, idx) in shaheList" :key="row.schedule_id">
                                                    <td>{{ idx + 1 }}</td>
                                                    <td>{{ row.classroom_name }}</td>
                                                    <td>{{ row.remark }}</td>
                                                    <td>{{ row.name }}</td>
                                                    <td>
                                                        <template v-if="isEditMode">
                                                            <input class="form-control form-control-sm"
                                                                v-model="row.student_id" />
                                                        </template>
                                                        <template v-else>{{ row.student_id }}</template>
                                                    </td>
                                                    <td>{{ row.group_name }}</td>
                                                    <td>
                                                        <button v-if="isEditMode"
                                                            class="btn btn-danger btn-sm rounded-pill"
                                                            @click="deleteRow('沙河', idx)">删除</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- 清水河校区 -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">清水河</h5>
                                        <div>
                                            <!-- 重置按钮已移除 -->
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill ms-2"
                                                :disabled="!isEditMode" @click="randomMember('清水河')">随机</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-hover table-striped mb-3 text-center">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>教室</th>
                                                    <th>备注</th>
                                                    <th>姓名</th>
                                                    <th>学号</th>
                                                    <th>组</th>
                                                    <th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(row, idx) in qingshuiheList" :key="row.schedule_id">
                                                    <td>{{ idx + 1 }}</td>
                                                    <td>{{ row.classroom_name }}</td>
                                                    <td>{{ row.remark }}</td>
                                                    <td>{{ row.name }}</td>
                                                    <td>
                                                        <template v-if="isEditMode">
                                                            <input class="form-control form-control-sm"
                                                                v-model="row.student_id" />
                                                        </template>
                                                        <template v-else>{{ row.student_id }}</template>
                                                    </td>
                                                    <td>{{ row.group_name }}</td>
                                                    <td>
                                                        <button v-if="isEditMode"
                                                            class="btn btn-danger btn-sm rounded-pill"
                                                            @click="deleteRow('清水河', idx)">删除</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- 未分配队员 -->
                                <div v-if="unassignedList && unassignedList.length > 0">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">未分配队员</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-hover table-striped mb-3 text-center">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>姓名</th>
                                                    <th>学号</th>
                                                    <th>组</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(person, idx) in unassignedList" :key="person.student_id">
                                                    <td>{{ idx + 1 }}</td>
                                                    <td> {{ person.name }} </td>
                                                    <td> {{ person.student_id }} </td>
                                                    <td> {{ person.group_name }} </td>
                                                </tr>
                                            </tbody>
                                        </table>
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

<style scoped>
.table-responsive {
    margin-bottom: 1rem;
}

.table th {
    white-space: nowrap;
}

.btn-group {
    gap: 0.5rem;
}
</style>
