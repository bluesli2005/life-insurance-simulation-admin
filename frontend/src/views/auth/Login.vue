<template>
    <AuthForm
        ref="authForm"
        title="管理画面ログイン"
        action="/auth/login"
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
        async goToAdmin() {
            try {
                await this.$router.push('/admin/applications');
            } catch (error) {
                // セッション確認で遷移が中断された場合、未処理の Promise にせず画面に通知する。
                if (this.$refs.authForm) {
                    this.$refs.authForm.error = 'ログイン後の画面を開けませんでした。再度お試しください。';
                }
            }
        },
    },
};
</script>
