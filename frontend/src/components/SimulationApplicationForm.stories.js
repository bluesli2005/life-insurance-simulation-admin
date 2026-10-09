import SimulationApplicationForm from './SimulationApplicationForm.vue';

export default {
    title: '申入/SimulationApplicationForm',
    component: SimulationApplicationForm,
    argTypes: { onSubmit: { action: 'submit' } },
};

export const NewApplication = () => ({
    components: { SimulationApplicationForm },
    template: '<SimulationApplicationForm submit-label="確認へ進む" @submit="$emit(\'submit\', $event)" />',
});

export const ValidationErrors = () => ({
    components: { SimulationApplicationForm },
    data: () => ({ errors: { applicant_name: ['申込者氏名を入力してください。'], coverage_amount: ['保険金額を入力してください。'] } }),
    template: '<SimulationApplicationForm :errors="errors" />',
});
