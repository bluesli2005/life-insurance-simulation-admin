<template>
    <AuthForm
        title="管理者登録"
        action="/auth/register"
        submit-label="登録"
        :fields="fields"
        :links="links"
        @success="goToAdmin"
    />
</template>

<script>
import AuthForm from '../../components/AuthForm.vue';

export default {
    components: { AuthForm },
    data() {
        return {
            fields: [
                { name: 'name', label: '氏名', autocomplete: 'name' },
                { name: 'email', label: 'メールアドレス', type: 'email', autocomplete: 'email' },
                { name: 'password', label: 'パスワード', type: 'password', autocomplete: 'new-password' },
                { name: 'password_confirmation', label: 'パスワード（確認）', type: 'password', autocomplete: 'new-password' },
            ],
            links: [{ to: '/login', label: 'ログインへ戻る' }],
        };
    },
    methods: {
        goToAdmin() {
            this.$router.push('/admin/applications');
        },
    },
};
</script>
