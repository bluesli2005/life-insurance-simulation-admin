import { createLocalVue, mount } from '@vue/test-utils';
import Vuex from 'vuex';
import client from '../../api/client';
jest.mock('../../api/client', () => ({ get: jest.fn(), post: jest.fn(), patch: jest.fn() }));
import AdminApp from '../AdminApp.vue';

const flushPromises = () => new Promise(resolve => setTimeout(resolve, 0));

describe('AdminApp', () => {
    test('logout uses the session-aware POST and reports failures', async () => {
        const localVue = createLocalVue();
        localVue.use(Vuex);
        const store = new Vuex.Store({ state: {
            session: { user_name: '管理者', role_name: '最高管理者', can_manage_users: true },
        } });
        client.post.mockRejectedValue(new Error('network'));

        const wrapper = mount(AdminApp, {
            localVue,
            store,
            stubs: {
                'router-link': { template: '<a><slot /></a>' },
                'router-view': true,
            },
        });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        expect(client.post).toHaveBeenCalledWith('/auth/logout');
        expect(wrapper.text()).toContain('ログアウトできませんでした。');
    });
});


test('logout succeeds after clearing session without rendering null permissions', async () => {
    const localVue = createLocalVue();
    localVue.use(Vuex);
    const store = new Vuex.Store({
        state: { session: { user_name: '管理者', role_name: '最高管理者', can_manage_users: true } },
        mutations: { setSession(state, session) { state.session = session; } },
    });
    client.post.mockResolvedValue({ status: 204 });
    client.get.mockResolvedValue({ status: 204 });
    const push = jest.fn();
    const wrapper = mount(AdminApp, { localVue, store, mocks: { $router: { push } },
        stubs: { 'router-link': { template: '<a><slot /></a>' }, 'router-view': true },
    });
    await wrapper.find('button').trigger('click');
    await flushPromises();
    expect(store.state.session).toBeNull();
    expect(push).toHaveBeenCalledWith('/login');
    expect(wrapper.text()).not.toContain('ログアウトできませんでした');
});
