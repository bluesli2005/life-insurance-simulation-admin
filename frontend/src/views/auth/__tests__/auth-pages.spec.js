import { createLocalVue, mount } from '@vue/test-utils';
import VueRouter from 'vue-router';
import axios from 'axios';
import AuthForm from '../../../components/AuthForm.vue';
import AuthApp from '../../AuthApp.vue';
import Login from '../Login.vue';
import ConfirmPassword from '../ConfirmPassword.vue';
import ForgotPassword from '../ForgotPassword.vue';
import ResetPassword from '../ResetPassword.vue';
import Register from '../Register.vue';
import EmailVerification from '../EmailVerification.vue';
import PasswordChange from '../../PasswordChange.vue';

jest.mock('axios', () => ({ post: jest.fn() }));

const localVue = createLocalVue();
localVue.use(VueRouter);
const flushPromises = () => new Promise(resolve => setTimeout(resolve, 0));

describe('Vue authentication pages', () => {
    beforeEach(() => {
        axios.post.mockReset();
    });

    test('authentication router exposes registration but keeps email verification closed', () => {
        const paths = AuthApp.router.options.routes.map(route => route.path);

        expect(paths).toEqual([
            '/password/confirm',
            '/password/reset/:token',
            '/password/reset',
            '/login',
            '/register',
            '*',
        ]);
        expect(paths).not.toContain('/email/verify');
    });

    test('auth form posts JSON and emits success', async () => {
        axios.post.mockResolvedValue({ status: 200 });
        const wrapper = mount(AuthForm, {
            localVue,
            propsData: {
                title: 'ログイン',
                action: '/login',
                submitLabel: 'ログイン',
                successMessage: '完了しました。',
                fields: [{ name: 'email', label: 'メールアドレス', type: 'email' }],
                links: [{ to: '/login', label: 'ログインへ戻る' }],
            },
            stubs: { 'router-link': { template: '<a><slot /></a>' } },
        });

        await wrapper.find('input[name="email"]').setValue('admin@example.com');
        await wrapper.find('form').trigger('submit');
        await flushPromises();
        await wrapper.vm.$nextTick();

        expect(axios.post).toHaveBeenCalledWith('/login', { email: 'admin@example.com' }, {
            headers: { Accept: 'application/json' },
        });
        expect(wrapper.emitted('success')).toHaveLength(1);
        expect(wrapper.text()).toContain('完了しました。');
    });

    test('password change clears sensitive fields after success', async () => {
        axios.post.mockResolvedValue({ status: 200 });
        const wrapper = mount(AuthForm, {
            localVue,
            propsData: {
                title: 'パスワード変更',
                action: '/admin/password',
                submitLabel: '変更',
                successMessage: 'パスワードを変更しました。',
                clearOnSuccess: true,
                fields: [
                    { name: 'current_password', label: '現在のパスワード', type: 'password' },
                    { name: 'password', label: '新しいパスワード', type: 'password' },
                ],
            },
        });

        await wrapper.find('input[name="current_password"]').setValue('old-password-123');
        await wrapper.find('input[name="password"]').setValue('new-password-123');
        await wrapper.find('form').trigger('submit');
        await flushPromises();
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.form.current_password).toBe('');
        expect(wrapper.vm.form.password).toBe('');
        expect(wrapper.text()).toContain('パスワードを変更しました。');
    });

    test('auth form renders hidden, checkbox, optional, and default-autocomplete fields', () => {
        const wrapper = mount(AuthForm, {
            localVue,
            propsData: {
                title: '登録',
                action: '/register',
                submitLabel: '登録',
                fields: [
                    { name: 'token', type: 'hidden', value: 'token-value' },
                    { name: 'remember', type: 'checkbox', label: '保持する' },
                    { name: 'name', label: '氏名', required: false },
                ],
            },
        });

        expect(wrapper.find('input[type="hidden"]').element.value).toBe('token-value');
        expect(wrapper.find('input[type="checkbox"]').element.checked).toBe(false);
        expect(wrapper.find('input[name="name"]').attributes('required')).toBeUndefined();
        expect(wrapper.find('input[name="name"]').attributes('autocomplete')).toBe('off');
    });

    test('auth form displays field validation errors and generic server errors', async () => {
        axios.post.mockRejectedValueOnce({ response: { data: { errors: { email: ['正しい形式で入力してください。'] } } } });
        const wrapper = mount(AuthForm, {
            localVue,
            propsData: {
                title: 'ログイン',
                action: '/login',
                submitLabel: 'ログイン',
                fields: [{ name: 'email', label: 'メールアドレス', type: 'email' }],
            },
        });

        await wrapper.find('form').trigger('submit');
        await flushPromises();
        await wrapper.vm.$nextTick();
        expect(wrapper.text()).toContain('正しい形式で入力してください。');

        axios.post.mockRejectedValueOnce(new Error('network error'));
        await wrapper.find('form').trigger('submit');
        await flushPromises();
        await wrapper.vm.$nextTick();
        expect(wrapper.text()).toContain('処理に失敗しました。入力内容をご確認ください。');
    });

    test('login and confirmation pages expose their intended actions', () => {
        const login = mount(Login, { localVue, stubs: { AuthForm: true } });
        const confirmation = mount(ConfirmPassword, { localVue, stubs: { AuthForm: true } });
        const forgot = mount(ForgotPassword, { localVue, stubs: { AuthForm: true } });

        expect(login.vm.fields.map(field => field.name)).toEqual(['email', 'password', 'remember']);
        expect(login.vm.links[0].to).toBe('/password/reset');
        expect(login.vm.links[1].to).toBe('/register');
        expect(confirmation.vm.fields[0].name).toBe('password');
        expect(forgot.vm.fields[0].name).toBe('email');
        expect(forgot.vm.$options.data().links[0].to).toBe('/login');
    });

    test('reset page carries its token and email from the route', () => {
        const router = new VueRouter({
            mode: 'abstract',
            routes: [{ path: '/password/reset/:token', name: 'reset', component: ResetPassword }],
        });
        router.push({ name: 'reset', params: { token: 'token-123' }, query: { email: 'admin@example.com' } });
        const reset = mount(ResetPassword, {
            localVue,
            router,
            stubs: { AuthForm: true },
        });

        expect(reset.vm.fields.map(field => field.name)).toEqual([
            'token', 'email', 'password', 'password_confirmation',
        ]);
        expect(reset.vm.fields[0].value).toBe('token-123');
        expect(reset.vm.fields[1].value).toBe('admin@example.com');
    });

    test('registration and password change forms collect the required fields', () => {
        const register = mount(Register, { localVue, stubs: { AuthForm: true } });
        const passwordChange = mount(PasswordChange, { localVue, stubs: { AuthForm: true } });

        expect(register.vm.fields.map(field => field.name)).toEqual([
            'name', 'email', 'password', 'password_confirmation',
        ]);
        expect(register.vm.links[0].to).toBe('/login');
        expect(passwordChange.vm.fields.map(field => field.name)).toEqual([
            'current_password', 'password', 'password_confirmation',
        ]);
    });

    test('email verification page is prepared but has no application route', () => {
        const verification = mount(EmailVerification, { localVue, stubs: { AuthForm: true } });
        expect(verification.vm.links[0].to).toBe('/login');
    });
});
