import Vue from 'vue';
import App from './App.vue';
import router from './router';
import store from './store';
import auth from './api/auth';

window.addEventListener('session-expired', () => {
    store.commit('setSession', null);
    if (router.currentRoute.meta.requiresAuth) router.replace('/login').catch(() => {});
});

auth.csrf().then(() => {
    new Vue({ router, store, render: h => h(App) }).$mount('#app');
}).catch(() => {
    document.getElementById('app').textContent = '接続できませんでした。サーバーを確認して画面を再読み込みしてください。';
});
