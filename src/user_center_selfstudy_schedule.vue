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
                group_name: item.group_name,
                campus: item.campus
            }
        }
    })
    unassignedArr.forEach(item => {
        if (item.student_id) {
            dict[item.student_id] = {
                student_id: item.student_id,
                name: item.name,
                group_name: item.group_name,
                campus: item.campus
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
console.log(response.data)
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

// 使用上次按钮功能
async function useLastSchedule(campus) {
    if (!currentDate.value) {
        swal({ title: "请先选择日期！", icon: "warning" })
        return
    }
    try {
        const { data } = await axios.post('/Ajax/DataManager/last_schedule_on_date', {
            date: currentDate.value,
            campus: campus
        });
        if (data.code === 200) {
            const mapping = {};
            // 补充：同一学号仅分配一次，其余跳过
            const assignedStudentIdSetForCampus = new Set();
            // 取接口返回的分配表
            let classroomMapArr = [];
            if (campus === "清水河") classroomMapArr = data.data.qingshuihe || [];
            else classroomMapArr = data.data.shahe || [];

            // 把 [{cid:id},...] 转为 {cid:student_id,...}
            const classroomIdToStudentId = {};
            classroomMapArr.forEach(obj => {
                for (const cid in obj) {
                    // 找出该 student_id 是否已分配过
                    const sid = obj[cid];
                    if (sid && !assignedStudentIdSetForCampus.has(sid)) {
                        classroomIdToStudentId[cid] = sid;
                        assignedStudentIdSetForCampus.add(sid);
                    }
                }
            });

            // 获得当前表
            const listRef = campus === "清水河" ? qingshuiheList : shaheList;
            for (let row of listRef.value) {
                // 仅处理有classroom_id的行
                const cid = row.classroom_id;
                if (!cid) continue;
                // 若接口没有此教室映射 或学号无效，置空
                const sid = classroomIdToStudentId.hasOwnProperty(cid) ? classroomIdToStudentId[cid] : '';
                if (!sid || !(sid in sourceStudentDict.value)) {
                    row.student_id = '';
                    row.name = '';
                    row.group_name = '';
                } else {
                    row.student_id = sid;
                    row.name = sourceStudentDict.value[sid].name;
                    row.group_name = sourceStudentDict.value[sid].group_name;
                }
            }
        } else {
            errorAlert(data.code);
        }
    } catch {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" });
    }
}

// 洗牌算法
function shuffleArray(arr) {
    // 完全乱序复制
    for (let i = arr.length - 1; i > 0; i--) {
        let j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
}

// 随机打乱校区排班，本地实现
function randomMember(campus) {
    if (!currentDate.value) {
        swal({ title: "请先选择日期！", icon: "warning" });
        return;
    }
    // 指针
    const listRef = campus === "清水河" ? qingshuiheList : shaheList;
    // 深拷贝业务数据
    const table = listRef.value;

    // 找出现分配成员的行（有student_id），并统计每个学号出现次数
    const assignedRows = [];
    const sidCountMap = {}; // {student_id: 出现次数}
    table.forEach((row, idx) => {
        if (row.student_id) {
            assignedRows.push({
                idx,
                group_name: row.group_name,
                student_id: row.student_id
            });
            sidCountMap[row.student_id] = (sidCountMap[row.student_id] || 0) + 1;
        }
    });

    // 获取组顺序（有成员分配的行），不可重复
    const groupOrdered = [];
    assignedRows.forEach(r => {
        if (r.group_name && !groupOrdered.includes(r.group_name)) {
            groupOrdered.push(r.group_name);
        }
    });

    if (groupOrdered.length === 0) {
        swal({ title: "无可分配成员，无法轮替", icon: "warning" });
        return;
    }
    // 1. 轮替组顺序：第一个丢到最后，其余顺序前推
    if (groupOrdered.length > 1) {
        const first = groupOrdered.shift();
        groupOrdered.push(first);
    }

    // 2. 按新顺序，组名=>成员池（每个成员出现多次的话要多次入池）
    const groupToMembers = {};
    groupOrdered.forEach(g => { groupToMembers[g] = []; });
    assignedRows.forEach(r => {
        groupToMembers[r.group_name].push(r.student_id);
    });

    // 3. 对每组成员池乱序
    for (const g in groupToMembers) {
        shuffleArray(groupToMembers[g]);
    }

    // 4. 组成员依次安放到assignedRows，每个学号出现次数必须不变
    // 探索要填多少分配次数，先建全部要填的学号池：组1的全部打乱成员，组2的全部打乱成员...
    const fillStudentIDs = [];
    groupOrdered.forEach(g => {
        fillStudentIDs.push(...groupToMembers[g]);
    });

    // 学号出现次数不能改变，需要校验
    const fillSidCountMap = {};
    fillStudentIDs.forEach(sid => {
        fillSidCountMap[sid] = (fillSidCountMap[sid] || 0) + 1;
    });
    // 若数量不对，应不会出现，但保底…
    if (Object.keys(sidCountMap).length !== Object.keys(fillSidCountMap).length) {
        swal({ title: "分配成员异常，轮替失败", icon: "error" });
        return;
    }
    for (const sid in sidCountMap) {
        if (sidCountMap[sid] !== fillSidCountMap[sid]) {
            swal({ title: "分配成员异常，轮替失败", icon: "error" });
            return;
        }
    }

    // 重新分配填回assignedRows
    let ptr = 0;
    assignedRows.forEach(r => {
        const sid = fillStudentIDs[ptr++];
        r._new_sid = sid;
    });

    // 刷新table中实际的数据
    let fillIdx = 0;
    table.forEach((row, idx) => {
        if (row.student_id) {
            const sid = assignedRows[fillIdx]._new_sid;
            if (sid && sourceStudentDict.value[sid]) {
                row.student_id = sid;
                row.name = sourceStudentDict.value[sid].name;
                row.group_name = sourceStudentDict.value[sid].group_name;
            } else {
                row.student_id = '';
                row.name = '';
                row.group_name = '';
            }
            fillIdx++;
        }
    });

    swal({ title: "轮替排序成功", icon: "success" });
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
                            * 点击导入按钮导入教室和已提交排班<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;* "自习日期+提交于"表明日期已提交排班，导入时会带入排班数据<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;* "自习日期"表明日期仅有早自习安排，无查早排班，导入时仅导入教室信息<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;* "删除"可以清除错误提交的排班数据<br />
                            * 对于暂未排班的早自习，点击“使用上次”可以用过往最近一次的排班方案，并尽量匹配所有教室和成员。<br />
                            * 导入排班后点击"轮替"按钮打乱现有排班，分别轮替组顺序且打乱组内人员顺序<br />
                        </div>
                        <div class="card">
                            <h5 class="card-header">早自习排班</h5>
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
                                    <button class="btn btn-sm btn-info rounded-pill" data-bs-toggle="modal"
                                        data-bs-target="#select-saved-selfstudy-classroom"
                                        @click="loadImportDates">导入</button>
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
                                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill ms-2"
                                                :disabled="!isEditMode" @click="useLastSchedule('沙河')">使用上次</button>
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill ms-2"
                                                :disabled="!isEditMode" @click="randomMember('沙河')">轮替</button>
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
                                                            <input
                                                                class="form-control form-control-sm studentID-min-width"
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
                                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill ms-2"
                                                :disabled="!isEditMode" @click="useLastSchedule('清水河')">使用上次</button>
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill ms-2"
                                                :disabled="!isEditMode" @click="randomMember('清水河')">轮替</button>
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
                                                            <input
                                                                class="form-control form-control-sm studentID-min-width"
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
                                                    <th>校区</th>
                                                    <th>姓名</th>
                                                    <th>学号</th>
                                                    <th>组</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(person, idx) in unassignedList" :key="person.student_id">
                                                    <td>{{ idx + 1 }}</td>
                                                    <td v-html="renderCampus(person.campus)"></td>
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
.studentID-min-width {
    min-width: 100px;
}
</style>
