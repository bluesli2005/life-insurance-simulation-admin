import { mount } from '@vue/test-utils';
import BaseTable from '../BaseTable.vue';

describe('BaseTable', () => {
    test('renders configurable columns and preserves falsy values', () => {
        const wrapper = mount(BaseTable, {
            propsData: {
                columns: [
                    { key: 'name', label: '氏名' },
                    { key: 'count', label: '件数' },
                    { key: 'enabled', label: '状態' },
                    { key: 'note', label: '備考' },
                ],
                rows: [{ id: 1, name: 'テスト', count: 0, enabled: false, note: null }],
            },
        });

        expect(wrapper.findAll('th').wrappers.map(cell => cell.text())).toEqual(['氏名', '件数', '状態', '備考']);
        expect(wrapper.findAll('tbody td').wrappers.map(cell => cell.text())).toEqual(['テスト', '0', 'false', '—']);
        expect(wrapper.find('.table-actions').exists()).toBe(false);
    });

    test('supports custom cell and action slots', () => {
        const wrapper = mount(BaseTable, {
            propsData: {
                columns: [{ key: 'name', label: '氏名' }],
                rows: [{ id: 1, name: 'テスト' }],
                actionsLabel: '編集',
            },
            scopedSlots: {
                cell: '<strong slot-scope="props">{{ props.column.label }}:{{ props.value }}</strong>',
                actions: '<button slot-scope="props">{{ props.row.name }}</button>',
            },
        });

        expect(wrapper.findAll('th').wrappers.map(cell => cell.text())).toEqual(['氏名', '編集']);
        expect(wrapper.find('tbody strong').text()).toBe('氏名:テスト');
        expect(wrapper.find('tbody button').text()).toBe('テスト');
    });
});
