import { mount } from '@vue/test-utils';
import ApplicationsCreate from '../ApplicationsCreate.vue';
import SimulationApplicationForm from '../../components/SimulationApplicationForm.vue';
import simulationApplicationsApi from '../../api/simulationApplications';

jest.mock('../../api/simulationApplications', () => ({
    __esModule: true,
    default: { create: jest.fn() },
}));

describe('ApplicationsCreate', () => {
    beforeEach(() => simulationApplicationsApi.create.mockReset());

    test('writes the application only after confirmation', async () => {
        const router = { push: jest.fn() };
        simulationApplicationsApi.create.mockResolvedValue({ data: { data: { id: 12 } } });
        const wrapper = mount(ApplicationsCreate, {
            mocks: { $router: router },
            stubs: {
                'router-link': { template: '<a><slot /></a>' },
            },
        });
        const application = {
            application_number: 'APP-001',
            applicant_name: '申込 太郎',
            insured_name: '被保険者 花子',
            insured_birth_date: '1990-01-01',
            beneficiary_name: '',
            coverage_amount: '1000000',
            premium_amount: '10000',
            currency: 'JPY',
            status: 'draft',
            effective_date: '2026-10-09',
            expiry_date: '',
            notes: '',
        };

        wrapper.findComponent(SimulationApplicationForm).vm.$emit('submit', application);
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('申込内容確認');
        expect(wrapper.text()).toContain('申込 太郎');
        expect(simulationApplicationsApi.create).not.toHaveBeenCalled();

        await wrapper.findAll('button').wrappers.find(button => button.text() === '確認して登録').trigger('click');
        await Promise.resolve();
        await Promise.resolve();

        expect(simulationApplicationsApi.create).toHaveBeenCalledWith(application);
        expect(router.push).toHaveBeenCalledWith('/admin/applications/12');
    });
});
