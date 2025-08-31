<script setup>
import { ref, onMounted } from "vue";
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'

// 用户输入
const studentId = ref("");
const password = ref("");
const loading = ref(false);
const loginError = ref("");

// 岗位选择弹窗相关
const workList = ref([]);
const selectWorkModal = ref(null); // modal的DOM
let bsModalInstance = null;

// 表单提交
const onSubmit = async () => {
    loginError.value = "";
    loading.value = true;
    try {
        const { data } = await axios.post("/Ajax/Users/login", {
            StudentID: studentId.value,
            Password: password.value,
        });
        const code = data.code;
        if (code === 400) {
            swal({ title: "参数错误，请联系管理员", icon: "warning" });
        } else if (code === 401) {
            swal({
                title: "登录错误",
                text: "用户名或密码错误，请确认输入，留意大小写与输入前后的空格",
                icon: "error",
            });
        } else if (code === 404) {
            swal({ title: "功能不存在，请联系管理员", icon: "warning" });
        } else if (code === 417) {
            swal({ title: "功能错误，请联系管理员", icon: "warning" });
        } else if (code === 498) {
            swal({ title: "数据库异常，请联系管理员", icon: "warning" });
        } else if (code === 499) {
            swal({ title: "功能维护中，暂不允许登录", icon: "warning" });
        } else if (code === 200 || code === 301) {
            if (code === 301) {
                window.console.log("登录函数移至新位置");
            }
            workList.value = data.data || [];
            showWorkModal();
        }
    } catch (e) {
        swal({ title: "请检查浏览器网络连接，建议刷新后重试", icon: "error" });
    } finally {
        loading.value = false;
    }
};

// 选择岗位后登录
const loginAsSpecifiedWork = async (department_id = 0, job = null) => {
    try {
        const { data } = await axios.post("/Ajax/Users/login_as_specified_work", {
            department_id: parseInt(department_id),
            job: job,
        });
        const code = data.code;
        if (code === 400) {
            swal({ title: "参数错误，请联系管理员", icon: "error" });
        } else if (code === 401) {
            swal({
                title: "登录错误",
                text: "请确保已经登录后重试。",
                icon: "error",
            });
        } else if (code === 404) {
            swal({ title: "功能不存在，请联系管理员", icon: "warning" });
        } else if (code === 417) {
            swal({ title: "功能错误，请联系管理员", icon: "warning" });
        } else if (code === 498) {
            swal({ title: "数据库异常，请联系管理员", icon: "warning" });
        } else if (code === 499) {
            swal({ title: "功能维护中，暂不允许登录", icon: "warning" });
        } else if (code === 200 || code === 301) {
            if (code === 301) {
                window.console.log("登录函数移至新位置");
            }
            window.location = data.data; // 跳转
        }
    } catch (e) {
        swal({ title: "请检查浏览器网络连接，建议刷新后重试", icon: "error" });
    }
};

// modal显示&隐藏
function showWorkModal() {
    if (!bsModalInstance && selectWorkModal.value) {
        // Bootstrap5 modal初始化
        bsModalInstance = new window.bootstrap.Modal(selectWorkModal.value, {
            backdrop: "static",
            keyboard: false,
        });
    }
    bsModalInstance && bsModalInstance.show();
}
function hideWorkModal() {
    bsModalInstance && bsModalInstance.hide();
}

// 可选操作实现（如注销）
function logout() {
    // 可补充具体注销逻辑
    hideWorkModal();
}

// 用于动态渲染按钮Pill内容
function getWorkPillContent(work) {
    if (work.department_id === 0) return work.name;
    return `${work.name} - ${work.display_title}`;
}

onMounted(() => {
    // 获取modal的DOM
    // ref在template已绑定ref="selectWorkModal"
});
</script>

<template>
    <!-- 岗位选择弹窗 -->
    <div class="modal fade" ref="selectWorkModal" id="select-login-work" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">选择登录岗位</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        @click="logout"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2" id="work-list-container">
                        <template v-if="workList.length === 0">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </template>
                        <template v-else>
                            <div v-for="work in workList" :key="work.department_id + '-' + work.job" class="col-auto">
                                <button class="btn btn-outline-primary rounded-pill"
                                    @click="loginAsSpecifiedWork(work.department_id, work.job)">
                                    {{ getWorkPillContent(work) }}
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 登录表单 -->
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <div class="card">
                    <div class="card-body">
                        <div class="app-brand justify-content-center">
                            <a href="/index.html" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <img src="/imgs/STSA_small.png" />
                                </span>
                                <span class="app-brand-text demo text-body fw-bolder">学风督导队</span>
                            </a>
                        </div>
                        <h4 class="mb-2">用户登录</h4>
                        <p class="mb-4">用户名为学号、工号</p>

                        <form class="mb-3" @submit.prevent="onSubmit">
                            <div class="mb-3">
                                <label class="form-label" for="studentId">用户名</label>
                                <input type="text" class="form-control" id="studentId" name="email-username"
                                    placeholder="学号或工号" v-model="studentId" required autofocus />
                            </div>
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">密码</label>
                                    <a href="forgot_password.html" tabindex="-1">
                                        <small>Forgot Password?</small>
                                    </a>
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="密码" aria-describedby="password" v-model="password" required />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit" :disabled="loading">
                                    <span v-if="!loading">登录</span>
                                    <span v-else>登录中...</span>
                                </button>
                            </div>
                            <div v-if="loginError" class="alert alert-danger">
                                {{ loginError }}
                            </div>
                        </form>

                        <p class="text-center">
                            <span>还没有账号？</span>
                            <a href="register.html">
                                <span>注册</span>
                            </a>
                        </p>
                        <p class="text-center text-muted m-0">
                            已登录账号在无操作<span class="text-primary">1小时</span>后过期
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
