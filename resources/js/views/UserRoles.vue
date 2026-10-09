<template>
    <section class="page-section">
        <div class="page-heading"><h2>権限管理</h2></div>
        <p class="muted">新規登録ユーザーは閲覧者です。最高管理者のみ権限を変更できます。</p>
        <p v-if="error" class="notice notice-error" role="alert">{{ error }}</p>
        <p v-if="notice" class="notice" role="status">{{ notice }}</p>
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>氏名</th><th>メールアドレス</th><th>権限</th><th>操作</th></tr></thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>
                            <select v-model="user.selectedRole" :disabled="user.email === 'admin@example.com'">
                                <option v-for="role in roles" :key="role.code" :value="role.code">{{ role.name }}</option>
                            </select>
                        </td>
                        <td><button class="button button-primary" :disabled="saving === user.id || user.selectedRole === user.role" @click="save(user)">保存</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>権限</th><th>申込の追加・編集</th><th>申込の削除</th><th>ユーザー権限の管理</th></tr></thead>
                <tbody>
                    <tr v-for="role in roles" :key="role.code">
                        <td>{{ role.name }}</td>
                        <td>{{ role.can_write_applications ? '可' : '不可' }}</td>
                        <td>{{ role.can_delete_applications ? '可' : '不可' }}</td>
                        <td>{{ role.can_manage_users ? '可' : '不可' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<script>
export default {
    data() {
        return { users: [], roles: [], saving: null, error: '', notice: '' };
    },
    created() {
        this.load();
    },
    methods: {
        async load() {
            try {
                const response = await window.axios.get('/admin/api/v1/users');
                this.users = response.data.data.map(user => Object.assign({ selectedRole: user.role }, user));
                this.roles = response.data.roles;
            } catch (error) {
                this.error = 'ユーザー一覧を取得できませんでした。';
            }
        },
        async save(user) {
            this.saving = user.id;
            this.error = '';
            this.notice = '';
            try {
                const response = await window.axios.patch(`/admin/api/v1/users/${user.id}/role`, { role: user.selectedRole });
                user.role = response.data.data.role;
                this.notice = '権限を変更しました。';
            } catch (error) {
                this.error = error.response && error.response.data.message || '権限を変更できませんでした。';
            } finally {
                this.saving = null;
            }
        },
    },
};
</script>
