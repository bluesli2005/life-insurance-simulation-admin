module.exports = {
    testEnvironment: 'jsdom',
    moduleFileExtensions: ['js', 'json', 'vue'],
    transform: {
        '^.+\\.vue$': 'vue-jest',
        '^.+\\.js$': 'babel-jest',
    },
    collectCoverageFrom: [
        'resources/js/**/*.{js,vue}',
        '!resources/js/**/__tests__/**',
    ],
    testMatch: ['**/resources/js/**/__tests__/**/*.spec.js'],
};
