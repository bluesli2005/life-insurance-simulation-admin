import { mount } from '@vue/test-utils';
import BaseToast from '../BaseToast.vue';

describe('BaseToast', () => {
    beforeEach(() => jest.useFakeTimers());
    afterEach(() => jest.useRealTimers());

    test('shows a status message and dismisses it after three seconds', () => {
        const wrapper = mount(BaseToast, { propsData: { message: '権限を変更しました。' } });
        expect(wrapper.find('.base-toast').attributes('role')).toBe('status');
        expect(wrapper.text()).toBe('権限を変更しました。');
        jest.advanceTimersByTime(2999);
        expect(wrapper.emitted('dismiss')).toBeUndefined();
        jest.advanceTimersByTime(1);
        expect(wrapper.emitted('dismiss')).toHaveLength(1);
        wrapper.destroy();
    });

    test('restarts the timer for a new message and clears it on destruction', async () => {
        const wrapper = mount(BaseToast, { propsData: { message: '権限を変更しました。' } });
        jest.advanceTimersByTime(1500);
        await wrapper.setProps({ message: 'ユーザーを復元しました。' });
        jest.advanceTimersByTime(1500);
        expect(wrapper.emitted('dismiss')).toBeUndefined();
        wrapper.destroy();
        jest.advanceTimersByTime(3000);
        expect(wrapper.emitted('dismiss')).toBeUndefined();
    });

    test('does not show or schedule dismissal for an empty message', () => {
        const wrapper = mount(BaseToast);
        expect(wrapper.find('.base-toast').exists()).toBe(false);
        jest.advanceTimersByTime(3000);
        expect(wrapper.emitted('dismiss')).toBeUndefined();
        wrapper.destroy();
    });
});
