import Vue from 'vue';
import VueRouter from 'vue-router';
import ApplicationsIndex from '../views/ApplicationsIndex.vue';

Vue.use(VueRouter);

export default new VueRouter({
    mode: 'history',
    routes: [
        { path: '/admin/applications', component: ApplicationsIndex },
        { path: '*', redirect: '/admin/applications' },
    ],
});
