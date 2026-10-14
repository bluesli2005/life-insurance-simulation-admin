<template>
    <section class="page-section">
        <ContentState :loading="loading" :error="loadError" retry @retry="loadApplication">
            <div v-if="application">
                <div class="page-heading">
                    <div>
                        <router-link class="back-link" :to="detailPath">申込詳細へ戻る</router-link>
                        <h2>申込編集</h2>
                    </div>
                </div>
                <p v-if="saveError" class="notice notice-error" role="alert">{{ saveError }}</p>
                <SimulationApplicationForm
                    :initial-value="application"
                    :errors="errors"
                    :loading="saving"
                    submit-label="変更を保存"
                    @submit="saveApplication"
                />
            </div>
        </ContentState>
    </section>
</template>

<script>
import ContentState from '../components/ContentState.vue';
import SimulationApplicationForm from '../components/SimulationApplicationForm.vue';
import simulationApplicationsApi from '../api/simulationApplications';
import { errorMessage } from '../api/client';

export default {
    components: { ContentState, SimulationApplicationForm },
    data() {
        return { application: null, loading: true, saving: false, loadError: '', saveError: '', errors: {} };
    },
    computed: {
        detailPath() {
            return `/admin/applications/${this.$route.params.id}`;
        },
    },
    created() {
        this.loadApplication();
    },
    methods: {
        async loadApplication() {
            this.loading = true;
            this.loadError = '';
            try {
                const response = await simulationApplicationsApi.show(this.$route.params.id);
                this.application = response.data.data;
            } catch (error) {
                this.loadError = errorMessage(error, '申込情報を取得できませんでした。');
            } finally {
                this.loading = false;
            }
        },
        async saveApplication(payload) {
            this.saving = true;
            this.saveError = '';
            this.errors = {};
            try {
                await simulationApplicationsApi.update(this.$route.params.id, payload);
                this.$router.push(this.detailPath);
            } catch (error) {
                this.errors = error.response && error.response.data.errors ? error.response.data.errors : {};
                this.saveError = errorMessage(error, '申込を保存できませんでした。入力内容をご確認ください。');
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>
