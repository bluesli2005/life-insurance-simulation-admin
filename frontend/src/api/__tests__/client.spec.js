import client, { errorMessage } from '../client';

test('shared client sends credentialed JSON requests to the API namespace', async () => {
    let sent;
    await client.get('/auth/session', { adapter: config => {
        sent = config;
        return Promise.resolve({ data: {}, status: 200, headers: {}, config });
    } });
    expect(sent.baseURL).toBe('/api/v1');
    expect(sent.withCredentials).toBe(true);
    expect(sent.xsrfCookieName).toBe('XSRF-TOKEN');
    expect(sent.xsrfHeaderName).toBe('X-XSRF-TOKEN');
    expect(sent.headers.Accept).toBe('application/json');
});

test('expired write request rejects without automatic replay', async () => {
    const adapter = jest.fn(config => Promise.reject({ response: { status: 419 }, config }));
    await expect(client.post('/simulation-applications', {}, { adapter })).rejects.toHaveProperty('response.status', 419);
    expect(adapter).toHaveBeenCalledTimes(1);
    expect(errorMessage({ response: { status: 419 } })).toContain('再読み込み');
});

test('unauthorized business requests announce session expiration once', async () => {
    const expired = jest.fn();
    window.addEventListener('session-expired', expired);
    try {
        const adapter = config => Promise.reject({ response: { status: 401 }, config });
        await expect(client.get('/simulation-applications', { adapter })).rejects.toHaveProperty('response.status', 401);
        expect(expired).toHaveBeenCalledTimes(1);
        expect(errorMessage({ response: { status: 401 } })).toBe('ログインしてください。');

        await expect(client.get('/auth/session', { adapter })).rejects.toHaveProperty('response.status', 401);
        await expect(client.post('/auth/login', {}, { adapter })).rejects.toHaveProperty('response.status', 401);
        expect(expired).toHaveBeenCalledTimes(1);

        await expect(client.get('/simulation-applications', {
            adapter: () => Promise.reject({ response: { status: 401 } }),
        })).rejects.toHaveProperty('response.status', 401);
        expect(expired).toHaveBeenCalledTimes(2);
    } finally {
        window.removeEventListener('session-expired', expired);
    }
});

test('API errors show permission, rate-limit, and server messages', () => {
    expect(errorMessage({ response: { status: 403 } })).toBe('この操作を行う権限がありません。');
    expect(errorMessage({ response: { status: 429 } })).toContain('試行回数が多すぎます');
    expect(errorMessage({ response: { status: 500, data: { message: 'サーバーエラー' } } })).toBe('サーバーエラー');
    expect(errorMessage(new Error('network'), '接続できませんでした。')).toBe('接続できませんでした。');
});
