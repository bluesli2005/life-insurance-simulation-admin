import { createLocalVue, mount } from '@vue/test-utils';
import Vuex from 'vuex';
import UserRoles from '../UserRoles.vue';

const flushPromises = () => new Promise(resolve => setTimeout(resolve, 0));

describe('UserRoles', () => {
    const localVue = createLocalVue();
    localVue.use(Vuex);
    const makeStore = (roleCode = 'super_admin') => new Vuex.Store({ state: { session: { user_id: 1, role_code: roleCode } } });

    beforeEach(() => {
        window.axios = { get: jest.fn(), patch: jest.fn() };
    });

    test('loads users and saves a changed role', async () => {
        window.axios.get.mockResolvedValue({ data: { data: [
            { id: 1, name: '管理者', email: 'admin@example.com', role: 'super_admin', status: 'active' },
            { id: 2, name: '利用者', email: 'user@example.com', role: 'viewer', status: 'active' },
        ], roles: [
            { code: 'viewer', name: '閲覧者', can_write_applications: false, can_delete_applications: false, can_manage_users: false },
            { code: 'editor', name: '編集者', can_write_applications: true, can_delete_applications: false, can_manage_users: false },
            { code: 'super_admin', name: '最高管理者', can_write_applications: true, can_delete_applications: true, can_manage_users: true },
        ] } });
        window.axios.patch.mockResolvedValue({ data: { data: { role: 'editor' } } });

        const wrapper = mount(UserRoles, { localVue, store: makeStore() });
        await flushPromises();
        expect(wrapper.findAll('select').at(0).attributes('disabled')).toBe('disabled');

        await wrapper.findAll('select').at(1).setValue('editor');
        await wrapper.findAll('button').at(1).trigger('click');
        await flushPromises();

        expect(window.axios.patch).toHaveBeenCalledWith('/admin/api/v1/users/2/role', { role: 'editor' });
        expect(wrapper.text()).toContain('権限を変更しました。');
    });

    test('marks another user deleted and can restore them', async () => {
        window.axios.get.mockResolvedValue({ data: { data: [
            { id: 1, name: '管理者', email: 'admin@example.com', role: 'super_admin', status: 'active' },
            { id: 2, name: '利用者', email: 'user@example.com', role: 'viewer', status: 'active' },
        ], roles: [{ code: 'viewer', name: '閲覧者' }, { code: 'super_admin', name: '最高管理者' }] } });
        window.axios.patch
            .mockResolvedValueOnce({ data: { data: { status: 'deleted' } } })
            .mockResolvedValueOnce({ data: { data: { status: 'active' } } });
        window.confirm = jest.fn().mockReturnValue(true);

        const wrapper = mount(UserRoles, { localVue, store: makeStore() });
        await flushPromises();
        expect(wrapper.findAll('button').wrappers.filter(button => button.text() === '削除')).toHaveLength(1);

        await wrapper.findAll('button').wrappers.find(button => button.text() === '削除').trigger('click');
        await flushPromises();
        expect(window.axios.patch).toHaveBeenCalledWith('/admin/api/v1/users/2/status', { status: 'deleted' });
        expect(wrapper.text()).toContain('削除済み');

        await wrapper.findAll('button').wrappers.find(button => button.text() === '復元').trigger('click');
        await flushPromises();
        expect(window.axios.patch).toHaveBeenCalledWith('/admin/api/v1/users/2/status', { status: 'active' });
    });

    test('hides status actions from a non-super user manager', async () => {
        window.axios.get.mockResolvedValue({ data: { data: [
            { id: 1, name: '管理者', email: 'manager@example.com', role: 'editor', status: 'active' },
            { id: 2, name: '利用者', email: 'user@example.com', role: 'viewer', status: 'deleted' },
        ], roles: [{ code: 'viewer', name: '閲覧者' }, { code: 'editor', name: '編集者' }] } });

        const wrapper = mount(UserRoles, { localVue, store: makeStore('editor') });
        await flushPromises();
        expect(wrapper.findAll('button').wrappers.filter(button => ['削除', '復元'].includes(button.text()))).toHaveLength(0);
    });
});
