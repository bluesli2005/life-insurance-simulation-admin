<template>
    <button :type="type" :disabled="disabled" :class="['button', variant]" @click="$emit('click', $event)">
        <slot />
    </button>
</template>

<script>
export default {
    props: {
        type: { type: String, default: 'button' },
        variant: { type: String, default: 'primary' },
        disabled: { type: Boolean, default: false },
    },
};
</script>
