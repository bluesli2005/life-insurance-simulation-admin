import axios from 'axios';

const client = axios.create({
    baseURL: `${(process.env.MIX_API_BASE_URL || '').replace(/\/$/, '')}/api/v1`,
    withCredentials: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
});

client.interceptors.response.use(response => response, error => {
    if (error.response && error.response.status === 401 && !['/auth/login', '/auth/session'].includes((error.config || {}).url)) {
        window.dispatchEvent(new Event('session-expired'));
    }
    return Promise.reject(error);
});

export function errorMessage(error, fallback) {
    const status = error.response && error.response.status;
    if (status === 401) return 'ログインしてください。';
    if (status === 403) return 'この操作を行う権限がありません。';
    if (status === 419) return 'セッションの有効期限が切れました。画面を再読み込みしてください。';
    if (status === 429) return '試行回数が多すぎます。しばらくしてから再度お試しください。';
    return (error.response && error.response.data && error.response.data.message) || fallback;
}

export default client;
