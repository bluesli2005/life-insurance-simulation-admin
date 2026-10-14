import BaseToast from './BaseToast.vue';

export default {
    title: 'Base/Toast',
    component: BaseToast,
};

export const Notification = () => ({
    components: { BaseToast },
    data() { return { message: '' }; },
    template: `<div><button type="button" @click="message = '権限を変更しました。'">Toast を表示</button><BaseToast :message="message" @dismiss="message = ''" /></div>`,
});
