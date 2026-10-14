import store from '../index';
import simulationApplicationsApi from '../../api/simulationApplications';
import * as clientModule from '../../api/client';

jest.mock('../../api/simulationApplications', () => ({
    __esModule: true,
    default: { list: jest.fn() },
}));

beforeEach(() => {
    simulationApplicationsApi.list.mockReset();
    store.commit('setApplications', {
        data: [],
        meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
    });
    store.commit('setFilters', { search: '', status: '', per_page: 10 });
    store.commit('setLoading', false);
    store.commit('setError', '');
    store.commit('setNavigationError', '');
    store.commit('setSession', null);
});

test('fetchApplications sends merged filters and stores the returned page', async () => {
    let resolveRequest;
    simulationApplicationsApi.list.mockReturnValue(new Promise(resolve => { resolveRequest = resolve; }));
    const pending = store.dispatch('fetchApplications', { search: '山田', page: 2 });

    expect(simulationApplicationsApi.list).toHaveBeenCalledWith({
        search: '山田', status: '', per_page: 10, page: 2,
    });
    expect(store.state.loading).toBe(true);
    expect(store.state.error).toBe('');

    const data = [{ id: 12, applicant_name: '山田 太郎' }];
    const meta = { current_page: 2, last_page: 3, per_page: 10, total: 21 };
    resolveRequest({ data: { data, meta } });
    await pending;

    expect(store.state.applications).toEqual(data);
    expect(store.state.pagination).toEqual(meta);
    expect(store.state.loading).toBe(false);
});

test('fetchApplications exposes a load failure and clears the loading state', async () => {
    const failure = new Error('network');
    simulationApplicationsApi.list.mockRejectedValue(failure);

    await expect(store.dispatch('fetchApplications')).rejects.toBe(failure);

    expect(store.state.error).toBe('申込一覧を取得できませんでした。');
    expect(store.state.loading).toBe(false);
    expect(store.state.applications).toEqual([]);
});

test('fetchApplications uses its default message if the shared formatter returns empty text', async () => {
    const formatter = jest.spyOn(clientModule, 'errorMessage').mockReturnValue('');
    simulationApplicationsApi.list.mockRejectedValue(new Error('network'));
    try {
        await expect(store.dispatch('fetchApplications')).rejects.toThrow('network');
        expect(store.state.error).toBe('申込一覧を取得できませんでした。');
    } finally {
        formatter.mockRestore();
    }
});
