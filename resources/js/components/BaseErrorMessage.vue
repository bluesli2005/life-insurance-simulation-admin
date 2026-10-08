<template>
    <p v-if="message" class="field-error" role="alert">{{ message }}</p>
</template>

<script>
export default {
    props: {
        message: { type: String, default: '' },
    },
};
</script>
