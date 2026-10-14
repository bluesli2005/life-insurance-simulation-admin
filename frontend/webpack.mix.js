const mix = require('laravel-mix');
const fs = require('fs');

mix.setPublicPath('dist')
    .js('src/app.js', 'js')
    .sass('src/styles/app.scss', 'css')
    .copy('public/favicon.ico', 'dist/favicon.ico')
    .copy('public/robots.txt', 'dist/robots.txt')
    .version();

mix.then(() => {
    const manifest = JSON.parse(fs.readFileSync('dist/mix-manifest.json', 'utf8'));
    const html = fs.readFileSync('public/index.html', 'utf8')
        .replace('/css/app.css', manifest['/css/app.css'])
        .replace('/js/app.js', manifest['/js/app.js']);
    fs.writeFileSync('dist/index.html', html);
});
