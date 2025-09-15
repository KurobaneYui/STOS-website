<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';
import swal from 'sweetalert';
import * as bootstrap from 'bootstrap';
import Sidebar from './components/Sidebar.vue';
import Topbar from './components/Topbar.vue';
import LoginWork from './components/Loginwork.vue';
import "/src/assets/demo.css";

// --- Reactive State ---
const groups = ref({});
const searchResults = ref([]);
const searchQuery = ref('');
const currentGroup = ref({ id: null, name: '' });
const toastContainer = ref(null);
let addMemberModal = null;

// --- API and Data Handling ---
async function getAllGroupsMembers() {
    try {
        const response = await axios.get("/Ajax/GroupManager/get_all_groups_members");
        handleApiResponse(response.data, (data) => {
            groups.value = data;
        });
    } catch (error) {
        console.error("Network or request error:", error);
        swal({
            title: "网络错误",
            text: "请检查您的网络连接并重试。",
            icon: "error",
        });
    }
}

async function searchMembers() {
    if (!searchQuery.value.trim()) {
        swal({
            title: "请输入学号",
            icon: "warning",
        });
        return;
    }
    try {
        const response = await axios.post("/Ajax/GroupManager/search_member", {
            student_ids: searchQuery.value
        });
        handleApiResponse(response.data, (data) => {
            searchResults.value = data;
        });
    } catch (error) {
        console.error("Network or request error:", error);
        swal({
            title: "网络错误",
            text: "搜索成员时出错，请重试。",
            icon: "error",
        });
    }
}

async function addMember(student) {
    try {
        const response = await axios.post("/Ajax/GroupManager/add_member", {
            student_id: student.student_id,
            group_id: currentGroup.value.id
        });

        handleApiResponse(response.data, () => {
            showToast("success", "成功", `已添加组员 ${student.name}`);
            // Remove the added student from search results
            searchResults.value = searchResults.value.filter(s => s.student_id !== student.student_id);
        });
    } catch (error) {
        console.error("Network or request error:", error);
        swal({
            title: "网络错误",
            text: "添加成员时出错，请重试。",
            icon: "error",
        });
    }
}

async function removeMember(student, groupId) {
    try {
        const response = await axios.post("/Ajax/GroupManager/remove_member", {
            student_id: student.student_id,
            group_id: groupId
        });

        handleApiResponse(response.data, () => {
            showToast("success", "成功", `已删除组员 ${student.student_name}`);
            // Find the group and remove the member from the reactive state
            const group = groups.value[groupId];
            if (group) {
                group.members = group.members.filter(m => m.student_id !== student.student_id);
            }
        }, (message) => {
            showToast("error", "失败", message);
        });
    } catch (error) {
        console.error("Network or request error:", error);
        swal({
            title: "网络错误",
            text: "删除成员时出错，请重试。",
            icon: "error",
        });
    }
}

// --- Lifecycle Hooks ---
onMounted(() => {
    getAllGroupsMembers();

    // Initialize Bootstrap Modal
    const modalElement = document.getElementById('add-member');
    if (modalElement) {
        addMemberModal = new bootstrap.Modal(modalElement);
        // Add event listener to refresh data when modal closes
        modalElement.addEventListener('hidden.bs.modal', () => {
            getAllGroupsMembers();
        });
    }
});

// --- UI Helpers ---
function renderGender(gender) {
    if (gender === '男') return "<span class='badge bg-label-info'>男</span>";
    if (gender === "女") return "<span class='badge bg-label-danger'>女</span>";
    return "<span class='badge bg-label-dark'>-</span>";
}

function openAddMemberModal(groupId, groupName) {
    currentGroup.value = { id: groupId, name: groupName };
    searchQuery.value = '';
    searchResults.value = [];
    if (addMemberModal) {
        addMemberModal.show();
    }
}

function showToast(status, title, text) {
    const toastHtml = `<div class="bs-toast toast m-2 fade bg-${status === 'success' ? 'success' : 'danger'}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${status === 'success' ? '1500' : '2500'}">
            <div class="toast-header">
                <i class="bx bx-${status === 'success' ? 'check' : 'x'} me-2"></i>
                <div class="me-auto fw-semibold">${title}</div>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">${text}</div>
        </div>`;

    if (toastContainer.value) {
        const toastElement = document.createElement('div');
        toastElement.innerHTML = toastHtml;
        toastContainer.value.appendChild(toastElement.firstChild);

        const newToast = new bootstrap.Toast(toastContainer.value.lastChild);
        newToast.show();

        // Clean up the DOM after the toast is hidden
        toastContainer.value.lastChild.addEventListener('hidden.bs.toast', (e) => {
            e.target.remove();
        });
    }
}

// --- API Response Centralized Handler ---
function handleApiResponse(data, successCallback, failureCallback) {
    const returnCode = data['code'];
    const message = data['message'] || '';

    switch (returnCode) {
        case 200:
        case 301:
            if (returnCode === 301) console.log('Function has moved to a new location.');
            if (successCallback) successCallback(data['data']);
            break;
        case 400:
            if (failureCallback) {
                failureCallback(message || "提供的数据错误，请联系管理员");
            } else {
                swal({ title: "提供的数据错误，请联系管理员", icon: "error" });
            }
            break;
        case 401:
            swal({ title: "权限错误", text: message || "您没有执行此操作的权限。", icon: "error" });
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
            swal({ title: `功能维护中`, text: message, icon: "warning" });
            break;
        default:
            swal({ title: "未知错误", text: `错误码: ${returnCode}`, icon: "error" });
    }
}

</script>

<template>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <aside class="layout-menu menu-vertical menu bg-menu-theme">
                <Sidebar />
            </aside>
            <div class="layout-page">
                <nav
                    class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme rounded-pill">
                    <Topbar />
                </nav>
                <div>
                    <LoginWork />
                </div>

                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                            aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="./index.html">个人中心</a></li>
                                <li class="breadcrumb-item"><a href="./index.html">组内管理</a></li>
                                <li class="breadcrumb-item active" aria-current="page">组员增删</li>
                            </ol>
                        </nav>

                        <div class="modal fade" id="add-member" tabindex="-1" data-bs-backdrop="static"
                            data-bs-keyboard="false" aria-labelledby="add-member-label" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="add-member-label">{{ currentGroup.name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted">
                                            输入完整学号,可以用英文逗号（,）分隔多个。点击条目末尾的添加按钮弹出添加成功提示即可。添加结束则点击“X”或“结束”按钮。<br />
                                            <span
                                                class="fw-bold text-primary">注意：本页支持岗位兼任，即使输错学号检索到已在岗的成员亦可完成岗位添加，请认真确认姓名、学号和性别。</span>
                                        </p>
                                        <form @submit.prevent="searchMembers">
                                            <div class="mb-3">
                                                <label for="multi-id" class="col-form-label">学号：</label>
                                                <input type="text" class="form-control" id="multi-id"
                                                    v-model="searchQuery" aria-describedby="inputHelp"
                                                    @keyup.enter="searchMembers">
                                                <div id="inputHelp" class="form-text">完整学号，可用英文逗号分隔多个学号</div>
                                                <button type="button" class="btn btn-primary btn-sm rounded-pill mt-2"
                                                    @click="searchMembers">搜索</button>
                                            </div>
                                            <div class="table-responsive text-nowrap mb-3"
                                                v-if="searchResults.length > 0">
                                                <table class="table table-sm table-hover table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>姓名</th>
                                                            <th>性别</th>
                                                            <th>学号</th>
                                                            <th>操作</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="student in searchResults" :key="student.student_id">
                                                            <td>{{ student.name }}</td>
                                                            <td>{{ student.gender }}</td>
                                                            <td>{{ student.student_id }}</td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-success btn-sm rounded-pill"
                                                                    @click="addMember(student)">添加</button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-secondary rounded-pill"
                                            data-bs-dismiss="modal">结束</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div ref="toastContainer" aria-live="polite" aria-atomic="true"
                            class="position-fixed top-0 end-0 p-3" style="z-index: 1090"></div>

                        <div class="row g-3">
                            <div v-for="(groupData, groupId) in groups" :key="groupId"
                                class="col-12 col-lg-6 col-xxl-4">
                                <div class="card">
                                    <h5 class="card-header">{{ groupData.group_name }}</h5>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-hover table-striped mb-3">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>姓名</th>
                                                    <th>性别</th>
                                                    <th>学号</th>
                                                    <th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(member, index) in groupData.members"
                                                    :key="member.student_id">
                                                    <td>{{ index + 1 }}</td>
                                                    <td>{{ member.student_name }}</td>
                                                    <td v-html="renderGender(member.gender)"></td>
                                                    <td>{{ member.student_id }}</td>
                                                    <td>
                                                        <button class="btn btn-danger btn-sm rounded-pill"
                                                            @click="removeMember(member, groupId)">删除</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <button class="btn btn-sm btn-primary rounded-pill mb-3 ms-3"
                                            @click="openAddMemberModal(groupId, groupData.group_name)">
                                            添加
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <footer class="content-footer footer bg-footer-theme">
                        <div
                            class="container-fluid d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                &copy;
                                <span>{{ new Date().getFullYear() }}</span>
                                <a href="javascript:void(0);"
                                    class="footer-link fw-bolder">学工部学风督导队：罗寅松、赵创日、涂芷荇、张舒涵、谢骁巍</a>
                            </div>
                        </div>
                    </footer>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</template>