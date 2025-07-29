<script setup>
</script>

<template>
    <!-- Content -->
    <div class="modal fade" id="select-login-work" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">选择登录岗位</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        onclick="logout()"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2" id="work-list-container">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Register -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="/index.html" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <image src="/imgs/STSA_small.png" />
                                </span>
                                <span class="app-brand-text demo text-body fw-bolder">学风督导队</span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-2">用户登录</h4>
                        <p class="mb-4">用户名为学号、工号</p>

                        <form id="loginform" class="mb-3">
                            <div class="mb-3">
                                <label for="text" class="form-label">用户名</label>
                                <input type="text" class="form-control" id="email" name="email-username"
                                    placeholder="学号或工号" autofocus />
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
                                        placeholder="密码" aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">登录</button>
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
                <!-- /Register -->
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>
