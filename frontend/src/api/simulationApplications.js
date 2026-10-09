import client from './client';

const endpoint = '/simulation-applications';

export default {
    list(params) { return client.get(endpoint, { params }); },
    show(id) { return client.get(`${endpoint}/${id}`); },
    create(payload) { return client.post(endpoint, payload); },
    update(id, payload) { return client.put(`${endpoint}/${id}`, payload); },
    patch(id, payload) { return client.patch(`${endpoint}/${id}`, payload); },
    remove(id) { return client.delete(`${endpoint}/${id}`); },
};
