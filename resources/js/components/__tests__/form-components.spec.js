import { mount, shallowMount } from '@vue/test-utils';
import BaseInput from '../BaseInput.vue';
import BaseSelect from '../BaseSelect.vue';
import BaseErrorMessage from '../BaseErrorMessage.vue';
import SimulationApplicationForm from '../SimulationApplicationForm.vue';

describe('form components', () => {
    test('BaseInput emits the changed value and marks required fields', async () => {
        const wrapper = mount(BaseInput, {
            propsData: { name: 'applicant_name', label: '申込者氏名', required: true },
        });

        await wrapper.find('input').setValue('申込 太郎');

        expect(wrapper.find('input').attributes('required')).toBe('required');
        expect(wrapper.emitted('input')[0]).toEqual(['申込 太郎']);
    });

    test('BaseSelect emits the selected status', async () => {
        const wrapper = mount(BaseSelect, {
            propsData: {
                name: 'status',
                label: 'ステータス',
                options: [{ value: 'approved', label: '承認済み' }],
            },
        });

        await wrapper.find('select').setValue('approved');

        expect(wrapper.emitted('input')[0]).toEqual(['approved']);
    });

    test('BaseErrorMessage shows a field error accessibly', () => {
        const wrapper = shallowMount(BaseErrorMessage, {
            propsData: { message: '申込番号は必須です。' },
        });

        expect(wrapper.text()).toBe('申込番号は必須です。');
        expect(wrapper.attributes('role')).toBe('alert');
    });

    test('application form emits values and displays server field errors', async () => {
        const wrapper = mount(SimulationApplicationForm, {
            propsData: {
                errors: { application_number: ['この申込番号はすでに登録されています。'] },
            },
            stubs: {
                'router-link': { template: '<a><span><slot /></span></a>' },
            },
        });

        expect(wrapper.text()).toContain('この申込番号はすでに登録されています。');
        await wrapper.find('[name="application_number"]').setValue('APP-001');
        await wrapper.find('[name="applicant_name"]').setValue('申込 太郎');
        await wrapper.find('[name="insured_name"]').setValue('被保険者 花子');
        await wrapper.find('[name="insured_birth_date"]').setValue('1990-01-01');
        await wrapper.find('[name="coverage_amount"]').setValue('1000000');
        await wrapper.find('[name="premium_amount"]').setValue('10000');
        await wrapper.find('[name="effective_date"]').setValue('2026-10-09');
        await wrapper.find('form').trigger('submit');
        expect(wrapper.emitted('submit')[0][0].currency).toBe('JPY');
    });

    test('application form shows Japanese required errors with the shared error component', async () => {
        const wrapper = mount(SimulationApplicationForm, {
            stubs: { 'router-link': { template: '<a><slot /></a>' } },
        });

        await wrapper.find('[name="application_number"]').setValue('');
        await wrapper.find('form').trigger('submit');

        expect(wrapper.text()).toContain('申込番号を入力してください。');
        expect(wrapper.find('.field-error').text()).toBe('申込番号を入力してください。');
        expect(wrapper.emitted('submit')).toBeUndefined();
    });
});
