<template>
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th v-for="column in columns" :key="column.key" scope="col">{{ column.label }}</th>
                    <th scope="col">操作</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rows" :key="row[rowKey]">
                    <td v-for="column in columns" :key="column.key">{{ row[column.key] || '—' }}</td>
                    <td class="table-actions"><slot name="actions" :row="row" /></td>
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
    },
};
</script>
