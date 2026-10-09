import Vue from 'vue';
import VueRouter from 'vue-router';
import ApplicationsIndex from '../views/ApplicationsIndex.vue';
import ApplicationsCreate from '../views/ApplicationsCreate.vue';
import ApplicationsShow from '../views/ApplicationsShow.vue';
import ApplicationsEdit from '../views/ApplicationsEdit.vue';
import PasswordChange from '../views/PasswordChange.vue';
import UserRoles from '../views/UserRoles.vue';

Vue.use(VueRouter);

export default new VueRouter({
    mode: 'history',
    routes: [
        { path: '/admin/password', component: PasswordChange },
        { path: '/admin/users', component: UserRoles },
        { path: '/admin/applications/create', component: ApplicationsCreate },
        { path: '/admin/applications/:id/edit', component: ApplicationsEdit },
        { path: '/admin/applications/:id', component: ApplicationsShow },
        { path: '/admin/applications', component: ApplicationsIndex },
        { path: '*', redirect: '/admin/applications' },
    ],
});
