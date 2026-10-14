import { mount } from '@vue/test-utils';
import ApplicationsEdit from '../ApplicationsEdit.vue';
import ApplicationsShow from '../ApplicationsShow.vue';
import simulationApplicationsApi from '../../api/simulationApplications';

jest.mock('../../api/simulationApplications', () => ({
    __esModule: true,
    default: { show: jest.fn(), update: jest.fn(), remove: jest.fn() },
}));

const flushPromises = () => new Promise(resolve => setTimeout(resolve, 0));
const stubs = { 'router-link': { props: ['to'], template: '<a><slot /></a>' } };
const application = {
    id: 12,
    application_number: 'SIM-012',
    applicant_name: '山田 太郎',
    insured_name: '山田 花子',
    insured_birth_date: '1990-01-01',
    beneficiary_name: '',
    coverage_amount: '1200000',
    premium_amount: '10000',
    currency: 'JPY',
    status: 'approved',
    effective_date: '2026-10-10',
    expiry_date: '',
    notes: '',
};

beforeEach(() => {
    simulationApplicationsApi.show.mockReset();
    simulationApplicationsApi.update.mockReset();
    simulationApplicationsApi.remove.mockReset();
});

function mountEdit(router = { push: jest.fn() }) {
    return mount(ApplicationsEdit, {
        mocks: { $route: { params: { id: '12' } }, $router: router }, stubs,
    });
}

function mountShow(session, router = { push: jest.fn() }) {
    return mount(ApplicationsShow, {
        mocks: {
            $route: { params: { id: '12' } }, $router: router,
            $store: { state: { session } },
        },
        stubs,
    });
}

test('edit loads existing data and saves changes to the same application', async () => {
    simulationApplicationsApi.show.mockResolvedValue({ data: { data: application } });
    simulationApplicationsApi.update.mockResolvedValue({ status: 200 });
    const router = { push: jest.fn() };
    const wrapper = mountEdit(router);
    await flushPromises();

    expect(wrapper.find('[name="applicant_name"]').element.value).toBe('山田 太郎');
    expect(wrapper.text()).toContain('申込編集');

    await wrapper.find('[name="applicant_name"]').setValue('山田 次郎');
    await wrapper.find('form').trigger('submit');
    await flushPromises();

    expect(simulationApplicationsApi.update).toHaveBeenCalledWith('12', expect.objectContaining({ applicant_name: '山田 次郎' }));
    expect(router.push).toHaveBeenCalledWith('/admin/applications/12');
});

test('edit shows server validation errors and keeps the entered data', async () => {
    simulationApplicationsApi.show.mockResolvedValue({ data: { data: application } });
    simulationApplicationsApi.update.mockRejectedValue({
        response: { status: 422, data: { errors: { application_number: ['申込番号は使用済みです。'] } } },
    });
    const wrapper = mountEdit();
    await flushPromises();

    await wrapper.find('[name="applicant_name"]').setValue('山田 次郎');
    await wrapper.find('form').trigger('submit');
    await flushPromises();

    expect(wrapper.text()).toContain('申込番号は使用済みです。');
    expect(wrapper.find('[name="applicant_name"]').element.value).toBe('山田 次郎');
    expect(wrapper.text()).toContain('申込を保存できませんでした。');
});

test('edit retries after a load failure', async () => {
    simulationApplicationsApi.show.mockRejectedValueOnce(new Error('network'))
        .mockResolvedValueOnce({ data: { data: application } });
    const wrapper = mountEdit();
    await flushPromises();

    expect(wrapper.text()).toContain('申込情報を取得できませんでした。');
    await wrapper.find('button').trigger('click');
    await flushPromises();

    expect(simulationApplicationsApi.show).toHaveBeenCalledTimes(2);
    expect(wrapper.find('[name="applicant_name"]').element.value).toBe('山田 太郎');
});

test('detail formats data and shows actions only with permissions', async () => {
    simulationApplicationsApi.show.mockResolvedValue({ data: { data: application } });
    const editor = mountShow({ can_write_applications: true, can_delete_applications: true });
    await flushPromises();

    expect(editor.text()).toContain('1,200,000 JPY');
    expect(editor.text()).toContain('承認済み');
    expect(editor.text()).toContain('SIM-012');
    expect(editor.text()).toContain('編集');
    expect(editor.text()).toContain('削除');

    const viewer = mountShow({ can_write_applications: false, can_delete_applications: false });
    await flushPromises();
    expect(viewer.text()).not.toContain('編集');
    expect(viewer.text()).not.toContain('削除');
});

test('detail reports missing records and can retry loading', async () => {
    simulationApplicationsApi.show.mockRejectedValueOnce({ response: { status: 404 } })
        .mockResolvedValueOnce({ data: { data: application } });
    const wrapper = mountShow(null);
    await flushPromises();

    expect(wrapper.text()).toContain('申込が見つかりません。');
    await wrapper.find('button').trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('SIM-012');
});

test('detail delete requires confirmation and returns to list after success', async () => {
    simulationApplicationsApi.show.mockResolvedValue({ data: { data: application } });
    simulationApplicationsApi.remove.mockResolvedValue({ status: 204 });
    const router = { push: jest.fn() };
    const wrapper = mountShow({ can_delete_applications: true }, router);
    await flushPromises();
    const confirm = jest.spyOn(window, 'confirm').mockReturnValueOnce(false).mockReturnValueOnce(true);
    try {
        await wrapper.find('button').trigger('click');
        expect(simulationApplicationsApi.remove).not.toHaveBeenCalled();

        await wrapper.find('button').trigger('click');
        await flushPromises();
        expect(simulationApplicationsApi.remove).toHaveBeenCalledWith(12);
        expect(router.push).toHaveBeenCalledWith('/admin/applications');
    } finally {
        confirm.mockRestore();
    }
});

test('detail keeps the user on the page when deletion fails', async () => {
    simulationApplicationsApi.show.mockResolvedValue({ data: { data: application } });
    simulationApplicationsApi.remove.mockRejectedValue(new Error('network'));
    const router = { push: jest.fn() };
    const wrapper = mountShow({ can_delete_applications: true }, router);
    await flushPromises();
    const confirm = jest.spyOn(window, 'confirm').mockReturnValue(true);
    try {
        await wrapper.find('button').trigger('click');
        await flushPromises();
        expect(wrapper.text()).toContain('申込を削除できませんでした。');
        expect(router.push).not.toHaveBeenCalled();
    } finally {
        confirm.mockRestore();
    }
});

test('detail uses a generic load message and preserves unfamiliar statuses', async () => {
    simulationApplicationsApi.show.mockRejectedValueOnce(new Error('network'))
        .mockResolvedValueOnce({ data: { data: Object.assign({}, application, { status: 'under_review' }) } });
    const wrapper = mountShow(null);
    await flushPromises();

    expect(wrapper.text()).toContain('申込情報を取得できませんでした。');
    await wrapper.find('button').trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('under_review');
});

test('edit shows a general save message when the server returns no field errors', async () => {
    simulationApplicationsApi.show.mockResolvedValue({ data: { data: application } });
    simulationApplicationsApi.update.mockRejectedValue(new Error('network'));
    const wrapper = mountEdit();
    await flushPromises();
    await wrapper.find('form').trigger('submit');
    await flushPromises();

    expect(wrapper.text()).toContain('申込を保存できませんでした。入力内容をご確認ください。');
    expect(wrapper.text()).not.toContain('申込番号は使用済みです。');
});
