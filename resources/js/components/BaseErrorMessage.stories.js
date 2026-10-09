import BaseErrorMessage from './BaseErrorMessage.vue';

export default {
    title: '基础组件/BaseErrorMessage',
    component: BaseErrorMessage,
    argTypes: { message: { control: 'text' } },
};

const Template = (args, { argTypes }) => ({
    components: { BaseErrorMessage },
    props: Object.keys(argTypes),
    template: '<BaseErrorMessage v-bind="$props" />',
});

export const RequiredField = Template.bind({});
RequiredField.args = { message: '申込者氏名を入力してください。' };
