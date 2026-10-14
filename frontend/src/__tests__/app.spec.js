function startApp(csrfResult) {
    jest.resetModules();
    const mount = jest.fn();
    const Vue = jest.fn().mockImplementation(() => ({ $mount: mount }));
    const router = { currentRoute: { meta: { requiresAuth: true } }, replace: jest.fn().mockResolvedValue() };
    const store = { commit: jest.fn() };
    const auth = { csrf: jest.fn().mockReturnValue(csrfResult) };

    jest.doMock('vue', () => Vue);
    jest.doMock('../App.vue', () => ({ __esModule: true, default: {} }));
    jest.doMock('../router', () => ({ __esModule: true, default: router }));
    jest.doMock('../store', () => ({ __esModule: true, default: store }));
    jest.doMock('../api/auth', () => ({ __esModule: true, default: auth }));

    document.body.innerHTML = '<div id="app"></div>';
    require('../app');
    return { Vue, mount, router, store, auth };
}

test('the app mounts after CSRF setup and redirects on session expiration', async () => {
    const { Vue, mount, router, store, auth } = startApp(Promise.resolve());
    await Promise.resolve();

    expect(auth.csrf).toHaveBeenCalledTimes(1);
    expect(Vue).toHaveBeenCalledTimes(1);
    expect(mount).toHaveBeenCalledWith('#app');
    const render = jest.fn();
    Vue.mock.calls[0][0].render(render);
    expect(render).toHaveBeenCalledWith({});

    window.dispatchEvent(new Event('session-expired'));
    expect(store.commit).toHaveBeenCalledWith('setSession', null);
    expect(router.replace).toHaveBeenCalledWith('/login');

    router.replace.mockRejectedValueOnce(new Error('navigation canceled'));
    window.dispatchEvent(new Event('session-expired'));
    await Promise.resolve();

    router.currentRoute.meta.requiresAuth = false;
    router.replace.mockClear();
    window.dispatchEvent(new Event('session-expired'));
    expect(router.replace).not.toHaveBeenCalled();
});

test('the app shows a connection error when CSRF setup fails', async () => {
    const { Vue } = startApp(Promise.reject(new Error('network')));
    await Promise.resolve();
    await Promise.resolve();

    expect(Vue).not.toHaveBeenCalled();
    expect(document.getElementById('app').textContent).toContain('接続できませんでした。');
});
