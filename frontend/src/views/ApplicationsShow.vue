<template>
    <section class="page-section">
        <ContentState :loading="loading" :error="error" retry @retry="loadApplication">
            <template v-if="application">
                <div class="page-heading">
                    <div>
                        <router-link class="back-link" to="/admin/applications">申込一覧へ戻る</router-link>
                        <h2>申込詳細</h2>
                        <p class="muted">{{ application.application_number }}</p>
                    </div>
                    <div class="heading-actions">
                        <router-link v-if="canEdit" class="button button-secondary" :to="`${basePath}/edit`">編集</router-link>
                        <BaseButton v-if="canDelete" variant="danger" :disabled="deleting" @click="deleteApplication">削除</BaseButton>
                    </div>
                </div>

                <dl class="details-grid">
                    <div><dt>申込者氏名</dt><dd>{{ application.applicant_name }}</dd></div>
                    <div><dt>被保険者氏名</dt><dd>{{ application.insured_name }}</dd></div>
                    <div><dt>被保険者生年月日</dt><dd>{{ application.insured_birth_date }}</dd></div>
                    <div><dt>受取人氏名</dt><dd>{{ application.beneficiary_name || '—' }}</dd></div>
                    <div><dt>保険金額</dt><dd>{{ formatAmount(application.coverage_amount) }}</dd></div>
                    <div><dt>保険料</dt><dd>{{ formatAmount(application.premium_amount) }}</dd></div>
                    <div><dt>ステータス</dt><dd><span :class="['status-badge', `status-${application.status}`]">{{ statusLabel(application.status) }}</span></dd></div>
                    <div><dt>適用開始日</dt><dd>{{ application.effective_date }}</dd></div>
                    <div><dt>適用終了日</dt><dd>{{ application.expiry_date || '—' }}</dd></div>
                    <div class="details-wide"><dt>備考</dt><dd class="notes-value">{{ application.notes || '—' }}</dd></div>
                </dl>
            </template>
        </ContentState>
    </section>
</template>

<script>
import BaseButton from '../components/BaseButton.vue';
import ContentState from '../components/ContentState.vue';
import simulationApplicationsApi from '../api/simulationApplications';
import { errorMessage } from '../api/client';

const statusLabels = {
    draft: '下書き',
    submitted: '申込済み',
    approved: '承認済み',
    rejected: '却下',
    cancelled: '取消',
};

export default {
    components: { BaseButton, ContentState },
    data() {
        return { application: null, loading: true, deleting: false, error: '' };
    },
    computed: {
        canEdit() { return (this.$store.state.session || {}).can_write_applications; },
        canDelete() { return (this.$store.state.session || {}).can_delete_applications; },
        basePath() {
            return `/admin/applications/${this.$route.params.id}`;
        },
    },
    created() {
        this.loadApplication();
    },
    methods: {
        async loadApplication() {
            this.loading = true;
            this.error = '';
            try {
                const response = await simulationApplicationsApi.show(this.$route.params.id);
                this.application = response.data.data;
            } catch (error) {
                this.error = error.response && error.response.status === 404
                    ? '申込が見つかりません。'
                    : errorMessage(error, '申込情報を取得できませんでした。');
            } finally {
                this.loading = false;
            }
        },
        statusLabel(status) {
            return statusLabels[status] || status;
        },
        formatAmount(amount) {
            return `${Number(amount).toLocaleString('ja-JP')} ${this.application.currency}`;
        },
        async deleteApplication() {
            if (!window.confirm('この申込を削除します。よろしいですか？')) return;

            this.deleting = true;
            try {
                await simulationApplicationsApi.remove(this.application.id);
                this.$router.push('/admin/applications');
            } catch (error) {
                this.error = errorMessage(error, '申込を削除できませんでした。');
            } finally {
                this.deleting = false;
            }
        },
    },
};
</script>
