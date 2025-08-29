<script setup>
import axios from 'axios'
import "/src/assets/vendor/fonts/boxicons.css"

const props = defineProps({
    userInfo: Object,
    formalMember: String,
    badges: Array
})

async function logout() {
    const { data } = await axios.get('/Ajax/Users/logout')
    if (data.data === 'finished') {
        window.location.href = '/authentication/logout.html'
    }
}
</script>

<template>
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center row g-1" id="self-status-badges">
                <div class="col-auto" v-for="badge in props.badges" :key="badge.text">
                    <span class="badge rounded-pill" :class="'bg-label-' + badge.type + ' fs-6'">{{ badge.text }}</span>
                </div>
            </div>
        </div>
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="/imgs/xyl.jpg" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#select-login-work">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="/imgs/xyl.jpg" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ props.userInfo.name }}</span>
                                    <small class="text-muted">{{ props.formalMember }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="/user_center/personal_info.html">
                            <i class="bx bx-id-card me-2"></i> <span class="align-middle">个人信息</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" target="_blank" href="#"><i class="bx bx-book me-2"></i><span
                                class="align-middle">督导队手册</span></a>
                    </li>
                    <li>
                        <a class="dropdown-item" target="_blank" href="#"><i class="bx bx-help-circle me-2"></i><span
                                class="align-middle">网站操作指南</span></a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" @click="logout">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</template>
