import axios from 'axios';

const endpoint = '/admin/api/v1/simulation-applications';

const client = axios.create({
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

export default {
    list(params) {
        return client.get(endpoint, { params });
    },
    show(id) {
        return client.get(`${endpoint}/${id}`);
    },
    create(payload) {
        return client.post(endpoint, payload);
    },
    update(id, payload) {
        return client.put(`${endpoint}/${id}`, payload);
    },
    patch(id, payload) {
        return client.patch(`${endpoint}/${id}`, payload);
    },
    remove(id) {
        return client.delete(`${endpoint}/${id}`);
    },
};
