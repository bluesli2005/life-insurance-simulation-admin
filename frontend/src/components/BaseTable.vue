<template>
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th v-for="column in columns" :key="column.key" scope="col">{{ column.label }}</th>
                    <th v-if="hasActions" scope="col">{{ actionsLabel }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rows" :key="row[rowKey]">
                    <td v-for="column in columns" :key="column.key">
                        <slot name="cell" :row="row" :column="column" :value="row[column.key]">{{ displayValue(row[column.key]) }}</slot>
                    </td>
                    <td v-if="hasActions" class="table-actions"><slot name="actions" :row="row" /></td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    props: {
        columns: { type: Array, required: true },
        rows: { type: Array, required: true },
        rowKey: { type: String, default: 'id' },
        actionsLabel: { type: String, default: '操作' },
    },
    computed: {
        hasActions() { return Boolean(this.$scopedSlots.actions || this.$slots.actions); },
    },
    methods: {
        displayValue(value) { return value === null || value === undefined || value === '' ? '—' : value; },
    },
};
</script>
