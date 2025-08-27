<script setup>
import { ref } from "vue";
import axios from "axios";

// 表单数据
const name = ref("");
const studentID = ref("");
const school = ref("");
const hometown = ref("");

// 重置密码函数
const resetPassword = async () => {
    // 构造请求数据
    const resetInfo = {
        Name: name.value.trim(),
        StudentID: studentID.value.trim(),
        School: school.value.trim(),
        Hometown: hometown.value.trim()
    };

    try {
        const response = await axios.post("/Ajax/Users/resetPassword", resetInfo);

        const data = response.data;
        const returnCode = data.code;

        switch (returnCode) {
            case 400:
                swal({ title: "参数错误，请联系管理员", icon: "warning" });
                break;
            case 401:
                swal({
                    title: "信息错误",
                    text: "提供的的信息不匹配，请确认输入，留意大小写以及输入前后的空格",
                    icon: "error"
                });
                break;
            case 404:
                swal({ title: "功能不存在，请联系管理员", icon: "warning" });
                break;
            case 417:
                swal({ title: "功能错误，请联系管理员", icon: "warning" });
                break;
            case 498:
                swal({ title: "数据库异常，请联系管理员", icon: "warning" });
                break;
            case 499:
                swal({ title: "功能维护中，暂不允许重置密码", icon: "warning" });
                break;
            case 200:
            case 301:
                if (returnCode === 301) {
                    window.console.log('重置密码函数移至新位置');
                }
                swal({
                    title: "重置成功",
                    text: "密码已改为学号，建议登录后重新设置密码",
                    icon: "success"
                }).then(() => {
                    // 跳转新页面
                    window.location.href = data.data; // 用window.location.href比较通用
                });
                break;
            default:
                swal({ title: "未知错误", icon: "warning" });
                break;
        }
    } catch (error) {
        swal({ title: "请检查浏览器网络连接，建议刷新后重试", icon: "error" });
    }
};
</script>

<template>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <!-- Forgot Password -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="/index.html" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <img src="/imgs/STSA_small.png" alt="logo" />
                                </span>
                                <span class="app-brand-text demo text-body fw-bolder">学风督导队</span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-2">忘记密码？</h4>
                        <p class="mb-4">下方信息和个人信息页内容保持一致即可重置密码为学号。</p>
                        <form class="mb-3" @submit.prevent="resetPassword">
                            <div class="mb-3">
                                <label for="name" class="form-label">姓名</label>
                                <input type="text" class="form-control" id="name" v-model="name" name="name" autofocus
                                    required />
                            </div>
                            <div class="mb-3">
                                <label for="id" class="form-label">学号</label>
                                <input type="text" class="form-control" id="id" v-model="studentID" name="id"
                                    required />
                            </div>
                            <div class="mb-3">
                                <label for="school" class="form-label">学院</label>
                                <input type="text" class="form-control" id="school" v-model="school" name="school"
                                    required />
                            </div>
                            <div class="mb-3">
                                <label for="hometown" class="form-label">籍贯</label>
                                <input type="text" class="form-control" id="hometown" v-model="hometown" name="hometown"
                                    required />
                            </div>
                            <button class="btn btn-primary d-grid w-100" type="submit">重置</button>
                        </form>
                        <div class="text-center">
                            <a href="login.html" class="d-flex align-items-center justify-content-center">
                                <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                                回到登录页
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Forgot Password -->
            </div>
        </div>
    </div>
</template>

<style scoped></style>
