<template>
    <AuthForm
        title="メールアドレスの確認"
        description="登録したメールアドレスに確認リンクを再送します。"
        action="/email/resend"
        submit-label="確認メールを再送"
        success-message="確認メールを再送しました。メールをご確認ください。"
        :fields="[]"
        :links="links"
    />
</template>

<script>
import AuthForm from '../../components/AuthForm.vue';

export default {
    components: { AuthForm },
    data() {
        return { links: [{ to: '/login', label: 'ログインへ戻る' }] };
    },
};
</script>
