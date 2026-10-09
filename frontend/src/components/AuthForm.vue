<template>
    <section class="login-card auth-card">
        <h1>{{ title }}</h1>
        <p v-if="description" class="auth-description">{{ description }}</p>
        <p v-if="status" class="login-status" role="status">{{ status }}</p>
        <p v-if="error" class="login-error" role="alert">{{ error }}</p>

        <form @submit.prevent="submit">
            <template v-for="field in fields">
                <input v-if="field.type === 'hidden'" :key="field.name" v-model="form[field.name]" type="hidden" :name="field.name">
                <label v-else-if="field.type === 'checkbox'" :key="field.name" class="remember-label">
                    <input v-model="form[field.name]" type="checkbox" :name="field.name" value="1">
                    {{ field.label }}
                </label>
                <label v-else :key="field.name" class="auth-field" :for="field.name">
                    <span>{{ field.label }}</span>
                    <input
                        :id="field.name"
                        v-model.trim="form[field.name]"
                        :type="field.type || 'text'"
                        :name="field.name"
                        :required="field.required !== false"
                        :autocomplete="field.autocomplete || 'off'"
                    >
                    <span v-if="errors[field.name]" class="auth-field-error">{{ errors[field.name][0] }}</span>
                </label>
            </template>
            <button type="submit" :disabled="processing">{{ processing ? '処理中…' : submitLabel }}</button>
        </form>

        <nav v-if="links.length" class="auth-links" aria-label="認証メニュー">
            <router-link v-for="link in links" :key="link.to" :to="link.to">{{ link.label }}</router-link>
        </nav>
    </section>
</template>

<script>
import client, { errorMessage } from '../api/client';

export default {
    props: {
        title: { type: String, required: true },
        description: { type: String, default: '' },
        action: { type: String, required: true },
        submitLabel: { type: String, required: true },
        fields: { type: Array, required: true },
        links: { type: Array, default: () => [] },
        successMessage: { type: String, default: '' },
        clearOnSuccess: { type: Boolean, default: false },
    },
    data() {
        const form = {};
        this.fields.forEach(field => { form[field.name] = field.value || (field.type === 'checkbox' ? false : ''); });
        return { form, errors: {}, error: '', status: '', processing: false };
    },
    methods: {
        async submit() {
            this.processing = true;
            this.errors = {};
            this.error = '';
            this.status = '';
            try {
                await client.post(this.action, this.form);
                if (this.successMessage) this.status = this.successMessage;
                if (this.clearOnSuccess) {
                    this.fields.forEach(field => { this.form[field.name] = field.type === 'checkbox' ? false : ''; });
                }
                this.$emit('success');
            } catch (error) {
                this.errors = error.response && error.response.data && error.response.data.errors
                    ? error.response.data.errors
                    : {};
                this.error = Object.keys(this.errors).length
                    ? ''
                    : errorMessage(error, '処理に失敗しました。入力内容をご確認ください。');
            } finally {
                this.processing = false;
            }
        },
    },
};
</script>
