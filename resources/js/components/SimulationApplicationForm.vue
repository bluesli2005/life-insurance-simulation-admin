<template>
    <form class="application-form" @submit.prevent="submit">
        <div class="form-grid">
            <div class="field-group">
                <BaseInput v-model="form.application_number" name="application_number" label="申込番号" :maxlength="50" required />
                <BaseErrorMessage :message="firstError('application_number')" />
            </div>
            <div class="field-group">
                <BaseInput v-model="form.applicant_name" name="applicant_name" label="申込者氏名" :maxlength="100" required autocomplete="name" />
                <BaseErrorMessage :message="firstError('applicant_name')" />
            </div>
            <div class="field-group">
                <BaseInput v-model="form.insured_name" name="insured_name" label="被保険者氏名" :maxlength="100" required />
                <BaseErrorMessage :message="firstError('insured_name')" />
            </div>
            <div class="field-group">
                <BaseInput v-model="form.insured_birth_date" name="insured_birth_date" label="被保険者生年月日" type="date" :max="today" required />
                <BaseErrorMessage :message="firstError('insured_birth_date')" />
            </div>
            <div class="field-group">
                <BaseInput v-model="form.beneficiary_name" name="beneficiary_name" label="受取人氏名" :maxlength="100" />
                <BaseErrorMessage :message="firstError('beneficiary_name')" />
            </div>
            <div class="field-group">
                <BaseInput v-model="form.coverage_amount" name="coverage_amount" label="保険金額" type="number" min="0.01" step="0.01" required />
                <BaseErrorMessage :message="firstError('coverage_amount')" />
            </div>
            <div class="field-group">
                <BaseInput v-model="form.premium_amount" name="premium_amount" label="保険料" type="number" min="0.01" step="0.01" required />
                <BaseErrorMessage :message="firstError('premium_amount')" />
            </div>
            <div class="field-group">
                <BaseInput v-model="form.currency" name="currency" label="通貨" :maxlength="3" pattern="[A-Z]{3}" required />
                <BaseErrorMessage :message="firstError('currency')" />
            </div>
            <div class="field-group">
                <BaseSelect v-model="form.status" name="status" label="ステータス" :options="statusOptions" required />
                <BaseErrorMessage :message="firstError('status')" />
            </div>
            <div class="field-group">
                <BaseInput v-model="form.effective_date" name="effective_date" label="適用開始日" type="date" required />
                <BaseErrorMessage :message="firstError('effective_date')" />
            </div>
            <div class="field-group">
                <BaseInput v-model="form.expiry_date" name="expiry_date" label="適用終了日" type="date" :min="form.effective_date || null" />
                <BaseErrorMessage :message="firstError('expiry_date')" />
            </div>
            <div class="field-group field-group-wide">
                <BaseTextarea v-model="form.notes" name="notes" label="備考" :maxlength="1000" />
                <BaseErrorMessage :message="firstError('notes')" />
            </div>
        </div>

        <div class="form-actions">
            <BaseButton type="submit" :disabled="loading">{{ loading ? '保存中…' : submitLabel }}</BaseButton>
            <router-link class="button button-secondary" to="/admin/applications">キャンセル</router-link>
        </div>
    </form>
</template>

<script>
import BaseButton from './BaseButton.vue';
import BaseInput from './BaseInput.vue';
import BaseSelect from './BaseSelect.vue';
import BaseTextarea from './BaseTextarea.vue';
import BaseErrorMessage from './BaseErrorMessage.vue';

export default {
    components: { BaseButton, BaseInput, BaseSelect, BaseTextarea, BaseErrorMessage },
    props: {
        initialValue: { type: Object, default: () => ({}) },
        errors: { type: Object, default: () => ({}) },
        loading: { type: Boolean, default: false },
        submitLabel: { type: String, default: '保存' },
    },
    data() {
        return {
            form: this.emptyForm(),
            statusOptions: [
                { value: 'draft', label: '下書き' },
                { value: 'submitted', label: '申込済み' },
                { value: 'approved', label: '承認済み' },
                { value: 'rejected', label: '却下' },
                { value: 'cancelled', label: '取消' },
            ],
        };
    },
    computed: {
        today() {
            return new Date().toISOString().slice(0, 10);
        },
    },
    watch: {
        initialValue: {
            immediate: true,
            handler(value) {
                this.form = Object.assign(this.emptyForm(), value || {});
            },
        },
    },
    methods: {
        emptyForm() {
            return {
                application_number: '',
                applicant_name: '',
                insured_name: '',
                insured_birth_date: '',
                beneficiary_name: '',
                coverage_amount: '',
                premium_amount: '',
                currency: 'JPY',
                status: 'draft',
                effective_date: this.todayValue(),
                expiry_date: '',
                notes: '',
            };
        },
        todayValue() {
            return new Date().toISOString().slice(0, 10);
        },
        firstError(field) {
            return this.errors[field] ? this.errors[field][0] : '';
        },
        submit() {
            this.$emit('submit', Object.assign({}, this.form));
        },
    },
};
</script>
