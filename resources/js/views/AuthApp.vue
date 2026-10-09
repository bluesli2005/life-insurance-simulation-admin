<template>
    <main class="login-page">
        <router-view :key="$route.fullPath" />
    </main>
</template>

<script>
import Vue from 'vue';
import VueRouter from 'vue-router';
import Login from './auth/Login.vue';
import Register from './auth/Register.vue';
import ConfirmPassword from './auth/ConfirmPassword.vue';
import ForgotPassword from './auth/ForgotPassword.vue';
import ResetPassword from './auth/ResetPassword.vue';

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    routes: [
        { path: '/password/confirm', component: ConfirmPassword },
        { path: '/password/reset/:token', component: ResetPassword },
        { path: '/password/reset', component: ForgotPassword },
        { path: '/login', component: Login },
        { path: '/register', component: Register },
        { path: '*', redirect: '/login' },
    ],
});

export default { router };
</script>
