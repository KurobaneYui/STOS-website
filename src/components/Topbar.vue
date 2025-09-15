<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import "/src/assets/vendor/fonts/boxicons.css"

const userInfo = ref({
    name: '',
    department_id: 0,
    department_name: '',
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
        { text: '查早：XXX', type: 'success' },
        { text: '查课：XXX', type: 'warning' },
        // ...
    ]
}

async function logout() {
    const { data } = await axios.get('/Ajax/Users/logout')
    if (data.data === 'finished') {
        window.location.href = '/authentication/logout.html'
    }
}

onMounted(getTopbarInfo);
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
                <div class="col-auto" v-for="badge in badges" :key="badge.text">
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
                                    <span class="fw-semibold d-block">{{ userInfo.name }}</span>
                                    <small class="text-muted">{{ formalMember }}</small>
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
