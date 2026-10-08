import Vue from 'vue';
import Vuex from 'vuex';
import simulationApplicationsApi from '../api/simulationApplications';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        applications: [],
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
        },
        filters: {
            search: '',
            status: '',
            per_page: 10,
        },
        loading: false,
        error: '',
    },
    mutations: {
        setApplications(state, payload) {
            state.applications = payload.data;
            state.pagination = payload.meta;
        },
        setFilters(state, filters) {
            state.filters = Object.assign({}, state.filters, filters);
        },
        setLoading(state, loading) {
            state.loading = loading;
        },
        setError(state, error) {
            state.error = error;
        },
    },
    actions: {
        async fetchApplications({ commit, state }, filters) {
            commit('setFilters', filters || {});
            commit('setLoading', true);
            commit('setError', '');

            try {
                const response = await simulationApplicationsApi.list(state.filters);
                commit('setApplications', response.data);
            } catch (error) {
                const message = error.response && error.response.data
                    ? error.response.data.message
                    : '申込一覧を取得できませんでした。';
                commit('setError', message || '申込一覧を取得できませんでした。');
                throw error;
            } finally {
                commit('setLoading', false);
            }
        },
    },
});
