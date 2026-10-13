const { test, expect } = require('@playwright/test');

test('登录页空表单显示服务端校验错误', async ({ page }) => {
    const pageResponse = await page.goto('/login');
    expect(pageResponse.status()).toBe(200);
    await expect(page.getByRole('heading', { name: '管理画面ログイン' })).toBeVisible();
    await expect(page.locator('form')).toHaveAttribute('novalidate', 'novalidate');

    const loginResponse = page.waitForResponse(response =>
        response.url().includes('/api/v1/auth/login') && response.request().method() === 'POST'
    );
    await page.getByRole('button', { name: 'ログイン' }).click();

    expect((await loginResponse).status()).toBe(422);
    await expect(page.locator('.auth-field-error').first()).toBeVisible();
});
