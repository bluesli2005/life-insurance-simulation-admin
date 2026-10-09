import BaseButton from './BaseButton.vue';

export default {
    title: '基础组件/BaseButton',
    component: BaseButton,
    argTypes: {
        type: { control: 'select', options: ['button', 'submit'] },
        variant: { control: 'select', options: ['button-primary', 'button-secondary', 'button-danger'] },
        disabled: { control: 'boolean' },
    },
};

const Template = (args, { argTypes }) => ({
    components: { BaseButton },
    props: Object.keys(argTypes),
    template: '<BaseButton v-bind="$props">保存</BaseButton>',
});

export const Primary = Template.bind({});
Primary.args = { type: 'button', variant: 'button-primary', disabled: false };

export const Secondary = Template.bind({});
Secondary.args = { type: 'button', variant: 'button-secondary', disabled: false };

export const Disabled = Template.bind({});
Disabled.args = { type: 'button', variant: 'button-primary', disabled: true };
