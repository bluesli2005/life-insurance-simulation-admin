import Vue from 'vue';
import VueRouter from 'vue-router';
import Vuex from 'vuex';
import './bootstrap';
import AdminApp from './views/AdminApp.vue';
import router from './router';
import store from './store';

Vue.use(VueRouter);
Vue.use(Vuex);

new Vue({
    router,
    store,
    render: h => h(AdminApp),
}).$mount('#app');
