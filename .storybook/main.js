module.exports = {
    stories: ['../resources/js/**/*.stories.js'],
    addons: ['@storybook/addon-essentials'],
    core: { builder: 'webpack4' },
    webpackFinal: async config => {
        config.module.rules.push({
            test: /\.scss$/,
            use: ['style-loader', 'css-loader', 'sass-loader'],
        });

        return config;
    },
};
