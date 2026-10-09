import { mount } from '@vue/test-utils';
import UserRoles from '../UserRoles.vue';

const flushPromises = () => new Promise(resolve => setTimeout(resolve, 0));

describe('UserRoles', () => {
    beforeEach(() => {
        window.axios = { get: jest.fn(), patch: jest.fn() };
    });

    test('loads users and saves a changed role', async () => {
        window.axios.get.mockResolvedValue({ data: { data: [
            { id: 1, name: '管理者', email: 'admin@example.com', role: 'super_admin' },
            { id: 2, name: '利用者', email: 'user@example.com', role: 'viewer' },
        ], roles: [
            { code: 'viewer', name: '閲覧者', can_write_applications: false, can_delete_applications: false, can_manage_users: false },
            { code: 'editor', name: '編集者', can_write_applications: true, can_delete_applications: false, can_manage_users: false },
            { code: 'super_admin', name: '最高管理者', can_write_applications: true, can_delete_applications: true, can_manage_users: true },
        ] } });
        window.axios.patch.mockResolvedValue({ data: { data: { role: 'editor' } } });

        const wrapper = mount(UserRoles);
        await flushPromises();
        expect(wrapper.findAll('select').at(0).attributes('disabled')).toBe('disabled');

        await wrapper.findAll('select').at(1).setValue('editor');
        await wrapper.findAll('button').at(1).trigger('click');
        await flushPromises();

        expect(window.axios.patch).toHaveBeenCalledWith('/admin/api/v1/users/2/role', { role: 'editor' });
        expect(wrapper.text()).toContain('権限を変更しました。');
    });
});
