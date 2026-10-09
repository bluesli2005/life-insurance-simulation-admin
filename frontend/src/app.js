import Vue from 'vue';
import VueRouter from 'vue-router';
import Vuex from 'vuex';
import './bootstrap';
import AdminApp from './views/AdminApp.vue';
import AuthApp from './views/AuthApp.vue';
import router from './router';
import store from './store';

Vue.use(VueRouter);
Vue.use(Vuex);

if (window.location.pathname.startsWith('/admin')) {
    window.axios.get('/admin/api/v1/session').then(response => {
        store.commit('setSession', response.data.data);
        new Vue({ router, store, render: h => h(AdminApp) }).$mount('#app');
    }).catch(error => {
        if (error.response && error.response.status === 401) {
            window.location.assign('/login');
        } else {
            document.getElementById('app').textContent = '画面を読み込めませんでした。再読み込みしてください。';
        }
    });
} else {
    new Vue({ render: h => h(AuthApp) }).$mount('#app');
}
