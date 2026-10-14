import { mount } from '@vue/test-utils';
import App from '../../App.vue';
import AuthApp from '../AuthApp.vue';

const shells = {
    AdminApp: { template: '<div class="admin-shell">管理</div>' },
    AuthApp: { template: '<div class="auth-shell">認証</div>' },
};

function mountApp(navigationError, route, session) {
    return mount(App, {
        mocks: { $route: { meta: route }, $store: { state: { navigationError, session } } },
        stubs: shells,
    });
}

test('App selects the navigation error, protected app, auth page, or loading state', () => {
    expect(mountApp('接続失敗', { requiresAuth: true }, null).find('[role="alert"]').text()).toBe('接続失敗');
    expect(mountApp('', { requiresAuth: true }, { user_id: 1 }).find('.admin-shell').exists()).toBe(true);
    expect(mountApp('', { requiresAuth: false }, null).find('.auth-shell').exists()).toBe(true);
    expect(mountApp('', { requiresAuth: true }, null).find('[role="status"]').text()).toBe('読み込み中…');
});

test('AuthApp provides a main landmark for the current route', () => {
    const wrapper = mount(AuthApp, {
        mocks: { $route: { fullPath: '/login' } },
        stubs: { 'router-view': { template: '<div class="route-content">ログイン</div>' } },
    });

    expect(wrapper.find('main.login-page').exists()).toBe(true);
    expect(wrapper.find('.route-content').text()).toBe('ログイン');
});
