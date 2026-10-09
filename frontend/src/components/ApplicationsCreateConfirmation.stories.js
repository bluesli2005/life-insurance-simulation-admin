import ApplicationsCreateConfirmation from './ApplicationsCreateConfirmation.vue';

export default {
    title: '申入/ApplicationsCreateConfirmation',
    component: ApplicationsCreateConfirmation,
    argTypes: { onBack: { action: 'back' }, onConfirm: { action: 'confirm' } },
};

const application = {
    application_number: 'APP-2026-001',
    applicant_name: '山田 太郎',
    insured_name: '山田 花子',
    insured_birth_date: '1990-04-12',
    beneficiary_name: '山田 一郎',
    coverage_amount: 10000000,
    premium_amount: 24000,
    currency: 'JPY',
    status: 'submitted',
    effective_date: '2026-11-01',
    expiry_date: '2046-10-31',
    notes: '契約内容をご確認ください。',
};

export const ReadyToSubmit = () => ({
    components: { ApplicationsCreateConfirmation },
    data: () => ({ application }),
    template: '<ApplicationsCreateConfirmation :application="application" @back="$emit(\'back\')" @confirm="$emit(\'confirm\', $event)" />',
});

export const Submitting = () => ({
    components: { ApplicationsCreateConfirmation },
    data: () => ({ application }),
    template: '<ApplicationsCreateConfirmation :application="application" :loading="true" />',
});
