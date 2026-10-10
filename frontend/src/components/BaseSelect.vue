<template>
    <label class="field">
        <span>{{ label }}<span v-if="required" class="required-mark">*</span></span>
        <select :id="name" :name="name" :value="value" :required="required" :disabled="disabled" @change="$emit('input', $event.target.value)">
            <option v-if="placeholder" value="">{{ placeholder }}</option>
            <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
        </select>
    </label>
</template>

<script>
export default {
    props: {
        name: { type: String, required: true },
        label: { type: String, required: false },
        value: { type: String, default: '' },
        options: { type: Array, required: true },
        placeholder: { type: String, default: '' },
        required: { type: Boolean, default: false },
        disabled: { type: Boolean, default: false },
    },
};
</script>
