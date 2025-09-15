<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import axios from 'axios';
import "/src/assets/vendor/fonts/boxicons.css"
import PerfectScrollbar from 'perfect-scrollbar';
import "perfect-scrollbar/css/perfect-scrollbar.css";

const currentPath = window.location.pathname
const userInfo = ref({
    name: '',
    department_id: 0,
    department_name: '',
    chazao: false,
    chake: false,
    datamanager: false,
    job: 'member'
})

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
        }
    } catch (e) {
        swal({ title: '请检查网络连接，或稍后再试', icon: "error" })
    }
}

const workInfoSubMenuPaths = [
    '/user_center/work_basic_info.html',
    '/user_center/financial_report.html',
];
const isWorkInfoOpen = ref(false);

const dataEntrySubMenuPaths = [
    '/user_center/selfstudy_record.html',
    '/user_center/courses_record.html',
];
const isDataEntryOpen = ref(false);

const dataConfirmSubMenuPaths = [
    '/user_center/selfstudy_recheck.html',
    '/user_center/courses_recheck.html',
];
const isDataConfirmOpen = ref(false);

const adminDataSubMenuPaths = [
    '/user_center/data_export.html',
    '/user_center/classroom_edit.html',
    '/user_center/selfstudy_classroom_edit.html',
    '/user_center/selfstudy_schedule.html',
];
const isAdminDataOpen = ref(false);

const otherDataSubMenuPaths = [
    '/user_center/department_edit.html',
    '/user_center/school_edit.html',
    '/user_center/finance_export.html',
];
const isOtherDataOpen = ref(false);

const groupSubMenuPaths = [
    '/user_center/empty_time_edit.html',
    '/user_center/member_edit.html',
];
const isGroupOpen = ref(false);

const menuInner = ref(null)
let ps = null

const updatePerfectScrollbar = async () => {
    await nextTick()
    if (ps) ps.update()
}

// 监听子菜单 open 状态，展开/收起后更新滚动条
watch([isWorkInfoOpen, isDataEntryOpen, isDataConfirmOpen, isAdminDataOpen, isOtherDataOpen, isGroupOpen], updatePerfectScrollbar)

onMounted(() => {
    getTopbarInfo();
    // 保留并执行原有的子菜单初始展开逻辑
    if (workInfoSubMenuPaths.includes(currentPath)) {
        isWorkInfoOpen.value = true;
    }
    if (dataEntrySubMenuPaths.includes(currentPath)) {
        isDataEntryOpen.value = true;
    }
    if (dataConfirmSubMenuPaths.includes(currentPath)) {
        isDataConfirmOpen.value = true;
    }
    if (adminDataSubMenuPaths.includes(currentPath)) {
        isAdminDataOpen.value = true;
    }
    if (otherDataSubMenuPaths.includes(currentPath)) {
        isOtherDataOpen.value = true;
    }
    if (groupSubMenuPaths.includes(currentPath)) {
        isGroupOpen.value = true;
    }

    // 初始化 PerfectScrollbar
    if (menuInner.value) {
        ps = new PerfectScrollbar(menuInner.value, { suppressScrollX: true })
    }
    // 当窗口大小或子菜单展开状态变化时刷新滚动条
    window.addEventListener('resize', updatePerfectScrollbar)
})

onUnmounted(() => {
    if (ps) {
        ps.destroy()
        ps = null
    }
    window.removeEventListener('resize', updatePerfectScrollbar)
})
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

    <ul ref="menuInner" class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item" :class="{ 'active': currentPath === '/user_center/index.html' }">
            <a href="/user_center/index.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="首页">首页</div>
            </a>
        </li>

        <li v-if="userInfo.department_id !== 0" class="menu-item"
            :class="{ 'active': currentPath === '/user_center/contact.html' }">
            <a href="/user_center/contact.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-phone"></i>
                <div data-i18n="通讯录">通讯录</div>
            </a>
        </li>

        <li v-if="userInfo.department_id == 1 || userInfo.job == 'manager'" class="menu-item"
            :class="{ 'active': currentPath === '/user_center/blacklist.html' }">
            <a href="/user_center/blacklist.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-upside-down"></i>
                <div data-i18n="清退记录">清退记录</div>
            </a>
        </li>

        <!-- 工作信息 -->
        <li v-if="userInfo.department_id !== 0" class="menu-header small text-uppercase">
            <span class="menu-header-text">工作信息</span>
        </li>
        <li v-if="userInfo.department_id !== 0" class="menu-item"
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
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/financial_report.html' }">
                    <a href="#" class="menu-link text-decoration-line-through">
                        <div data-i18n="财务表">财务表</div>
                    </a>
                </li>
            </ul>
        </li>

        <li v-if="userInfo.job === 'member' && (userInfo.chazao || userInfo.chake)" class="menu-item"
            :class="{ 'active': currentPath === '/user_center/recent_task.html' }">
            <a href="/user_center/recent_task.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-collection"></i>
                <div data-i18n="近期任务总览">近期任务总览</div>
            </a>
        </li>

        <!-- 任务数据 -->
        <li v-if="userInfo.department_id == 1 || userInfo.chazao || userInfo.chake || userInfo.datamanager"
            class="menu-header small text-uppercase">
            <span class="menu-header-text">任务数据</span>
        </li>

        <li v-if="(userInfo.chazao || userInfo.chake) && userInfo.job == 'member'" class="menu-item"
            :class="{ 'active': dataEntrySubMenuPaths.includes(currentPath), 'open': isDataEntryOpen }">
            <a href="javascript:void(0);" class="menu-link menu-toggle" @click="isDataEntryOpen = !isDataEntryOpen">
                <i class="menu-icon tf-icons bx bx-notepad"></i>
                <div data-i18n="数据填写">数据填写</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/selfstudy_record.html' }">
                    <a href="/user_center/selfstudy_record.html" class="menu-link">
                        <div data-i18n="查早">查早</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/courses_record.html' }">
                    <a href="/user_center/courses_record.html" class="menu-link">
                        <div data-i18n="查课">查课</div>
                    </a>
                </li>
            </ul>
        </li>
        <li v-if="userInfo.department_id == 1 || (userInfo.job == 'manager' && (userInfo.chazao || userInfo.chake))"
            class="menu-item"
            :class="{ 'active': dataConfirmSubMenuPaths.includes(currentPath), 'open': isDataConfirmOpen }"
            auth_require="x1">
            <a href="javascript:void(0);" class="menu-link menu-toggle" @click="isDataConfirmOpen = !isDataConfirmOpen">
                <i class="menu-icon tf-icons bx bx-task"></i>
                <div data-i18n="数据确认">数据确认</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/selfstudy_recheck.html' }">
                    <a href="/user_center/selfstudy_recheck.html" class="menu-link">
                        <div data-i18n="查早">查早</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/courses_recheck.html' }">
                    <a href="/user_center/courses_recheck.html" class="menu-link">
                        <div data-i18n="查课">查课</div>
                    </a>
                </li>
            </ul>
        </li>
        <li v-if="userInfo.datamanager" class="menu-item"
            :class="{ 'active': adminDataSubMenuPaths.includes(currentPath), 'open': isAdminDataOpen }"
            auth_require="01">
            <a href="javascript:void(0);" class="menu-link menu-toggle" @click="isAdminDataOpen = !isAdminDataOpen">
                <i class="menu-icon tf-icons bx bx-data"></i>
                <div data-i18n="后台数据管理">后台数据管理</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item text-decoration-line-through" :class="{ 'active': currentPath === '/???' }">
                    <a href="#" class="menu-link">
                        <div data-i18n="变更锁定时间">变更锁定时间</div>
                    </a>
                </li>
                <li class="menu-item text-decoration-line-through" :class="{ 'active': currentPath === '/???' }">
                    <a href="#" class="menu-link">
                        <div data-i18n="数据编辑">数据编辑</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/data_export.html' }">
                    <a href="/user_center/data_export.html" class="menu-link">
                        <div data-i18n="数据导出">数据导出</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/classroom_edit.html' }">
                    <a href="/user_center/classroom_edit.html" class="menu-link">
                        <div data-i18n="全校教室信息">全校教室信息</div>
                    </a>
                </li>
                <li class="menu-item"
                    :class="{ 'active': currentPath === '/user_center/selfstudy_classroom_edit.html' }">
                    <a href="/user_center/selfstudy_classroom_edit.html" class="menu-link">
                        <div data-i18n="早自习教室信息">早自习教室信息</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/selfstudy_schedule.html' }">
                    <a href="/user_center/selfstudy_schedule.html" class="menu-link">
                        <div data-i18n="早自习排班">早自习排班</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/???' }">
                    <a href="#" class="menu-link">
                        <div data-i18n="全校教学列表">全校教学列表</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/???' }">
                    <a href="#" class="menu-link">
                        <div data-i18n="查课排班">查课排班</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- 管理 -->
        <li v-if="userInfo.department_id == 1 || userInfo.job == 'manager'" class="menu-header small text-uppercase">
            <span class="menu-header-text">管理</span>
        </li>
        <li v-if="userInfo.department_id == 1 || userInfo.job == 'manager'" class="menu-item" auth_require="x1">
            <a href="#" class="menu-link text-decoration-line-through">
                <i class="menu-icon tf-icons bx bx-message-square-dots"></i>
                <div data-i18n="通知编辑">通知编辑</div>
            </a>
        </li>
        <li v-if="userInfo.department_id == 1 || userInfo.job == 'manager'" class="menu-item"
            :class="{ 'active': groupSubMenuPaths.includes(currentPath), 'open': isGroupOpen }" auth_require="x1">
            <a href="javascript:void(0);" class="menu-link menu-toggle" @click="isGroupOpen = !isGroupOpen">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div data-i18n="组内管理">组内管理</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/empty_time_edit.html' }">
                    <a href="/user_center/empty_time_edit.html" class="menu-link">
                        <div data-i18n="空课表变更">空课表变更</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/member_edit.html' }">
                    <a href="/user_center/member_edit.html" class="menu-link">
                        <div data-i18n="人员增删">人员增删</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link text-decoration-line-through">
                        <div data-i18n="财务变动">财务变动</div>
                    </a>
                </li>
            </ul>
        </li>
        <li v-if="userInfo.department_id == 1" class="menu-item"
            :class="{ 'active': otherDataSubMenuPaths.includes(currentPath), 'open': isOtherDataOpen }"
            auth_require="01">
            <a href="javascript:void(0);" class="menu-link menu-toggle" @click="isOtherDataOpen = !isOtherDataOpen">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                <div data-i18n="其他数据">其他数据</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/department_edit.html' }">
                    <a href="/user_center/department_edit.html" class="menu-link">
                        <div data-i18n="部门管理">部门管理</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/school_edit.html' }">
                    <a href="/user_center/school_edit.html" class="menu-link">
                        <div data-i18n="学院管理">学院管理</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/finance_export.html' }">
                    <a href="/user_center/finance_export.html" class="menu-link">
                        <div data-i18n="财务报表导出">财务报表导出</div>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</template>
