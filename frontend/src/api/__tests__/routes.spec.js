import client from '../client';
import auth from '../auth';
import applications from '../simulationApplications';

const methods = ['get', 'post', 'put', 'patch', 'delete'];
let spies;

beforeEach(() => {
    spies = Object.fromEntries(methods.map(method => [method, jest.spyOn(client, method).mockResolvedValue({})]));
});

afterEach(() => methods.forEach(method => spies[method].mockRestore()));

test('authentication requests use the versioned session API', async () => {
    await auth.csrf();
    await auth.session();
    await auth.logout();

    expect(spies.get.mock.calls.map(call => call[0])).toEqual(['/auth/csrf', '/auth/session']);
    expect(spies.post).toHaveBeenCalledWith('/auth/logout');
});

test('application requests preserve the backend resource paths and methods', async () => {
    const filters = { search: '山田', page: 2 };
    const payload = { application_number: 'SIM-012' };
    await applications.list(filters);
    await applications.show(12);
    await applications.create(payload);
    await applications.update(12, payload);
    await applications.patch(12, payload);
    await applications.remove(12);

    expect(spies.get).toHaveBeenNthCalledWith(1, '/simulation-applications', { params: filters });
    expect(spies.get).toHaveBeenNthCalledWith(2, '/simulation-applications/12');
    expect(spies.post).toHaveBeenCalledWith('/simulation-applications', payload);
    expect(spies.put).toHaveBeenCalledWith('/simulation-applications/12', payload);
    expect(spies.patch).toHaveBeenCalledWith('/simulation-applications/12', payload);
    expect(spies.delete).toHaveBeenCalledWith('/simulation-applications/12');
});
