<script setup>
import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'

const currentPath = window.location.pathname

const userInfo = ref({
    name: '',
    departmentId: 0,
    departmentName: '',
    job: 'member'
})
const formalMember = ref('')
const badges = ref([])

async function getTopbarInfo() {
    try {
        const { data } = await axios.get('/Ajax/Users/topbarInfo')
        const code = data.code
        if ([400, 401, 404, 417, 498, 499].includes(code)) {
            if (data.msg) swal({ title: data.msg, icon: "warning" })
            else swal({ title: '出错了，如刷新无效请尝试重新登录', icon: 'error' })
            return
        }
        if (code === 200 || code === 301) {
            const info = data.data
            userInfo.value = info
            updateFormalMember(info)
            updateBadges(info)
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

function updateFormalMember(info) {
    if (info.department_id === 0) {
        formalMember.value = info.department_name
    } else if (info.department_id === 1) {
        formalMember.value = `${info.department_name} - ${info.job === "manager" ? '队长' : '副队长'}`
    } else {
        formalMember.value = `${info.department_name} - ${info.job === "manager" ? '组长' : '组员'}`
    }
}

function updateBadges(info) {
    badges.value = [
        { text: '查早：完成', type: 'success' },
        { text: '查课：未确认', type: 'warning' },
        // ...
    ]
}

const campusOptions = ref([
    { value: '', text: '请选择校区' }
])
const schoolOptions = ref([
    { value: '', text: '请选择学院' }
])

const form = ref({
    // 个人信息
    username: '',
    student_id: '',
    gender: 'male',
    ethnicity: '',
    hometown: '',
    // 联系方式
    phone: '',
    qq: '',
    campus: '',
    school: '',
    dormitory_yuan: '学知苑',
    dormitory_dong: '',
    dormitory_hao: '',
    // 工资信息
    application_bankcard: '',
    application_name: '',
    application_student_id: '',
    subsidyDossier: false,
    // 登录信息
    password: '',
    changePassword: false,
})

// 随机图片编号数组，假设页面有4张卡片图片
const randomImgs = ref([])
const cardImgCount = 4 // 页面卡片图片数量
const cardImgMax = 11
const cardImgMin = 0

function genRandomImgs() {
    randomImgs.value = []
    for (let i = 0; i < cardImgCount; i++) {
        let n = Math.floor(Math.random() * (cardImgMax - cardImgMin + 1) + cardImgMin)
        randomImgs.value.push(n)
    }
}

async function get_campus() {
    try {
        const { data } = await axios.get('/Ajax/DataManager/get_campus_for_form')
        const code = data.code
        if ([400, 401, 404, 417, 498, 499].includes(code)) {
            swal({
                title: data.message || "获取校区异常",
                icon: "warning"
            })
            campusOptions.value = [{ value: '', text: '请选择校区' }]
            return
        }
        // 200或301
        if (code === 301) console.log('获取校区信息函数移至新位置')
        campusOptions.value = [{ value: '', text: '请选择校区' }]
        if (Array.isArray(data.data)) {
            for (let i of data.data) {
                campusOptions.value.push({ value: i['campus'], text: i['campus'] })
            }
        }
    } catch (err) {
        swal({ title: "网络错误", text: "请检查浏览器网络连接，建议刷新后重试", icon: "error" })
        campusOptions.value = [{ value: '', text: '请选择校区' }]
    }
}

async function get_school() {
    try {
        const { data } = await axios.post('/Ajax/DataManager/get_school_for_form')
        const code = data.code
        if ([400, 401, 404, 417, 498, 499].includes(code)) {
            swal({
                title: data.message || "获取学院异常",
                icon: "warning"
            })
            schoolOptions.value = [{ value: '', text: '请选择学院' }]
            return
        }
        if (code === 301) console.log('获取学院信息函数移至新位置')
        schoolOptions.value = [{ value: '', text: '请选择学院' }]
        if (Array.isArray(data.data)) {
            for (let i of data.data) {
                schoolOptions.value.push({ value: i['name'], text: i['name'] })
            }
        }
    } catch (err) {
        swal({ title: "网络错误", text: "请检查浏览器网络连接，建议刷新后重试", icon: "error" })
        schoolOptions.value = [{ value: '', text: '请选择学院' }]
    }
}

async function get_personal_info() {
    try {
        const { data } = await axios.get('/Ajax/Users/get_personal_info')
        if (data.code === 200 || data.code === 301) {
            let d = data.data[0]
            form.value.username = d.name
            form.value.student_id = d.student_id
            form.value.gender = d.gender === '男' ? 'male' : 'female'
            form.value.ethnicity = d.ethnicity
            form.value.hometown = d.hometown

            form.value.phone = d.phone
            form.value.qq = d.qq
            form.value.campus = d.campus
            form.value.school = d.school
            form.value.dormitory_yuan = d.dormitory_yuan
            form.value.dormitory_dong = d.dormitory_dong
            form.value.dormitory_hao = d.dormitory_hao

            form.value.application_bankcard = d.application_bankcard
            form.value.application_name = d.application_name
            form.value.application_student_id = d.application_student_id
            form.value.subsidyDossier = !!d.subsidy_dossier
        } else {
            swal({ title: data.message || "获取个人信息错误", icon: "error" })
        }
    } catch (err) {
        swal({ title: "网络错误", text: "请检查浏览器网络连接，建议刷新后重试", icon: "error" })
    }
}

async function submitForm() {
    let payload = {
        // 个人信息
        name: form.value.username,
        gender: form.value.gender === 'male' ? "男" : "女",
        ethnicity: form.value.ethnicity,
        hometown: form.value.hometown,
        // 联系方式
        phone: form.value.phone,
        qq: form.value.qq,
        campus: form.value.campus,
        school: form.value.school,
        dormitory_yuan: form.value.dormitory_yuan,
        dormitory_dong: form.value.dormitory_dong,
        dormitory_hao: form.value.dormitory_hao,
        // 工资信息
        application_bankcard: form.value.application_bankcard,
        application_name: form.value.application_name,
        application_student_id: form.value.application_student_id,
        subsidyDossier: form.value.subsidyDossier,
    }
    if (form.value.changePassword && form.value.password) {
        payload.password = form.value.password
    }
    try {
        const { data } = await axios.post('/Ajax/Users/change_personal_info', payload)
        if (data.code === 200 || data.code === 301) {
            swal({ title: "修改成功", text: data.message, icon: "success" }).then(() => {
                form.value.password = ''
                form.value.changePassword = false
                get_personal_info() // 重新获取信息以同步显示
            })
        } else {
            swal({ title: data.message || "内容不符合要求", icon: "error" })
        }
    } catch (err) {
        swal({ title: "网络错误", text: "请检查浏览器网络连接，建议刷新后重试", icon: "error" })
    }
}

function onPasswordChangeSwitch(val) {
    if (!val) form.value.password = ''
}

const deleteConfirmText = ref('')

async function confirmDelete() {
    if (deleteConfirmText.value !== '我已知晓且确认注销账户') {
        return swal({
            title: "请确认",
            text: "如需注销账户，请填写确认文字！",
            icon: "error",
        });
    }

    try {
        const { data } = await axios.post('/Ajax/Users/delete_personal_info', { 'confirmDelete': 'confirm' })
        if (data.code === 200 || data.code === 301) {
            if (data.code === 301) {
                console.log('注销个人信息函数移至新位置');
            }
            swal({
                title: "注销成功",
                icon: "success",
            }).then(() => {
                window.location.href = "/index.html"
            });
        } else {
            swal({
                title: data.message || "操作失败",
                text: "请重试",
                icon: "error",
            });
        }
    } catch (err) {
        swal({ title: "网络错误", text: "请检查浏览器网络连接，建议刷新后重试", icon: "error" })
    }
}

onMounted(async () => {
    getTopbarInfo();
    genRandomImgs();
    await get_campus();
    await get_school();
    await get_personal_info();
})
</script>

<template>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <aside class="layout-menu menu-vertical menu bg-menu-theme">
                <Sidebar :current-path="currentPath" :user-info="userInfo" />
            </aside>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <nav
                    class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme rounded-pill">
                    <Topbar :user-info="userInfo" :formal-member="formalMember" :badges="badges" />
                </nav>
                <div>
                    <LoginWork />
                </div>

                <div class="content-wrapper">
                    <!-- Content -->


                    <div class="container-fluid flex-grow-1 container-p-y">
                        <!-- Breadcrumb -->
                        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                            aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="./index.html">个人中心</a></li>
                                <li class="breadcrumb-item active" aria-current="page">个人信息</li>
                            </ol>
                        </nav>
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-xxl-4">
                                <div class="card">
                                    <img class="card-img-top random-card-personInfoImg" type="image"
                                        :src="`/imgs/personInfoImg${randomImgs[0] ?? 0}.png`" alt="UESTC campus" />
                                    <div class="card-body">
                                        <h4 class="card-title">基本信息</h4>
                                        <div class="mb-3">
                                            <label for="username" class="form-label">姓名</label>
                                            <input type="text" class="form-control" v-model="form.username"
                                                name="username" autofocus required placeholder="请输入姓名" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="id" class="form-label">学号</label>
                                            <input type="text" class="form-control" v-model="form.student_id" id="id"
                                                name="id" placeholder="请输入学号" readonly />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">性别</label>
                                            <div class="row">
                                                <div class="form-check col-2 offset-1">
                                                    <label for="gender_male" class="form-check-label">男</label>
                                                    <input type="radio" class="form-check-input" v-model="form.gender"
                                                        id="gender_male" name="gender" value="male" />
                                                </div>
                                                <div class="form-check col-2">
                                                    <label for="gender_female" class="form-check-label">女</label>
                                                    <input type="radio" class="form-check-input" v-model="form.gender"
                                                        id="gender_female" name="gender" value="female" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ethnicity" class="form-label">民族</label>
                                            <input type="text" class="form-control" v-model="form.ethnicity"
                                                id="ethnicity" name="ethnicity" placeholder="请输入民族"
                                                aria-describedby="ethnicityHelp" required />
                                            <div id="ethnicityHelp" class="form-text">完整填写，如：填写“汉族”，而不是“汉”</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="hometown" class="form-label">籍贯</label>
                                            <input type="text" class="form-control" v-model="form.hometown"
                                                id="hometown" name="hometown" placeholder="请输入籍贯" required />
                                            <div id="ethnicityHelp" class="form-text">精确至：省市（县）</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xxl-4">
                                <div class="card">
                                    <img class="card-img-top random-card-personInfoImg"
                                        :src="`/imgs/personInfoImg${randomImgs[1] ?? 0}.png`" alt="UESTC campus" />
                                    <div class="card-body">
                                        <h4 class="card-title">工资信息</h4>
                                        <div class="mb-3">
                                            <label for="application_bankcard" class="form-label">银行卡号</label>
                                            <input type="text" class="form-control" v-model="form.application_bankcard"
                                                id="application_bankcard" name="application_bankcard"
                                                placeholder="请输入银行卡号" aria-describedby="bankHelp" required />
                                            <div id="bankHelp" class="form-text">建议学校的建行卡</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="application_name" class="form-label">领取人姓名</label>
                                            <input type="text" class="form-control" v-model="form.application_name"
                                                id="application_name" name="application_name" placeholder="请输入领取人姓名"
                                                required />
                                            <label for="application_student_id" class="form-label">领取人学号</label>
                                            <input type="text" class="form-control"
                                                v-model="form.application_student_id" id="application_student_id"
                                                name="application_student_id" placeholder="请输入领取人学号"
                                                aria-describedby="wage_idHelp" required />
                                            <div id="wage_idHelp" class="form-text">领工资的人的信息</div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <label for="subsidyDossier" class="form-check-label">建档立卡</label>
                                                <input type="checkbox" role="switch" class="form-check-input"
                                                    v-model="form.subsidyDossier" id="subsidyDossier"
                                                    name="subsidyDossier" aria-describedby="subsidyDossierHelp"
                                                    required />
                                                <div id="subsidyDossierHelp" class="form-text">
                                                    扶贫政策，如有办理建档立卡则选择。如没听说过，大概率不是，不用选择。不确定请联系辅导员询问。<span
                                                        class="fw-bold text-primary">数据会核实，请勿作假</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xxl-4">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6 col-xxl-12">
                                        <div class="card">
                                            <img class="card-img-top random-card-personInfoImg"
                                                :src="`/imgs/personInfoImg${randomImgs[2] ?? 0}.png`"
                                                alt="UESTC campus" />
                                            <div class="card-body">
                                                <h4 class="card-title">联系方式</h4>
                                                <div class="mb-3">
                                                    <label for="phone" class="form-label">电话</label>
                                                    <input type="tel" class="form-control" v-model="form.phone"
                                                        id="phone" name="phone" placeholder="请输入电话" required />
                                                </div>
                                                <div class="mb-3">
                                                    <label for="qq" class="form-label">QQ</label>
                                                    <input type="text" class="form-control" v-model="form.qq" id="qq"
                                                        name="qq" placeholder="请输入QQ号" required />
                                                </div>
                                                <div class="mb-3">
                                                    <label for="campus" class="form-label">校区</label>
                                                    <select id="campus" class="form-select" required
                                                        v-model="form.campus">
                                                        <option v-for="item in campusOptions" :key="item.value"
                                                            :value="item.value">{{ item.text
                                                            }}</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="school" class="form-label">学院</label>
                                                    <select id="school" class="form-select" required
                                                        v-model="form.school">
                                                        <option v-for="item in schoolOptions" :key="item.value"
                                                            :value="item.value">{{ item.text }}</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="dormitory" class="form-label">寝室</label>
                                                    <div id="dormitory" class="input-group">
                                                        <select id="dormitory_yuan"
                                                            class="form-control text-center ps-2 pe-2"
                                                            v-model="form.dormitory_yuan" style="min-width: 80px;"
                                                            aria-describedby="dormitoryHelp" required>
                                                            <option value="学知苑" selected>学知苑</option>
                                                            <option value="硕丰苑">硕丰苑</option>
                                                            <option value="校内">沙河校内</option>
                                                            <option value="校外">校外</option>
                                                        </select>
                                                        <span class="input-group-text ps-2 pe-2">苑</span>
                                                        <input id="dormitory_dong" v-model="form.dormitory_dong"
                                                            type="number" style="min-width: 30px;"
                                                            class="form-control text-center ps-2 pe-2" min="1" max="99"
                                                            placeholder="楼栋" aria-label="dong" required />
                                                        <span class="input-group-text ps-2 pe-2">栋</span>
                                                        <input id="dormitory_hao" v-model="form.dormitory_hao"
                                                            type="number" min="1" max="999"
                                                            class="form-control text-center ps-2 pe-2"
                                                            style="min-width: 30px;" placeholder="宿舍号" aria-label="hao"
                                                            required />
                                                        <span class="input-group-text ps-2 pe-2">号</span>
                                                    </div>
                                                    <div id="dormitoryHelp" class="form-text">第一空若为“校外”，则后两空填0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-xxl-12">
                                        <div class="card">
                                            <img class="card-img-top random-card-personInfoImg"
                                                :src="`/imgs/personInfoImg${randomImgs[3] ?? 0}.png`"
                                                alt="UESTC campus" />
                                            <div class="card-body">
                                                <h4 class="card-title">登录信息</h4>
                                                <div class="form-check form-switch">
                                                    <label for="passwordChange" class="form-check-label">修改密码</label>
                                                    <input type="checkbox" v-model="form.changePassword"
                                                        @change="onPasswordChangeSwitch(form.changePassword)"
                                                        role="switch" class="form-check-input" id="passwordChange"
                                                        name="passwordChange" aria-describedby="passwordChangeHelp"
                                                        required />
                                                    <div id="passwordChangeHelp" class="form-text">如需修改密码请开启，否则请关闭
                                                    </div>
                                                </div>
                                                <div class="mb-3 form-password-toggle">
                                                    <label class="form-label" for="password">登录密码</label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="password" v-model="form.password" id="password"
                                                            class="form-control" name="password"
                                                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                            :disabled="!form.changePassword"
                                                            aria-describedby="passwordHelp" />
                                                        <span class="input-group-text cursor-pointer"><i
                                                                class="bx bx-hide"></i></span>
                                                    </div>
                                                    <div id="passwordHelp" class="form-text">可由大小写字母、数字、或这些符号 ! # $
                                                        % & * + - / = ? ^ _ { | } ~ . [ ] 组成
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card col-6 offset-3 bg-transparent shadow-none">
                                <div class="card-body">
                                    <form id="changeInfo" @submit.prevent="submitForm">
                                        <div class="mb-3 d-flex justify-content-center">
                                            <a href="#" data-bs-toggle="modal"
                                                data-bs-target="#collectionNotice">信息收集说明</a>
                                            <div class="modal fade" id="collectionNotice">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="collectionNoticeModalTitle">
                                                                信息收集说明
                                                            </h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <dl>
                                                                <dt>全队联系方式公示：</dt>
                                                                <dd class="ms-3">姓名、性别、电话、QQ、所在部门和岗位</dd>
                                                                <dt>仅队长、组长可见信息：</dt>
                                                                <dd class="ms-3">此表所填所有信息</dd>
                                                                <dt>工资信息说明：</dt>
                                                                <dd class="ms-3">工资信息用于每月财务上报，注册后在个人信息页有更详细的内容需要提交。
                                                                    <span
                                                                        class="fw-bold text-primary">财务处和银行对接，需要核对姓名、学号和卡号，请仔细确认</span>
                                                                </dd>
                                                            </dl>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary d-grid w-100">确认</button>
                                    </form>
                                </div>
                            </div>
                            <!-- Register Card -->
                            <div class="row">
                                <div class="d-flex justify-content-center">
                                    <button type="button" class="btn btn-danger d-grid flex-shrink"
                                        data-bs-toggle="modal" data-bs-target="#destroyNotice">注销个人账户</button>
                                    <div class="modal fade" id="destroyNotice" data-bs-backdrop="static"
                                        data-bs-keyboard="false">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="destroyNoticeModalTitle">账户注销说明</h5>
                                                </div>
                                                <div class="modal-body">
                                                    <p>注销账户意味着：</p>
                                                    <ul>
                                                        <li>个人信息全部删除</li>
                                                        <li>个人工作岗位数据全部删除</li>
                                                        <li>从所在组内移除，不再纳入团队管理</li>
                                                        <li>需重新注册后使用，且原有数据不会恢复</li>
                                                        <li>已提交工作数据仍保留</li>
                                                    </ul>
                                                    <p class="fw-bold text-primary">
                                                        注意：为了保证已提交的工作数据可以朔源，我们会保留个人学号和姓名的记录，但其余信息不保留。</p>
                                                    <p class="fw-bold text-danger">确认注销请输入：我已知晓且确认注销账户</p>
                                                    <input type="text" class="form-control" id="deleteConfirm"
                                                        v-model="deleteConfirmText" required>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-danger"
                                                        @click="confirmDelete">确定删除</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal"
                                                        @click="deleteConfirmText = ''">取消</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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