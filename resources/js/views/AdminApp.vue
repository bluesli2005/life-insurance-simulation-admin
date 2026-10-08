<template>
    <div class="admin-layout">
        <header class="admin-header">
            <h1>シミュレーション申込・管理</h1>
            <nav><router-link to="/admin/applications">申込一覧</router-link></nav>
        </header>
        <main class="admin-main"><router-view /></main>
    </div>
</template>

<style scoped>
.admin-layout { min-height: 100vh; background: #f5f7fa; color: #253238; }
.admin-header { display: flex; justify-content: space-between; padding: 20px 32px; background: #fff; border-bottom: 1px solid #e5e7eb; }
.admin-header h1 { margin: 0; font-size: 20px; }
.admin-header a { color: #2563eb; text-decoration: none; }
.admin-main { max-width: 1120px; margin: 0 auto; padding: 32px; }
</style>
