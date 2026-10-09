<template>
    <div class="admin-layout">
        <header class="admin-header">
            <h1>シミュレーション申込・管理</h1>
            <nav>
                <router-link to="/admin/applications">申込一覧</router-link>
                <router-link v-if="canManageUsers" to="/admin/users">権限管理</router-link>
                <router-link to="/admin/password">パスワード変更</router-link>
                <span>{{ roleLabel }}</span>
                <span>{{ userName }}</span>
                <button type="button" :disabled="loggingOut" @click="logout">ログアウト</button>
            </nav>
        </header>
        <main class="admin-main">
            <p v-if="logoutError" class="notice notice-error" role="alert">{{ logoutError }}</p>
            <router-view />
        </main>
    </div>
</template>

<script>
import auth from '../api/auth';
export default {
    data() {
        return { loggingOut: false, logoutError: '' };
    },
    computed: {
        userName() { return (this.$store.state.session || {}).user_name; },
        roleLabel() { return (this.$store.state.session || {}).role_name; },
        canManageUsers() { return (this.$store.state.session || {}).can_manage_users; },
    },
    methods: {
        async logout() {
            this.loggingOut = true;
            this.logoutError = '';
            try {
                await auth.logout();
                this.$store.commit('setSession', null);
                await auth.csrf();
                this.$router.push('/login');
            } catch (error) {
                this.logoutError = 'ログアウトできませんでした。再度お試しください。';
                this.loggingOut = false;
            }
        },
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
