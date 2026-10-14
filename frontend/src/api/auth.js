import client from './client';

export default {
    csrf() { return client.get('/auth/csrf'); },
    session() { return client.get('/auth/session'); },
    logout() { return client.post('/auth/logout'); },
};
