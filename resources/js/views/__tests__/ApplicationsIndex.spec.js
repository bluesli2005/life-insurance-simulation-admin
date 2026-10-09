import { createLocalVue, mount } from '@vue/test-utils';
import Vuex from 'vuex';
import ApplicationsIndex from '../ApplicationsIndex.vue';

describe('ApplicationsIndex', () => {
    test('does not search while typing and submits search only on button action', async () => {
        const localVue = createLocalVue();
        localVue.use(Vuex);
        const actions = {
            fetchApplications: jest.fn().mockResolvedValue(),
        };
        const store = new Vuex.Store({
            state: {
                session: { can_write_applications: true },
                applications: [],
                pagination: { current_page: 1, last_page: 2, per_page: 10, total: 12, from: 1, to: 10 },
                filters: { search: '', status: '', per_page: 10 },
                loading: false,
                error: '',
            },
            actions,
        });
        const wrapper = mount(ApplicationsIndex, {
            localVue,
            store,
            stubs: {
                'router-link': { template: '<a><slot /></a>' },
            },
        });

        await wrapper.vm.$nextTick();
        actions.fetchApplications.mockClear();

        await wrapper.find('input[type="search"]').setValue('SIM-2026');
        expect(actions.fetchApplications).not.toHaveBeenCalled();

        await wrapper.find('form.filter-panel').trigger('submit');
        expect(actions.fetchApplications.mock.calls[0][1]).toEqual({
            search: 'SIM-2026',
            status: '',
            per_page: 10,
            page: 1,
        });
    });

    test('viewer does not see the create button', async () => {
        const localVue = createLocalVue();
        localVue.use(Vuex);
        const store = new Vuex.Store({
            state: {
                session: { can_write_applications: false },
                applications: [], pagination: {}, filters: { search: '', status: '' }, loading: false, error: '',
            },
            actions: { fetchApplications: jest.fn().mockResolvedValue() },
        });
        const wrapper = mount(ApplicationsIndex, {
            localVue, store, stubs: { 'router-link': { template: '<a><slot /></a>' } },
        });
        expect(wrapper.text()).not.toContain('申込登録');
    });
});
