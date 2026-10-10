<template>
    <section class="page-section">
        <div class="page-heading"><h2>権限管理</h2></div>
        <p class="muted">新規登録ユーザーは閲覧者です。削除してもデータは残り、ログインできなくなります。</p>
        <p v-if="error" class="notice notice-error" role="alert">{{ error }}</p>
        <BaseToast :message="notice" @dismiss="notice = ''" />
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>氏名</th><th>メールアドレス</th><th>権限</th><th>状態</th><th>操作</th></tr></thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>
                            <BaseSelect
                                v-model="user.selectedRole"
                                :name="`user-role-${user.id}`"
                                :options="roleOptions"
                                :disabled="user.email === 'admin@example.com' || user.status === 'deleted'"
                            />
                        </td>
                        <td>{{ user.status === 'deleted' ? '削除済み' : '有効' }}</td>
                        <td>
                            <div class="heading-actions">
                                <button class="button button-primary" :disabled="saving === user.id || user.status === 'deleted' || user.selectedRole === user.role" @click="save(user)">保存</button>
                                <button v-if="canDeleteUsers && user.status === 'active' && user.id !== currentUserId && user.email !== 'admin@example.com'" class="button button-danger" :disabled="saving === user.id" @click="changeStatus(user, 'deleted')">削除</button>
                                <button v-if="canDeleteUsers && user.status === 'deleted'" class="button button-secondary" :disabled="saving === user.id" @click="changeStatus(user, 'active')">復元</button>
                            </div>
                        </td>
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
import BaseToast from '../components/BaseToast.vue';
import BaseSelect from '../components/BaseSelect.vue';
import client, { errorMessage } from '../api/client';
export default {
    components: { BaseSelect, BaseToast },
    computed: {
        roleOptions() { return this.roles.map(role => ({ value: role.code, label: role.name })); },
        currentUserId() { return (this.$store.state.session || {}).user_id; },
        canDeleteUsers() { return (this.$store.state.session || {}).role_code === 'super_admin'; },
    },
    data() {
        return { users: [], roles: [], saving: null, error: '', notice: '' };
    },
    created() {
        this.load();
    },
    methods: {
        async load() {
            try {
                const response = await client.get('/users');
                this.users = response.data.data.map(user => Object.assign({ selectedRole: user.role }, user));
                this.roles = response.data.roles;
            } catch (error) {
                this.error = errorMessage(error, 'ユーザー一覧を取得できませんでした。');
            }
        },
        async save(user) {
            this.saving = user.id;
            this.error = '';
            this.notice = '';
            try {
                const response = await client.patch(`/users/${user.id}/role`, { role: user.selectedRole });
                user.role = response.data.data.role;
                this.notice = '権限を変更しました。';
            } catch (error) {
                this.error = errorMessage(error, '権限を変更できませんでした。');
            } finally {
                this.saving = null;
            }
        },
        async changeStatus(user, status) {
            if (status === 'deleted' && !window.confirm('このユーザーを削除しますか？データは残り、ログインできなくなります。')) return;

            this.saving = user.id;
            this.error = '';
            this.notice = '';
            try {
                const response = await client.patch(`/users/${user.id}/status`, { status });
                user.status = response.data.data.status;
                this.notice = status === 'deleted' ? 'ユーザーを削除しました。' : 'ユーザーを復元しました。';
            } catch (error) {
                this.error = errorMessage(error, 'ユーザーの状態を変更できませんでした。');
            } finally {
                this.saving = null;
            }
        },
    },
};
</script>
