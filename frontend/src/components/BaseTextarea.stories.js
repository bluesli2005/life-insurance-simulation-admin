import BaseTextarea from './BaseTextarea.vue';

export default {
    title: '基础组件/BaseTextarea',
    component: BaseTextarea,
};

export const Notes = () => ({
    components: { BaseTextarea },
    data: () => ({ value: '契約内容について確認済みです。' }),
    template: '<BaseTextarea v-model="value" name="notes" label="備考" :maxlength="1000" />',
});
