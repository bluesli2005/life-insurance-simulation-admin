<template>
    <div class="admin-layout">
        <header class="admin-header">
            <h1>シミュレーション申込・管理</h1>
            <div class="admin-account">
                <span>{{ roleLabel }}</span>
                <span>{{ userName }}</span>
                <button type="button" :disabled="loggingOut" @click="logout">ログアウト</button>
            </div>
        </header>
        <div class="admin-body">
            <aside class="admin-sidebar">
                <nav aria-label="管理メニュー">
                    <router-link to="/admin/applications" active-class="is-active">申込一覧</router-link>
                    <router-link v-if="canManageUsers" to="/admin/users" active-class="is-active">権限管理</router-link>
                    <router-link to="/admin/password" active-class="is-active">パスワード変更</router-link>
                </nav>
            </aside>
            <main class="admin-main">
                <p v-if="logoutError" class="notice notice-error" role="alert">{{ logoutError }}</p>
                <router-view />
            </main>
        </div>
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
.admin-header { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 20px 32px; background: #fff; border-bottom: 1px solid #e5e7eb; }
.admin-header h1 { margin: 0; font-size: 20px; }
.admin-account { display: flex; align-items: center; flex-wrap: wrap; gap: 20px; }
.admin-account button { border: 0; background: none; color: #2563eb; cursor: pointer; font: inherit; }
.admin-body { display: grid; grid-template-columns: 220px minmax(0, 1fr); min-height: calc(100vh - 69px); }
.admin-sidebar { padding: 24px 16px; background: #fff; border-right: 1px solid #e5e7eb; }
.admin-sidebar nav { display: grid; gap: 8px; }
.admin-sidebar a { padding: 12px 16px; border-left: 3px solid transparent; border-radius: 5px; color: #475569; text-decoration: none; }
.admin-sidebar a:hover { background: #f1f5f9; color: #1d4ed8; }
.admin-sidebar a.is-active { border-left-color: #2563eb; background: #eff6ff; color: #1d4ed8; font-weight: 600; }
.admin-sidebar a:focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }
.admin-main { width: 100%; min-width: 0; max-width: 1184px; margin: 0 auto; padding: 32px; }
@media (max-width: 720px) {
    .admin-header { flex-wrap: wrap; padding: 16px; }
    .admin-account { gap: 12px; }
    .admin-body { grid-template-columns: 136px minmax(0, 1fr); }
    .admin-sidebar { padding: 20px 8px; }
    .admin-sidebar a { padding: 12px 8px; font-size: 14px; }
    .admin-main { padding: 20px 16px; }
}
</style>
