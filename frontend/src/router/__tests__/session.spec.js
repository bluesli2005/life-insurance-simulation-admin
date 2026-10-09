import { checkSession } from '../index';
import auth from '../../api/auth';
import store from '../../store';

jest.mock('../../api/auth', () => ({ session: jest.fn() }));

beforeEach(() => {
    store.commit('setSession', null);
    store.commit('setNavigationError', '');
    auth.session.mockReset();
});

test('anonymous protected navigation goes to login', async () => {
    auth.session.mockRejectedValue({ response: { status: 401 } });
    const next = jest.fn();
    await checkSession({ meta: { requiresAuth: true } }, {}, next);
    expect(next).toHaveBeenCalledWith('/login');
});

test('authenticated login navigation goes to applications and keeps permissions', async () => {
    const session = { user_id: 1, can_write_applications: true };
    auth.session.mockResolvedValue({ data: { data: session } });
    const next = jest.fn();
    await checkSession({ meta: { guestOnly: true } }, {}, next);
    expect(next).toHaveBeenCalledWith('/admin/applications');
    expect(store.state.session).toEqual(session);
});

test('network failure is visible and does not redirect in a loop', async () => {
    auth.session.mockRejectedValue(new Error('network'));
    const next = jest.fn();
    await checkSession({ meta: { requiresAuth: true } }, {}, next);
    expect(next).toHaveBeenCalledWith(false);
    expect(store.state.navigationError).toContain('接続できませんでした');
});
