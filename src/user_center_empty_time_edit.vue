<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'
import * as bootstrap from 'bootstrap'

import Sidebar from './components/Sidebar.vue'
import Topbar from './components/Topbar.vue'
import LoginWork from './components/Loginwork.vue'
import GreenPoint from "./components/icons/GreenPoint.vue"
import YellowPoint from "./components/icons/YellowPoint.vue"
import RedPoint from "./components/icons/RedPoint.vue"
import "/src/assets/demo.css"

const members = ref([]) // 所有成员及其空课数据
const weekTabs = ref({}) // tab 切换跟踪 { [student_id]: 'odd' | 'even' }

const weekDayOrder = ['mon', 'tue', 'wed', 'thu', 'fri']
const timePeriod = ['1-2', '3-4', '5-6', '7-8']

function getPointType(val, order, isEven) {
    // val: '0', '1', '2', '3'
    // 0: 单双都没空，1: 单周有空，2: 双周有空，3: 单双都空
    if (isEven) {
        return (val === "2" || val === "3") ? "green" : "red"
    } else {
        return (val === "1" || val === "3") ? "green" : "red"
    }
}

function getTableData(data, isEven) {
    // 4行, 5列
    let table = []
    for (let row = 0; row < timePeriod.length; ++row) {
        let rowData = []
        for (let col = 0; col < weekDayOrder.length; ++col) {
            let key = weekDayOrder[col]
            let pointType = getPointType(data[key][row], row, isEven)
            rowData.push(pointType)
        }
        table.push(rowData)
    }
    return table
}

// 切换空课点的处理
async function handleChangePoint(memberIdx, isEven, rowIndex, colIndex) {
    // memberIdx：在 members 的索引
    const member = members.value[memberIdx]
    const student_id = member.student_id
    const weekName = weekDayOrder[colIndex]
    const timePeriodOrder = rowIndex

    // “当前点”原来的状态
    let oldValue = member[weekName][rowIndex]
    let emptyOrNot // 1=有空，0=无空

    if (isEven) {
        // even周
        if (oldValue === "2") {
            // 2(仅双周空) => 0(都不空)
            member[weekName][rowIndex] = "0"
            emptyOrNot = false
        } else if (oldValue === "0") {
            // 0 => 2
            member[weekName][rowIndex] = "2"
            emptyOrNot = true
        } else if (oldValue === "3") {
            // 3 => 1
            member[weekName][rowIndex] = "1"
            emptyOrNot = false
        } else if (oldValue === "1") {
            // 1 => 3
            member[weekName][rowIndex] = "3"
            emptyOrNot = true
        }
    } else {
        // odd周
        if (oldValue === "1") {
            member[weekName][rowIndex] = "0";
            emptyOrNot = false
        } else if (oldValue === "0") {
            member[weekName][rowIndex] = "1";
            emptyOrNot = true
        } else if (oldValue === "3") {
            member[weekName][rowIndex] = "2";
            emptyOrNot = false
        } else if (oldValue === "2") {
            member[weekName][rowIndex] = "3";
            emptyOrNot = true
        }
    }

    // 立刻更新前端 UI（乐观更新），然后同步到服务端
    try {
        const resp = await axios.post('/Ajax/GroupManager/set_member_empty_table', {
            student_id,
            weekName,
            timePeriodOrder,
            evenOrNot: isEven,
            emptyOrNot
        });
        let returnCode = resp.data.code
        if (returnCode !== 200 && returnCode !== 301) {
            throw resp.data;
        }
        if (returnCode === 301) {
            window.console.log('修改空课表函数移至新位置')
        }
        // 若后端出错，可以按需回滚前端数据
    } catch (err) {
        // 撤销前端更改
        member[weekName][rowIndex] = oldValue
        let data = err.response?.data ?? err
        if (!data.code) {
            swal({ title:"网络异常", text: '', icon: "error" })
            return
        }
        switch (data.code) {
            case 400:
                swal({ title:"提供的数据错误，请联系管理员", text:data.message, icon:"error" });break
            case 401:
                swal({ title:"权限错误", text:"仅现场组组长可修改空课表。", icon:"error" });break
            case 404:
                swal({ title:"功能不存在，请联系管理员", icon:"warning" });break
            case 417:
                swal({ title:"功能错误，请联系管理员", icon:"warning" });break
            case 498:
                swal({ title:"数据库异常，请联系管理员", icon:"warning" });break
            case 499:
                swal({ title:"功能维护中，暂不允许修改空课表", icon:"warning" });break
            default:
                swal({ title:"未知错误", text:JSON.stringify(data), icon:"error" })
        }
    }
}

async function loadTable() {
    try {
        const resp = await axios.get('/Ajax/GroupManager/get_group_empty_table')
        let data = resp.data

        let code = data.code
        if (code !== 200 && code !== 301) { throw data }
        if (code === 301) window.console.log('查看组内空课表函数移至新位置')
        members.value = data.data
        // 初始化tab状态
        weekTabs.value = {}
        data.data.forEach(stu => {
            weekTabs.value[stu.student_id] = 'odd'
        })
    } catch (err) {
        let data = err.response?.data ?? err
        switch (data.code) {
            case 400:
                swal({ title:"提供的数据错误，请联系管理员", text:data.message, icon:"error" });break
            case 401:
                swal({ title:"权限错误", text:"仅现场组组长可查看组内空课表。", icon:"error" });break
            case 404:
                swal({ title:"功能不存在，请联系管理员", icon:"warning" });break
            case 417:
                swal({ title:"功能错误，请联系管理员", icon:"warning" });break
            case 498:
                swal({ title:"数据库异常，请联系管理员", icon:"warning" });break
            case 499:
                swal({ title:"功能维护中，暂不允许查看组内空课表", icon:"warning" });break
            default:
                swal({ title:"未知错误", text:JSON.stringify(data), icon:"error" })
        }
    }
}

onMounted(loadTable)

function handleTabClick(student_id, type) {
    weekTabs.value[student_id] = type
}
</script>

<template>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <!-- 侧栏 -->
        <aside class="layout-menu menu-vertical menu bg-menu-theme">
            <Sidebar />
        </aside>
        <div class="layout-page">
            <nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme rounded-pill">
                <Topbar />
            </nav>
            <div>
                <LoginWork />
            </div>
            <div class="content-wrapper">
                <div class="container-fluid flex-grow-1 container-p-y">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./index.html">个人中心</a></li>
                            <li class="breadcrumb-item"><a href="./index.html">组内管理</a></li>
                            <li class="breadcrumb-item active" aria-current="page">空课表变更</li>
                        </ol>
                    </nav>
                    <div class="col-12 alert alert-primary" role="alert">
                        * 点击圆点变更空课信息。<br />
                        * <GreenPoint style="height:15px;width:15px;" /> 为有空，<RedPoint style="height:15px;width:15px;" /> 为没空。
                    </div>
                    <div class="row g-2">

                        <div v-for="(member, i) in members" :key="member.student_id" class="col-12 col-md-6 col-xxl-4">
                            <div class="card">
                                <h5 class="card-header" :student_id="member.student_id">{{ member.student_name }}</h5>
                                <ul class="nav nav-pills ms-3" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link btn-sm"
                                            :class="{'active': weekTabs[member.student_id]==='odd'}"
                                            @click="handleTabClick(member.student_id, 'odd')"
                                            type="button"
                                            >单周</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link btn-sm"
                                            :class="{'active': weekTabs[member.student_id]==='even'}"
                                            @click="handleTabClick(member.student_id, 'even')"
                                            type="button"
                                            >双周</button>
                                    </li>
                                </ul>
                                <div class="tab-content p-1">
                                    <div
                                        class="tab-pane fade"
                                        :class="weekTabs[member.student_id]==='odd' ? 'show active' : ''"
                                    >
                                        <div class="table-responsive text-nowrap">
                                            <table class="table table-striped table-hover text-center">
                                                <thead>
                                                    <tr>
                                                        <th>时段</th>
                                                        <th v-for="d in weekDayOrder" :key="d">{{ d.replace('mon','周一').replace('tue','周二').replace('wed','周三').replace('thu','周四').replace('fri','周五')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="(rowName, rowIdx) in timePeriod" :key="rowName" :timePeriodOrder="rowIdx">
                                                    <td>{{ rowName }}</td>
                                                    <td v-for="(d, colIdx) in weekDayOrder" :key="d"
                                                        @click="handleChangePoint(i, false, rowIdx, colIdx)"
                                                        style="cursor:pointer"
                                                    >
                                                        <component
                                                            :is="getPointType(member[d][rowIdx], rowIdx, false) === 'green' ? GreenPoint : RedPoint"
                                                            style="height:15px;width:15px;"
                                                        />
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div
                                        class="tab-pane fade"
                                        :class="weekTabs[member.student_id]==='even' ? 'show active' : ''"
                                    >
                                        <div class="table-responsive text-nowrap">
                                            <table class="table table-striped table-hover text-center">
                                                <thead>
                                                    <tr>
                                                        <th>时段</th>
                                                        <th v-for="d in weekDayOrder" :key="d">{{ d.replace('mon','周一').replace('tue','周二').replace('wed','周三').replace('thu','周四').replace('fri','周五')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="(rowName, rowIdx) in timePeriod" :key="rowName" :timePeriodOrder="rowIdx">
                                                    <td>{{ rowName }}</td>
                                                    <td v-for="(d, colIdx) in weekDayOrder" :key="d"
                                                        @click="handleChangePoint(i, true, rowIdx, colIdx)"
                                                        style="cursor:pointer"
                                                    >
                                                        <component
                                                            :is="getPointType(member[d][rowIdx], rowIdx, true) === 'green' ? GreenPoint : RedPoint"
                                                            style="height:15px;width:15px;"
                                                        />
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- footer略 -->
            </div>
        </div>
    </div>
</div>
</template>
