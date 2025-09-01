<script setup>
import { ref, onMounted } from 'vue'
import "/src/assets/vendor/fonts/boxicons.css"

const props = defineProps({
    currentPath: String,
    userInfo: {
        department_id: Number,
        department_name: String,
        job: String,
        name: String,
    }
})

const workInfoSubMenuPaths = [
    '/user_center/work_basic_info.html',
    '/user_center/score_details.html',
    '/user_center/financial_report.html',
];
const isWorkInfoOpen = ref(false);

const dataEntrySubMenuPaths = [
    '/user_center/selfstudy_record.html',
    '/user_center/courses_record.html',
];
const isDataEntryOpen = ref(false);

const dataConfirmSubMenuPaths = [
    '/user_center/selfstudy_record_recheck.html',
    '/user_center/courses_record_recheck.html',
];
const isDataConfirmOpen = ref(false);

const adminDataSubMenuPaths = [
    '/user_center/data_export.html',
    '/user_center/classroom_editor.html',
    '/user_center/selfstudy_classroom_editor.html',
    '/user_center/selfstudy_scheduler.html',
];
const isAdminDataOpen = ref(false);

const otherDataSubMenuPaths = [
    '/user_center/department_edit.html',
    '/user_center/school_edit.html',
    '/user_center/finance_EXCEL_export.html',
];
const isOtherDataOpen = ref(false);

// 新增：组内管理 子路径与 open 控制
const groupSubMenuPaths = [
    '/user_center/empty_time_editor.html',
    '/user_center/member_management.html',
];
const isGroupOpen = ref(false);

onMounted(() => {
    if (workInfoSubMenuPaths.includes(props.currentPath)) {
        isWorkInfoOpen.value = true;
    }
    if (dataEntrySubMenuPaths.includes(props.currentPath)) {
        isDataEntryOpen.value = true;
    }
    if (dataConfirmSubMenuPaths.includes(props.currentPath)) {
        isDataConfirmOpen.value = true;
    }
    if (adminDataSubMenuPaths.includes(props.currentPath)) {
        isAdminDataOpen.value = true;
    }
    if (otherDataSubMenuPaths.includes(props.currentPath)) {
        isOtherDataOpen.value = true;
    }
    if (groupSubMenuPaths.includes(props.currentPath)) {
        isGroupOpen.value = true;
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

        <li v-if="userInfo.department_id !== 0" class="menu-item"
            :class="{ 'active': currentPath === '/user_center/contact.html' }">
            <a href="/user_center/contact.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-phone"></i>
                <div data-i18n="通讯录">通讯录</div>
            </a>
        </li>

        <li v-if="userInfo.department_id === 1" class="menu-item"
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
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/score_details.html' }">
                    <a href="/user_center/score_details.html" class="menu-link text-decoration-line-through">
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

        <li v-if="userInfo.job === 'member' && (userInfo.department_name.includes('现场组') || userInfo.department_name.includes('查课组') || userInfo.department_name.includes('沙河组'))"
            class="menu-item" :class="{ 'active': currentPath === '/user_center/task_recent.html' }">
            <a href="/user_center/task_recent.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-collection"></i>
                <div data-i18n="近期任务总览">近期任务总览</div>
            </a>
        </li>

        <!-- 任务数据 -->
        <li v-if="userInfo.department_id == 1 || userInfo.department_name.includes('现场组') || userInfo.department_name.includes('查课组') || userInfo.department_name.includes('沙河组') || userInfo.department_name.includes('数据组')"
            class="menu-header small text-uppercase">
            <span class="menu-header-text">任务数据</span>
        </li>

        <li v-if="(userInfo.department_name.includes('现场组') || userInfo.department_name.includes('查课组') || userInfo.department_name.includes('沙河组')) && userInfo.job == 'member'"
            class="menu-item"
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
        <li v-if="userInfo.department_id == 1 || (userInfo.job == 'manager' && (userInfo.department_name.includes('现场组') || userInfo.department_name.includes('查课组') || userInfo.department_name.includes('沙河组')))"
            class="menu-item"
            :class="{ 'active': dataConfirmSubMenuPaths.includes(currentPath), 'open': isDataConfirmOpen }"
            auth_require="x1">
            <a href="javascript:void(0);" class="menu-link menu-toggle" @click="isDataConfirmOpen = !isDataConfirmOpen">
                <i class="menu-icon tf-icons bx bx-task"></i>
                <div data-i18n="数据确认">数据确认</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item"
                    :class="{ 'active': currentPath === '/user_center/selfstudy_record_recheck.html' }">
                    <a href="/user_center/selfstudy_record_recheck.html" class="menu-link">
                        <div data-i18n="查早">查早</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/courses_record_recheck.html' }">
                    <a href="/user_center/courses_record_recheck.html" class="menu-link">
                        <div data-i18n="查课">查课</div>
                    </a>
                </li>
            </ul>
        </li>
        <li v-if="userInfo.department_name.includes('数据组')" class="menu-item"
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
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/classroom_editor.html' }">
                    <a href="/user_center/classroom_editor.html" class="menu-link">
                        <div data-i18n="全校教室信息">全校教室信息</div>
                    </a>
                </li>
                <li class="menu-item"
                    :class="{ 'active': currentPath === '/user_center/selfstudy_classroom_editor.html' }">
                    <a href="/user_center/selfstudy_classroom_editor.html" class="menu-link">
                        <div data-i18n="早自习教室信息">早自习教室信息</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/selfstudy_scheduler.html' }">
                    <a href="/user_center/selfstudy_scheduler.html" class="menu-link">
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
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/empty_time_editor.html' }">
                    <a href="/user_center/empty_time_editor.html" class="menu-link">
                        <div data-i18n="空课表变更">空课表变更</div>
                    </a>
                </li>
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/member_management.html' }">
                    <a href="/user_center/member_management.html" class="menu-link">
                        <div data-i18n="人员增删">人员增删</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link text-decoration-line-through">
                        <div data-i18n="财务变动">财务变动</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link text-decoration-line-through">
                        <div data-i18n="组员分数变动">组员分数变动</div>
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
                <li class="menu-item" :class="{ 'active': currentPath === '/user_center/finance_EXCEL_export.html' }">
                    <a href="/user_center/finance_EXCEL_export.html" class="menu-link">
                        <div data-i18n="财务报表导出">财务报表导出</div>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</template>
