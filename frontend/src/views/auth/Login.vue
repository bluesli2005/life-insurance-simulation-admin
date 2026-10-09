<template>
    <AuthForm
        title="管理画面ログイン"
        action="/login"
        submit-label="ログイン"
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
                { name: 'email', label: 'メールアドレス', type: 'email', autocomplete: 'username' },
                { name: 'password', label: 'パスワード', type: 'password', autocomplete: 'current-password' },
                { name: 'remember', label: 'ログイン状態を保持する', type: 'checkbox' },
            ],
            links: [
                { to: '/password/reset', label: 'パスワードをお忘れですか？' },
                { to: '/register', label: '新規登録' },
            ],
        };
    },
    methods: {
        goToAdmin() {
            window.location.assign('/admin/applications');
        },
    },
};
</script>
