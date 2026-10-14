<template>
    <div>
        <p v-if="$store.state.navigationError" class="notice notice-error" role="alert">{{ $store.state.navigationError }}</p>
        <AdminApp v-else-if="$route.meta.requiresAuth && $store.state.session" />
        <AuthApp v-else-if="!$route.meta.requiresAuth" />
        <p v-else role="status">読み込み中…</p>
    </div>
</template>

<script>
import AdminApp from './views/AdminApp.vue';
import AuthApp from './views/AuthApp.vue';

export default { components: { AdminApp, AuthApp } };
</script>
