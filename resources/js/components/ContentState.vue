<template>
    <div>
        <div v-if="loading" class="content-state" role="status">読み込み中です…</div>
        <div v-else-if="error" class="content-state content-state-error" role="alert">
            <p>{{ error }}</p>
            <button v-if="retry" type="button" @click="$emit('retry')">再試行</button>
        </div>
        <div v-else-if="empty" class="content-state" role="status">{{ emptyMessage }}</div>
        <slot v-else />
    </div>
</template>

<script>
export default {
    props: {
        loading: { type: Boolean, default: false },
        error: { type: String, default: '' },
        empty: { type: Boolean, default: false },
        emptyMessage: { type: String, default: '表示するデータがありません。' },
        retry: { type: Boolean, default: false },
    },
};
</script>
