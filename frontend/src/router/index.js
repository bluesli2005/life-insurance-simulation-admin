import Vue from 'vue';
import VueRouter from 'vue-router';
import store from '../store';
import auth from '../api/auth';
import ApplicationsIndex from '../views/ApplicationsIndex.vue';
import ApplicationsCreate from '../views/ApplicationsCreate.vue';
import ApplicationsShow from '../views/ApplicationsShow.vue';
import ApplicationsEdit from '../views/ApplicationsEdit.vue';
import PasswordChange from '../views/PasswordChange.vue';
import UserRoles from '../views/UserRoles.vue';
import Login from '../views/auth/Login.vue';
import Register from '../views/auth/Register.vue';
import ConfirmPassword from '../views/auth/ConfirmPassword.vue';
import ForgotPassword from '../views/auth/ForgotPassword.vue';
import ResetPassword from '../views/auth/ResetPassword.vue';

Vue.use(VueRouter);

export async function checkSession(to, from, next) {
    try {
        const response = await auth.session();
        store.commit('setSession', response.data.data);
        store.commit('setNavigationError', '');
    } catch (error) {
        if (!error.response || error.response.status !== 401) {
            store.commit('setNavigationError', '接続できませんでした。画面を再読み込みしてください。');
            next(false);
            return;
        }
        store.commit('setSession', null);
    }
    if (to.meta.requiresAuth && !store.state.session) next('/login');
    else if (to.meta.guestOnly && store.state.session) next('/admin/applications');
    else next();
}

const router = new VueRouter({
    mode: 'history',
    routes: [
        { path: '/login', component: Login, meta: { guestOnly: true } },
        { path: '/register', component: Register, meta: { guestOnly: true } },
        { path: '/password/reset', component: ForgotPassword, meta: { guestOnly: true } },
        { path: '/password/reset/:token', component: ResetPassword, meta: { guestOnly: true } },
        { path: '/password/confirm', component: ConfirmPassword, meta: { requiresAuth: true } },
        { path: '/admin/password', component: PasswordChange, meta: { requiresAuth: true } },
        { path: '/admin/users', component: UserRoles, meta: { requiresAuth: true } },
        { path: '/admin/applications/create', component: ApplicationsCreate, meta: { requiresAuth: true } },
        { path: '/admin/applications/:id/edit', component: ApplicationsEdit, meta: { requiresAuth: true } },
        { path: '/admin/applications/:id', component: ApplicationsShow, meta: { requiresAuth: true } },
        { path: '/admin/applications', component: ApplicationsIndex, meta: { requiresAuth: true } },
        { path: '*', redirect: '/admin/applications' },
    ],
});

router.beforeEach(checkSession);
export default router;
