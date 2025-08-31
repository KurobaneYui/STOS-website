<script setup>
import { ref, onMounted } from 'vue'
import "/src/assets/vendor/fonts/boxicons.css"

const props = defineProps({
    currentPath: String,
    userInfo: {
        type: Object,
        required: true
    }
})

const workInfoSubMenuPaths = ['/user_center/work_basic_info.html', '/user_center/score_details.html', '/user_center/financial_report.html'];
const isWorkInfoOpen = ref(false);

onMounted(() => {
    if (workInfoSubMenuPaths.includes(props.currentPath)) {
        isWorkInfoOpen.value = true;
    }
});
</script>

<template>
    <div class="app-brand demo">
        <a href="/index.html" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="/imgs/STSA_small.png" alt="logo" />
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">学风督导队</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item" :class="{ 'active': currentPath === '/user_center/index.html' }">
            <a href="/user_center/index.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="首页">首页</div>
            </a>
        </li>

        <li v-if="userInfo.departmentId !== 0" class="menu-item"
            :class="{ 'active': currentPath === '/user_center/contact.html' }">
            <a href="/user_center/contact.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-phone"></i>
                <div data-i18n="通讯录">通讯录</div>
            </a>
        </li>

        <li v-if="userInfo.departmentId === 1" class="menu-item"
            :class="{ 'active': currentPath === '/user_center/blacklist.html' }">
            <a href="/user_center/blacklist.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-upside-down"></i>
                <div data-i18n="清退记录">清退记录</div>
            </a>
        </li>

        <!-- 工作信息 -->
        <li v-if="userInfo.departmentId !== 0" class="menu-header small text-uppercase">
            <span class="menu-header-text">工作信息</span>
        </li>
        <li v-if="userInfo.departmentId !== 0" class="menu-item"
            :class="{ 'active': workInfoSubMenuPaths.includes(currentPath), 'open': isWorkInfoOpen }">
            <a href="javascript:void(0);" class="menu-link menu-toggle" @click="isWorkInfoOpen = !isWorkInfoOpen">
                <i class="menu-icon tf-icons bx bx-briefcase"></i>
                <div data-i18n="岗位信息">岗位信息</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/work_basic_info.html' }">
                    <a href="/user_center/work_basic_info.html" class="menu-link">
                        <div data-i18n="基本信息">基本信息</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/score_details.html' }">
                    <a href="/user_center/score_details.html" class="menu-link">
                        <div data-i18n="扣分详情">扣分详情</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/financial_report.html' }">
                    <a href="#" class="menu-link text-decoration-line-through">
                        <div data-i18n="财务表">财务表</div>
                    </a>
                </li>
            </ul>
        </li>

        <li v-if="userInfo.job === 'member' && (userInfo.departmentName.includes('现场组') || userInfo.departmentName.includes('查课组') || userInfo.departmentName.includes('沙河组'))"
            class="menu-item" :class="{ 'active': currentPath === '/user_center/task_recent.html' }">
            <a href="/user_center/task_recent.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-collection"></i>
                <div data-i18n="近期任务总览">近期任务总览</div>
            </a>
        </li>

        <!-- 任务数据 -->
    </ul>
    <!-- <ul class="menu-inner py-1">
        <li class="menu-item" v-for="item in dashboard_menu_list" :key="item.title"
            :class="{ 'active': currentPath === item.link }">
            <a :href="item.link" class="menu-link">
                <i class="menu-icon tf-icons" :class="item.icon"></i>
                <div>{{ item.title }}</div>
            </a>
        </li>
    </ul> -->
</template>
