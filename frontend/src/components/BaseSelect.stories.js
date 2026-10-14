import BaseSelect from './BaseSelect.vue';

export default {
    title: '基础组件/BaseSelect',
    component: BaseSelect,
    argTypes: { required: { control: 'boolean' } },
};

const Template = (args, { argTypes }) => ({
    components: { BaseSelect },
    props: Object.keys(argTypes),
    template: '<BaseSelect v-bind="$props" />',
});

export const ApplicationStatus = Template.bind({});
ApplicationStatus.args = {
    name: 'status',
    label: 'ステータス',
    value: 'submitted',
    required: true,
    options: [
        { value: 'draft', label: '下書き' },
        { value: 'submitted', label: '申込済み' },
        { value: 'approved', label: '承認済み' },
    ],
};
