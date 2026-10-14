<template>
    <section class="application-form" aria-labelledby="confirmation-heading">
        <p id="confirmation-heading">以下の内容で登録します。内容をご確認ください。</p>
        <dl class="details-grid">
            <div><dt>申込番号</dt><dd>{{ application.application_number }}</dd></div>
            <div><dt>申込者氏名</dt><dd>{{ application.applicant_name }}</dd></div>
            <div><dt>被保険者氏名</dt><dd>{{ application.insured_name }}</dd></div>
            <div><dt>被保険者生年月日</dt><dd>{{ application.insured_birth_date }}</dd></div>
            <div><dt>受取人氏名</dt><dd>{{ application.beneficiary_name || '—' }}</dd></div>
            <div><dt>保険金額</dt><dd>{{ formatAmount(application.coverage_amount) }}</dd></div>
            <div><dt>保険料</dt><dd>{{ formatAmount(application.premium_amount) }}</dd></div>
            <div><dt>通貨</dt><dd>{{ application.currency }}</dd></div>
            <div><dt>ステータス</dt><dd>{{ statusLabel(application.status) }}</dd></div>
            <div><dt>適用開始日</dt><dd>{{ application.effective_date }}</dd></div>
            <div><dt>適用終了日</dt><dd>{{ application.expiry_date || '—' }}</dd></div>
            <div class="details-wide"><dt>備考</dt><dd class="notes-value">{{ application.notes || '—' }}</dd></div>
        </dl>
        <div class="form-actions">
            <BaseButton variant="secondary" :disabled="loading" @click="$emit('back')">戻る</BaseButton>
            <BaseButton :disabled="loading" @click="$emit('confirm', application)">
                {{ loading ? '登録中…' : '確認して登録' }}
            </BaseButton>
        </div>
    </section>
</template>

<script>
import BaseButton from './BaseButton.vue';

const statusLabels = {
    draft: '下書き',
    submitted: '申込済み',
    approved: '承認済み',
    rejected: '却下',
    cancelled: '取消',
};

export default {
    components: { BaseButton },
    props: {
        application: { type: Object, required: true },
        loading: { type: Boolean, default: false },
    },
    methods: {
        statusLabel(status) {
            return statusLabels[status] || status;
        },
        formatAmount(amount) {
            return `${Number(amount).toLocaleString('ja-JP')} ${this.application.currency}`;
        },
    },
};
</script>
