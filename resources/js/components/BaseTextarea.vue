<template>
    <label class="field">
        <span>{{ label }}</span>
        <textarea :id="name" :name="name" :value="value" :maxlength="maxlength" rows="4" @input="$emit('input', $event.target.value)"></textarea>
    </label>
</template>

<script>
export default {
    props: {
        name: { type: String, required: true },
        label: { type: String, required: true },
        value: { type: String, default: '' },
        maxlength: { type: Number, default: 1000 },
    },
};
</script>
