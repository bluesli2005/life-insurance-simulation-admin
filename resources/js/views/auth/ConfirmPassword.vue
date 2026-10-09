<template>
    <AuthForm
        title="パスワードの再確認"
        description="続行するには、現在のパスワードを入力してください。"
        action="/password/confirm"
        submit-label="確認"
        :fields="fields"
        @success="goToAdmin"
    />
</template>

<script>
import AuthForm from '../../components/AuthForm.vue';

export default {
    components: { AuthForm },
    data() {
        return { fields: [{ name: 'password', label: 'パスワード', type: 'password', autocomplete: 'current-password' }] };
    },
    methods: {
        goToAdmin() {
            window.location.assign('/admin/applications');
        },
    },
};
</script>
