<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

// ------ 表单数据 ------
const form = reactive({
    name: '',
    studentID: '',
    gender: 'male',
    ethnicity: '',
    hometown: '',
    phone: '',
    qq: '',
    campus: '',        // 校区，下拉
    school: '',        // 学院，下拉
    dormitory_yuan: '学知苑',
    dormitory_dong: '',
    dormitory_hao: '',
    bank: '',
    subsidyDossier: false,
    password: '',
    claimBox: false
})

const claimError = ref(false)
const campusOptions = ref([
    { value: '', text: '请选择校区' }
])
const schoolOptions = ref([
    { value: '', text: '请选择学院' }
])

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

onMounted(() => {
    get_campus()
    get_school()
})

// ---- 注册函数 ----
async function register() {
    claimError.value = false
    if (!form.claimBox) {
        claimError.value = true
        if (window.sweetAlert) sweetAlert("请阅读个人信息说明");
        else if (window.swal) swal({ title: "请阅读个人信息说明", icon: "info" });
        return
    }

    const register_info = {
        name: form.name,
        studentID: form.studentID,
        gender: form.gender === 'male' ? '男' : '女',
        ethnicity: form.ethnicity,
        hometown: form.hometown,
        phone: form.phone,
        qq: form.qq,
        campus: form.campus,
        school: form.school,
        dormitory_yuan: form.dormitory_yuan,
        dormitory_dong: String(form.dormitory_dong),
        dormitory_hao: String(form.dormitory_hao),
        bank: form.bank,
        subsidyDossier: form.subsidyDossier,
        password: form.password
    }

    try {
        const { data } = await axios.post('/Ajax/Users/register', register_info)
        let returnCode = data.code
        if (returnCode === 400) {
            swal({ title: "内容不符合要求", text: data.message, icon: "error" })
        } else if (returnCode === 401) {
            swal({ title: "注册错误", text: "提供的学号已存在，请检查！", icon: "error" })
        } else if (returnCode === 404) {
            swal({ title: "功能不存在，请联系管理员", icon: "warning" })
        } else if (returnCode === 417) {
            swal({ title: "功能错误，请联系管理员", icon: "warning" })
        } else if (returnCode === 498) {
            swal({ title: "数据库异常，请联系管理员", icon: "warning" })
        } else if (returnCode === 499) {
            swal({ title: "功能维护中，暂不允许注册", icon: "warning" })
        } else if (returnCode === 200 || returnCode === 301) {
            if (returnCode === 301) window.console.log('注册函数移至新位置')
            swal({
                title: "注册成功",
                text: "请使用学号和密码登录。",
                icon: "success"
            }).then(() => {
                window.location = data.data
            })
        }
    } catch (err) {
        swal({ title: "网络错误", text: "请检查浏览器网络连接，建议刷新后重试", icon: "error" })
    }
}

function onSubmit(e) {
    e.preventDefault()
    register()
}
</script>

<template>
    <div class="container-fluid">
        <div class="row mt-3 mb-3 g-3">
            <div class="col-12">
                <div class="card bg-transparent shadow-none">
                    <div class="card-body">
                        <div class="app-brand justify-content-center">
                            <a href="/index.html" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <image src="/imgs/STSA_small.png" />
                                </span>
                                <span class="app-brand-text demo text-body fw-bolder">学风督导队</span>
                            </a>
                        </div>
                        <p class="mt-3 mb-0 text-center">队员注册：填写下方信息完成注册，任何问题请联系本组组长、队长或数据组组长</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">基本信息</h4>
                        <div class="mb-3">
                            <label for="username" class="form-label">姓名</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="请输入姓名"
                                autofocus required v-model="form.name" />
                        </div>
                        <div class="mb-3">
                            <label for="id" class="form-label">学号</label>
                            <input type="text" class="form-control" id="id" name="id" placeholder="请输入学号" required
                                v-model="form.studentID" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">性别</label>
                            <div class="row">
                                <div class="form-check col-2 offset-1">
                                    <label for="gender_male" class="form-check-label">男</label>
                                    <input type="radio" class="form-check-input" id="gender_male" name="gender"
                                        value="male" v-model="form.gender" />
                                </div>
                                <div class="form-check col-2">
                                    <label for="gender_female" class="form-check-label">女</label>
                                    <input type="radio" class="form-check-input" id="gender_female" name="gender"
                                        value="female" v-model="form.gender" />
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="ethnicity" class="form-label">民族</label>
                            <input type="text" class="form-control" id="ethnicity" name="ethnicity" placeholder="请输入民族"
                                aria-describedby="ethnicityHelp" required v-model="form.ethnicity" />
                            <div id="ethnicityHelp" class="form-text">完整填写，如：填写“汉族”，而不是“汉”</div>
                        </div>
                        <div class="mb-3">
                            <label for="hometown" class="form-label">籍贯</label>
                            <input type="text" class="form-control" id="hometown" name="hometown" placeholder="请输入籍贯"
                                required v-model="form.hometown" />
                            <div id="ethnicityHelp" class="form-text">精确至：省市（县）</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">联系方式</h4>
                        <div class="mb-3">
                            <label for="phone" class="form-label">电话</label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="请输入电话" required
                                v-model="form.phone" />
                        </div>
                        <div class="mb-3">
                            <label for="qq" class="form-label">QQ</label>
                            <input type="text" class="form-control" id="qq" name="qq" placeholder="请输入QQ号" required
                                v-model="form.qq" />
                        </div>
                        <div class="mb-3">
                            <label for="campus" class="form-label">校区</label>
                            <select id="campus" class="form-select" required v-model="form.campus">
                                <option v-for="item in campusOptions" :key="item.value" :value="item.value">{{ item.text
                                }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="school" class="form-label">学院</label>
                            <select id="school" class="form-select" required v-model="form.school">
                                <option v-for="item in schoolOptions" :key="item.value" :value="item.value">{{ item.text
                                }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="dormitory" class="form-label">寝室</label>
                            <div id="dormitory" class="input-group">
                                <select id="dormitory_yuan" class="form-control" aria-describedby="dormitoryHelp"
                                    required v-model="form.dormitory_yuan">
                                    <option value="学知苑">学知苑</option>
                                    <option value="硕丰苑">硕丰苑</option>
                                    <option value="校内">沙河校内</option>
                                    <option value="校外">校外</option>
                                </select>
                                <span class="input-group-text">苑</span>
                                <input id="dormitory_dong" type="number" class="form-control" min="1" max="99"
                                    placeholder="楼栋" aria-label="dong" required v-model="form.dormitory_dong" />
                                <span class="input-group-text">栋</span>
                                <input id="dormitory_hao" type="number" class="form-control" min="1" max="999"
                                    placeholder="宿舍号" aria-label="hao" required v-model="form.dormitory_hao" />
                                <span class="input-group-text">号</span>
                            </div>
                            <div id="dormitoryHelp" class="form-text">第一空若为“校外”，则后两空填0</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6 col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">工资信息</h4>
                                <div class="mb-3">
                                    <label for="bank" class="form-label">银行卡号</label>
                                    <input type="text" class="form-control" id="bank" name="bank" placeholder="请输入银行卡号"
                                        aria-describedby="bankHelp" required v-model="form.bank" />
                                    <div id="bankHelp" class="form-text">建议学校的建行卡</div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <label for="subsidyDossier" class="form-check-label">建档立卡</label>
                                        <input type="checkbox" class="form-check-input" id="subsidyDossier"
                                            name="subsidyDossier" aria-describedby="subsidyDossierHelp" required
                                            v-model="form.subsidyDossier" />
                                        <div id="subsidyDossierHelp" class="form-text">
                                            扶贫政策，如有办理建档立卡则选择。如没听说过，大概率不是，不用选择。不确定请联系辅导员询问。
                                            <span class="fw-bold text-primary">数据会核实，请勿作假</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">登录信息</h4>
                                <div class="mb-3 form-password-toggle">
                                    <label class="form-label" for="password">登录密码</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="password" class="form-control" name="password"
                                            placeholder="请设置登录密码" aria-describedby="passwordHelp"
                                            v-model="form.password" />
                                        <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                    </div>
                                    <div id="passwordHelp" class="form-text">
                                        可由大小写字母、数字、或这些符号 ! # $ % & * + - / = ? ^ _ { | } ~ . [ ] 组成
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card col-6 offset-3 bg-transparent shadow-none">
                <div class="card-body">
                    <form id="register" @submit="onSubmit">
                        <div class="mb-3 d-flex justify-content-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms"
                                    v-model="form.claimBox" />
                                <label class="form-check-label" :for="'terms-conditions'"
                                    :style="{ color: claimError ? 'red' : '' }">
                                    阅读
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#collectionNotice">信息收集说明</a>
                                </label>
                            </div>
                            <div class="modal fade" id="collectionNotice" data-bs-backdrop="static"
                                data-bs-keyboard="false">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="backDropModalTitle">信息收集说明</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <dl>
                                                <dt>全队联系方式公示：</dt>
                                                <dd class="ms-3">姓名、性别、电话、QQ、所在部门和岗位</dd>
                                                <dt>仅队长、组长可见信息：</dt>
                                                <dd class="ms-3">此表所填所有信息</dd>
                                                <dt>工资信息说明：</dt>
                                                <dd class="ms-3">
                                                    工资信息用于每月财务上报，注册后在个人信息页有更详细的内容需要提交。
                                                    <span
                                                        class="fw-bold text-primary">财务处和银行对接，需要核对姓名、学号和卡号，请仔细确认</span>
                                                </dd>
                                            </dl>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                                                @click="form.claimBox = true">已知晓
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100">注册</button>
                        <p class="text-center mt-3">
                            <span>已有帐户？</span>
                            <a href="login.html"><span>直接登录吧</span></a>
                        </p>
                    </form>
                </div>
            </div>
            <!-- Register Card -->
        </div>
    </div>
</template>

<style scoped></style>
