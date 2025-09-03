<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const workList = ref([])
const loading = ref(true)

async function getWorkList() {
    loading.value = true
    try {
        const { data } = await axios.get('/Ajax/Users/get_login_works')
        if (data.code === 200 || data.code === 301) {
            workList.value = data.data
        } else {
            swal({ title: "错误", text: data.msg || '获取岗位失败', icon: "error" })
        }
    } finally {
        loading.value = false
    }
}

async function loginAsSpecifiedWork(department_id, job) {
    try {
        const { data } = await axios.post("/Ajax/Users/login_as_specified_work", {
            department_id: parseInt(department_id),
            job: job
        })
        if (data.code === 200 || data.code === 301) {
            window.location.reload()
        } else {
            alert(data.msg || "登录失败")
        }
    } catch {
        alert('请检查浏览器网络连接，建议刷新后重试')
    }
}

function getJobName(department_id, job, name) {
    if (department_id === 1) return name + ' - ' + (job === "manager" ? "队长" : "副队长")
    if (department_id === 0) return name
    return name + ' - ' + (job === "manager" ? "组长" : "组员")
}

onMounted(getWorkList)
</script>

<template>
    <div class="modal fade" id="select-login-work" tabindex="10" data-bs-keyboard="false"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">选择登录岗位</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2" v-if="!loading">
                        <div class="col-auto" v-for="work in workList" :key="work.department_id + '-' + work.job">
                            <button class="btn btn-outline-primary rounded-pill"
                                @click="loginAsSpecifiedWork(work.department_id, work.job)">
                                {{ getJobName(work.department_id, work.job, work.name) }}
                            </button>
                        </div>
                    </div>
                    <div v-else>
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
