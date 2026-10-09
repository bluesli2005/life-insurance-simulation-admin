<template>
    <section class="page-section">
        <div class="page-heading"><h2>パスワード変更</h2></div>
        <AuthForm
            title="現在のパスワードを確認"
            action="/admin/password"
            submit-label="パスワードを変更"
            success-message="パスワードを変更しました。"
            :fields="fields"
            :clear-on-success="true"
        />
    </section>
</template>

<script>
import AuthForm from '../components/AuthForm.vue';

export default {
    components: { AuthForm },
    data() {
        return {
            fields: [
                { name: 'current_password', label: '現在のパスワード', type: 'password', autocomplete: 'current-password' },
                { name: 'password', label: '新しいパスワード', type: 'password', autocomplete: 'new-password' },
                { name: 'password_confirmation', label: '新しいパスワード（確認）', type: 'password', autocomplete: 'new-password' },
            ],
        };
    },
};
</script>
