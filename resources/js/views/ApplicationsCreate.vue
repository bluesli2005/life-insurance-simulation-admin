<template>
    <section class="page-section">
        <div class="page-heading">
            <div>
                <router-link class="back-link" to="/admin/applications">申込一覧へ戻る</router-link>
                <h2>{{ confirming ? '申込内容確認' : '申込登録' }}</h2>
            </div>
        </div>
        <p v-if="error" class="notice notice-error" role="alert">{{ error }}</p>
        <SimulationApplicationForm
            v-show="!confirming"
            :errors="errors"
            :loading="saving"
            submit-label="入力内容を確認する"
            @submit="prepareConfirmation"
        />
        <ApplicationsCreateConfirmation
            v-if="confirming"
            :application="pendingApplication"
            :loading="saving"
            @back="confirming = false"
            @confirm="createApplication"
        />
    </section>
</template>

<script>
import SimulationApplicationForm from '../components/SimulationApplicationForm.vue';
import ApplicationsCreateConfirmation from '../components/ApplicationsCreateConfirmation.vue';
import simulationApplicationsApi from '../api/simulationApplications';

export default {
    components: { SimulationApplicationForm, ApplicationsCreateConfirmation },
    data() {
        return { saving: false, confirming: false, pendingApplication: null, error: '', errors: {} };
    },
    methods: {
        prepareConfirmation(payload) {
            this.pendingApplication = payload;
            this.errors = {};
            this.error = '';
            this.confirming = true;
        },
        async createApplication(payload) {
            this.saving = true;
            this.error = '';
            this.errors = {};
            try {
                const response = await simulationApplicationsApi.create(payload);
                this.$router.push(`/admin/applications/${response.data.data.id}`);
            } catch (error) {
                this.errors = error.response && error.response.data.errors ? error.response.data.errors : {};
                this.error = this.errors.application_number
                    ? ''
                    : '申込を登録できませんでした。入力内容をご確認ください。';
                this.confirming = false;
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>
