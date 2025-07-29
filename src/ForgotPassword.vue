<script setup>
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
                                    <image src="/imgs/STSA_small.png" />
                                </span>
                                <span class="app-brand-text demo text-body fw-bolder">学风督导队</span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-2">忘记密码？</h4>
                        <p class="mb-4">下方信息和个人信息页内容保持一致即可重置密码为学号。</p>
                        <form id="recoverform" class="mb-3">
                            <div class="mb-3">
                                <label for="name" class="form-label">姓名</label>
                                <input type="text" class="form-control" id="name" name="name" autofocus />
                            </div>
                            <div class="mb-3">
                                <label for="id" class="form-label">学号</label>
                                <input type="text" class="form-control" id="id" name="id" />
                            </div>
                            <div class="mb-3">
                                <label for="school" class="form-label">学院</label>
                                <input type="text" class="form-control" id="school" name="school" />
                            </div>
                            <div class="mb-3">
                                <label for="hometown" class="form-label">籍贯</label>
                                <input type="text" class="form-control" id="hometown" name="hometown" />
                            </div>
                            <button class="btn btn-primary d-grid w-100">重置</button>
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

<style scoped>
</style>
