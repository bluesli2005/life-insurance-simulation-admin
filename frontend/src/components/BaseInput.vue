<template>
    <label class="field">
        <span>{{ label }}<span v-if="required" class="required-mark">*</span></span>
        <input
            :id="name"
            :name="name"
            :type="type"
            :value="value"
            :required="required"
            :max="max"
            :min="min"
            :step="step"
            :maxlength="maxlength"
            :pattern="pattern"
            :autocomplete="autocomplete"
            @input="$emit('input', $event.target.value)"
        >
    </label>
</template>

<script>
export default {
    props: {
        name: { type: String, required: true },
        label: { type: String, required: true },
        value: { type: [String, Number], default: '' },
        type: { type: String, default: 'text' },
        required: { type: Boolean, default: false },
        max: { type: String, default: null },
        min: { type: String, default: null },
        step: { type: String, default: null },
        maxlength: { type: Number, default: null },
        pattern: { type: String, default: null },
        autocomplete: { type: String, default: 'off' },
    },
};
</script>
