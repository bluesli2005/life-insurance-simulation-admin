import { createLocalVue, mount } from '@vue/test-utils';
import Vuex from 'vuex';
import AdminApp from '../AdminApp.vue';

const flushPromises = () => new Promise(resolve => setTimeout(resolve, 0));

describe('AdminApp', () => {
    test('logout uses the session-aware POST and reports failures', async () => {
        const localVue = createLocalVue();
        localVue.use(Vuex);
        const store = new Vuex.Store({ state: {
            session: { user_name: '管理者', role_name: '最高管理者', can_manage_users: true },
        } });
        window.axios = { post: jest.fn().mockRejectedValue(new Error('network')) };

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

        expect(window.axios.post).toHaveBeenCalledWith('/logout', {}, { headers: { Accept: 'application/json' } });
        expect(wrapper.text()).toContain('ログアウトできませんでした。');
    });
});
