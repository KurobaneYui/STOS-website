<script setup>
</script>

<template>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div class="m-5">
        <div class="error-box">
            <div class="error-body text-center">
                <h1 class="text-uppercase">Wrong URL !</h1>
                <h3>请从登录页或个人中心首页访问网站相关功能！</h3>
                <img width="300px" src="/imgs/for404.jpg" alt="Not Found ~~" />
                <p class="text-muted m-t-30 m-b-30">如果您从收藏夹前来，请删除此收藏。</p>
                <a href="/index.html" class="btn btn-info btn-rounded waves-effect waves-light m-b-40 text-white">Back
                    to home</a>
            </div>
            <footer class="footer text-center">&copy;
                <script>
                    document.write(new Date().getFullYear());
                </script>
                学风督导队.</footer>
        </div>
    </div>
</template>

<style scoped>
</style>
