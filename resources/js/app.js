import Vue from 'vue';
import VueRouter from 'vue-router';
import Vuex from 'vuex';
import AdminApp from './views/AdminApp.vue';
import router from './router';

Vue.use(VueRouter);
Vue.use(Vuex);

new Vue({
    router,
    render: h => h(AdminApp),
}).$mount('#app');
