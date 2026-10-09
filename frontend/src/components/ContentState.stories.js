import ContentState from './ContentState.vue';

export default {
    title: '基础组件/ContentState',
    component: ContentState,
    argTypes: {
        loading: { control: 'boolean' },
        error: { control: 'text' },
        empty: { control: 'boolean' },
        emptyMessage: { control: 'text' },
        retry: { control: 'boolean' },
        onRetry: { action: 'retry' },
    },
};

const Template = (args, { argTypes }) => ({
    components: { ContentState },
    props: Object.keys(argTypes),
    template: '<ContentState v-bind="$props"><p>申入の一覧を表示します。</p></ContentState>',
});

export const Loading = Template.bind({});
Loading.args = { loading: true };

export const Empty = Template.bind({});
Empty.args = { empty: true, emptyMessage: '該当する申入はありません。' };

export const Error = Template.bind({});
Error.args = { error: '申入を読み込めませんでした。', retry: true };

export const WithContent = Template.bind({});
WithContent.args = {};
