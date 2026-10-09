<template>
    <AuthForm
        title="パスワード再設定"
        description="登録済みのメールアドレスに再設定用リンクを送信します。"
        action="/auth/password/email"
        submit-label="再設定リンクを送信"
        success-message="再設定用リンクを送信しました。メールをご確認ください。"
        :fields="fields"
        :links="links"
    />
</template>

<script>
import AuthForm from '../../components/AuthForm.vue';

export default {
    components: { AuthForm },
    data() {
        return {
            fields: [{ name: 'email', label: 'メールアドレス', type: 'email', autocomplete: 'email' }],
            links: [{ to: '/login', label: 'ログインへ戻る' }],
        };
    },
};
</script>
