import { mount } from '@vue/test-utils';
import ApplicationsCreateConfirmation from '../ApplicationsCreateConfirmation.vue';

const application = {
    application_number: 'SIM-013', applicant_name: '山田 太郎', insured_name: '山田 花子',
    insured_birth_date: '1990-01-01', beneficiary_name: '', coverage_amount: '1000000',
    premium_amount: '10000', currency: 'JPY', status: 'pending_review',
    effective_date: '2026-10-10', expiry_date: '', notes: '',
};

test('confirmation displays unknown statuses and optional empty values safely', () => {
    const wrapper = mount(ApplicationsCreateConfirmation, { propsData: { application } });

    expect(wrapper.text()).toContain('pending_review');
    expect(wrapper.text()).toContain('1,000,000 JPY');
    expect(wrapper.text()).toContain('—');
});
