<template>
    <div class="admin-layout">
        <header class="admin-header">
            <h1>シミュレーション申込・管理</h1>
            <nav>
                <router-link to="/admin/applications">申込一覧</router-link>
                <span>{{ userName }}</span>
                <form method="POST" action="/logout">
                    <input type="hidden" name="_token" :value="csrfToken">
                    <button type="submit">ログアウト</button>
                </form>
            </nav>
        </header>
        <main class="admin-main"><router-view /></main>
    </div>
</template>

<script>
export default {
    data() {
        return {
            userName: document.getElementById('app').dataset.userName,
            csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        };
    },
};
</script>

<style scoped>
.admin-layout { min-height: 100vh; background: #f5f7fa; color: #253238; }
.admin-header { display: flex; justify-content: space-between; padding: 20px 32px; background: #fff; border-bottom: 1px solid #e5e7eb; }
.admin-header h1 { margin: 0; font-size: 20px; }
.admin-header a { color: #2563eb; text-decoration: none; }
.admin-header nav { display: flex; align-items: center; gap: 20px; }
.admin-header button, .logout-form button { border: 0; background: none; color: #2563eb; cursor: pointer; font: inherit; }
.admin-main { max-width: 1120px; margin: 0 auto; padding: 32px; }
</style>
