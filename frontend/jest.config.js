module.exports = {
    testEnvironment: 'jsdom',
    moduleFileExtensions: ['js', 'json', 'vue'],
    transform: {
        '^.+\\.vue$': '<rootDir>/scripts/jest-vue-transformer.js',
        '^.+\\.js$': 'babel-jest',
    },
    collectCoverageFrom: [
        'src/**/*.{js,vue}',
        '!src/**/__tests__/**',
        '!src/**/*.stories.js',
    ],
    coverageReporters: ['text', 'html', 'lcov', 'json-summary'],
    testMatch: ['**/src/**/__tests__/**/*.spec.js'],
};
