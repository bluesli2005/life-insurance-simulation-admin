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
