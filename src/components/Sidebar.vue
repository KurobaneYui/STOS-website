<script setup>
import { computed } from 'vue'
import "/src/assets/vendor/fonts/boxicons.css"

const props = defineProps({
    currentPath: String,
    userInfo: {
        type: Object,
        required: true
    }
})
const menuList = computed(() => [
    {
        title: '首页',
        icon: 'bx bx-home-circle',
        link: '/user_center/index.html',
        show: true
    },
    {
        title: '通讯录',
        icon: 'bx bx-phone',
        link: '/user_center/contact.html',
        show: props.userInfo.departmentId !== 0
    },
    {
        title: '清退记录',
        icon: 'bx bx-upside-down',
        link: '/user_center/blacklist.html',
        show: props.userInfo.departmentId === 1
    },
].filter(item => item.show))
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
        <li class="menu-item" v-for="item in menuList" :key="item.title"
            :class="{ 'active': currentPath === item.link }">
            <a :href="item.link" class="menu-link">
                <i class="menu-icon tf-icons" :class="item.icon"></i>
                <div>{{ item.title }}</div>
            </a>
        </li>
        <!-- 可继续模板化所有 group/menu/submenu，推荐用对象/数组数据结构手动展开，无需写死 -->
    </ul>
</template>
