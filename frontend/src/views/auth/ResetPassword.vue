<template>
    <AuthForm
        title="新しいパスワードの設定"
        action="/auth/password/reset"
        submit-label="パスワードを更新"
        :fields="fields"
        @success="backToLogin"
    />
</template>

<script>
import AuthForm from '../../components/AuthForm.vue';

export default {
    components: { AuthForm },
    computed: {
        fields() {
            return [
                { name: 'token', type: 'hidden', value: this.$route.params.token },
                { name: 'email', label: 'メールアドレス', type: 'email', value: this.$route.query.email || '', autocomplete: 'email' },
                { name: 'password', label: '新しいパスワード', type: 'password', autocomplete: 'new-password' },
                { name: 'password_confirmation', label: '新しいパスワード（確認）', type: 'password', autocomplete: 'new-password' },
            ];
        },
    },
    methods: {
        backToLogin() {
            this.$router.push('/admin/applications');
        },
    },
};
</script>
