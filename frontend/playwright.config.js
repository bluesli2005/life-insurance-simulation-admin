const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
    testDir: './e2e',
    reporter: 'list',
    use: {
        baseURL: process.env.E2E_BASE_URL || 'http://localhost:8080',
        browserName: 'chromium',
        channel: process.env.E2E_BROWSER_CHANNEL || 'chrome',
    },
});
