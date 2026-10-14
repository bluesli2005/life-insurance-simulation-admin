<template>
    <section class="page-section">
        <div class="page-heading"><h2>権限管理</h2></div>
        <p class="muted">新規登録ユーザーは閲覧者です。削除してもデータは残り、ログインできなくなります。</p>
        <p v-if="error" class="notice notice-error" role="alert">{{ error }}</p>
        <BaseToast :message="notice" @dismiss="notice = ''" />
        <BaseTable :columns="userColumns" :rows="users" row-key="id">
            <template slot="cell" slot-scope="{ row, column, value }">
                <BaseSelect
                    v-if="column.key === 'selectedRole'"
                    v-model="row.selectedRole"
                    :name="`user-role-${row.id}`"
                    :options="roleOptions"
                    :disabled="row.email === 'admin@example.com' || row.status === 'deleted'"
                />
                <span v-else-if="column.key === 'status'">{{ value === 'deleted' ? '削除済み' : '有効' }}</span>
                <span v-else>{{ value }}</span>
            </template>
            <template slot="actions" slot-scope="{ row }">
                <div class="heading-actions">
                    <button class="button button-primary" :disabled="saving === row.id || row.status === 'deleted' || row.selectedRole === row.role" @click="save(row)">保存</button>
                    <button v-if="canDeleteUsers && row.status === 'active' && row.id !== currentUserId && row.email !== 'admin@example.com'" class="button button-danger" :disabled="saving === row.id" @click="changeStatus(row, 'deleted')">削除</button>
                    <button v-if="canDeleteUsers && row.status === 'deleted'" class="button button-secondary" :disabled="saving === row.id" @click="changeStatus(row, 'active')">復元</button>
                </div>
            </template>
        </BaseTable>
        <BaseTable :columns="roleColumns" :rows="roles" row-key="code">
            <template slot="cell" slot-scope="{ column, value }">
                <span v-if="column.key === 'can_write_applications' || column.key === 'can_delete_applications' || column.key === 'can_manage_users'">{{ value ? '可' : '不可' }}</span>
                <span v-else>{{ value }}</span>
            </template>
        </BaseTable>
    </section>
</template>

<script>
import BaseToast from '../components/BaseToast.vue';
import BaseSelect from '../components/BaseSelect.vue';
import BaseTable from '../components/BaseTable.vue';
import client, { errorMessage } from '../api/client';
export default {
    components: { BaseSelect, BaseTable, BaseToast },
    computed: {
        roleOptions() { return this.roles.map(role => ({ value: role.code, label: role.name })); },
        currentUserId() { return (this.$store.state.session || {}).user_id; },
        canDeleteUsers() { return (this.$store.state.session || {}).role_code === 'super_admin'; },
    },
    data() {
        return {
            users: [],
            roles: [],
            saving: null,
            error: '',
            notice: '',
            userColumns: [
                { key: 'name', label: '氏名' },
                { key: 'email', label: 'メールアドレス' },
                { key: 'selectedRole', label: '権限' },
                { key: 'status', label: '状態' },
            ],
            roleColumns: [
                { key: 'name', label: '権限' },
                { key: 'can_write_applications', label: '申込の追加・編集' },
                { key: 'can_delete_applications', label: '申込の削除' },
                { key: 'can_manage_users', label: 'ユーザー権限の管理' },
            ],
        };
    },
    created() {
        this.load();
    },
    methods: {
        async load() {
            try {
                const response = await client.get('/users');
                // 選択中の権限と保存済みの権限を分け、未保存の変更を判定する。
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
