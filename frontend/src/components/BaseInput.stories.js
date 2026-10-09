import BaseInput from './BaseInput.vue';

export default {
    title: '基础组件/BaseInput',
    component: BaseInput,
    argTypes: {
        type: { control: 'select', options: ['text', 'date', 'number', 'email'] },
        required: { control: 'boolean' },
    },
};

const Template = (args, { argTypes }) => ({
    components: { BaseInput },
    props: Object.keys(argTypes),
    template: '<BaseInput v-bind="$props" />',
});

export const Default = Template.bind({});
Default.args = { name: 'applicant_name', label: '申込者氏名', value: '山田 太郎', type: 'text', required: true };

export const Date = Template.bind({});
Date.args = { name: 'birth_date', label: '生年月日', value: '1990-01-01', type: 'date', required: true };
