<template>
    <transition name="toast-fade">
        <div v-if="message" class="base-toast" role="status" aria-live="polite" aria-atomic="true">
            {{ message }}
        </div>
    </transition>
</template>

<script>
export default {
    props: {
        message: { type: String, default: '' },
        duration: { type: Number, default: 3000 },
    },
    watch: {
        message: {
            immediate: true,
            handler(message) {
                clearTimeout(this.dismissTimer);
                if (message) {
                    this.dismissTimer = setTimeout(() => this.$emit('dismiss'), this.duration);
                }
            },
        },
    },
    beforeDestroy() {
        clearTimeout(this.dismissTimer);
    },
};
</script>

<style scoped>
.base-toast {
    position: fixed;
    top: 16px;
    left: 50%;
    z-index: 1000;
    transform: translateX(-50%);
    width: max-content;
    max-width: calc(100vw - 32px);
    padding: 14px 24px;
    border: 1px solid rgba(202, 138, 4, .2);
    border-radius: 6px;
    background: rgba(254, 249, 195, .65);
    color: #1f2937;
    box-shadow: 0 4px 12px rgba(15, 23, 42, .12);
    overflow-wrap: anywhere;
    pointer-events: none;
}
.toast-fade-leave-active { transition: opacity .3s ease; }
.toast-fade-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .toast-fade-leave-active { transition: none; }
}
</style>
