import BaseTable from './BaseTable.vue';

export default {
    title: '基础组件/BaseTable',
    component: BaseTable,
};

export const Applications = () => ({
    components: { BaseTable },
    data: () => ({
        columns: [
            { key: 'application_number', label: '申込番号' },
            { key: 'applicant_name', label: '申込者氏名' },
            { key: 'status', label: 'ステータス' },
        ],
        rows: [
            { id: 1, application_number: 'APP-2026-001', applicant_name: '山田 太郎', status: '申込済み' },
            { id: 2, application_number: 'APP-2026-002', applicant_name: '佐藤 花子', status: '審査中' },
        ],
    }),
    template: '<BaseTable :columns="columns" :rows="rows"><template #actions="{ row }"><button type="button">{{ row.application_number }}を表示</button></template></BaseTable>',
});
