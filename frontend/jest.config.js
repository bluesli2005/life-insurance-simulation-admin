module.exports = {
    testEnvironment: 'jsdom',
    moduleFileExtensions: ['js', 'json', 'vue'],
    transform: {
        '^.+\\.vue$': 'vue-jest',
        '^.+\\.js$': 'babel-jest',
    },
    collectCoverageFrom: [
        'src/**/*.{js,vue}',
        '!src/**/__tests__/**',
    ],
    testMatch: ['**/src/**/__tests__/**/*.spec.js'],
};
