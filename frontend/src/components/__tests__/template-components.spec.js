import { mount, shallowMount } from '@vue/test-utils';
import BaseButton from '../BaseButton.vue';
import BaseErrorMessage from '../BaseErrorMessage.vue';
import BaseInput from '../BaseInput.vue';
import BaseSelect from '../BaseSelect.vue';
import BaseTextarea from '../BaseTextarea.vue';
import ContentState from '../ContentState.vue';

describe('template components', () => {
    test('BaseButton forwards its click and exposes disabled and variant props', async () => {
        const wrapper = mount(BaseButton, {
            propsData: { type: 'submit', variant: 'button-danger', disabled: false },
            slots: { default: '削除' },
        });

        expect(wrapper.attributes('type')).toBe('submit');
        expect(wrapper.classes()).toContain('button-danger');
        await wrapper.trigger('click');
        expect(wrapper.emitted('click')).toHaveLength(1);

        await wrapper.setProps({ disabled: true });
        expect(wrapper.attributes('disabled')).toBe('disabled');
    });

    test('BaseErrorMessage hides empty feedback and announces nonempty errors', async () => {
        const wrapper = shallowMount(BaseErrorMessage);
        expect(wrapper.html()).toBe('');

        await wrapper.setProps({ message: '入力内容を確認してください。' });
        expect(wrapper.text()).toBe('入力内容を確認してください。');
        expect(wrapper.attributes('role')).toBe('alert');

        const untrustedMessage = '<img src=x onerror=alert(1)>';
        await wrapper.setProps({ message: untrustedMessage });
        expect(wrapper.find('img').exists()).toBe(false);
        expect(wrapper.text()).toBe(untrustedMessage);
    });

    test('BaseInput applies constraints and emits typed input values', async () => {
        const wrapper = mount(BaseInput, {
            propsData: {
                name: 'email', label: 'メールアドレス', type: 'email', value: 'a@example.com',
                required: true, maxlength: 80, autocomplete: 'email', pattern: '.+@.+',
            },
        });
        const input = wrapper.find('input');

        expect(input.attributes()).toEqual(expect.objectContaining({
            id: 'email', name: 'email', type: 'email', required: 'required', maxlength: '80', autocomplete: 'email',
        }));
        await input.setValue('b@example.com');
        expect(wrapper.emitted('input')[0]).toEqual(['b@example.com']);
    });

    test('BaseSelect renders placeholder and options and emits selection changes', async () => {
        const wrapper = mount(BaseSelect, {
            propsData: {
                name: 'status', label: '状態', placeholder: 'すべて', required: true,
                options: [{ value: 'draft', label: '下書き' }, { value: 'approved', label: '承認済み' }],
            },
        });

        expect(wrapper.findAll('option').wrappers.map(option => option.text())).toEqual(['すべて', '下書き', '承認済み']);
        await wrapper.find('select').setValue('approved');
        expect(wrapper.emitted('input')[0]).toEqual(['approved']);

        await wrapper.setProps({ disabled: true, placeholder: '' });
        expect(wrapper.find('select').attributes('disabled')).toBe('disabled');
        expect(wrapper.findAll('option')).toHaveLength(2);
    });

    test('BaseTextarea emits edits and enforces its maximum length', async () => {
        const wrapper = mount(BaseTextarea, {
            propsData: { name: 'notes', label: '備考', value: 'メモ', maxlength: 200 },
        });
        const textarea = wrapper.find('textarea');

        expect(textarea.attributes()).toEqual(expect.objectContaining({ id: 'notes', name: 'notes', maxlength: '200', rows: '4' }));
        await textarea.setValue('更新メモ');
        expect(wrapper.emitted('input')[0]).toEqual(['更新メモ']);
    });

    test('ContentState prioritizes loading, then error, empty state, and content', async () => {
        const wrapper = mount(ContentState, {
            propsData: { loading: true, error: '失敗', empty: true, retry: true },
            slots: { default: '<p>一覧データ</p>' },
        });
        expect(wrapper.find('[role="status"]').text()).toBe('読み込み中です…');
        expect(wrapper.find('[role="alert"]').exists()).toBe(false);

        await wrapper.setProps({ loading: false });
        expect(wrapper.find('[role="alert"]').text()).toContain('失敗');
        await wrapper.find('button').trigger('click');
        expect(wrapper.emitted('retry')).toHaveLength(1);

        await wrapper.setProps({ error: '', empty: true, emptyMessage: '検索結果なし' });
        expect(wrapper.find('[role="status"]').text()).toBe('検索結果なし');

        await wrapper.setProps({ empty: false });
        expect(wrapper.find('p').text()).toBe('一覧データ');
    });
});
