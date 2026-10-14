const http = require('http');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '../dist');
const backend = new URL(process.env.API_PROXY_TARGET || 'http://localhost:8001');
const types = { '.html': 'text/html; charset=utf-8', '.js': 'application/javascript', '.css': 'text/css', '.ico': 'image/x-icon', '.json': 'application/json', '.txt': 'text/plain' };

http.createServer((request, response) => {
    if (request.url.startsWith('/api/')) {
        const proxy = http.request({ hostname: backend.hostname, port: backend.port, path: request.url, method: request.method, headers: { ...request.headers, host: backend.host } }, upstream => {
            response.writeHead(upstream.statusCode, upstream.headers);
            upstream.pipe(response);
        });
        proxy.on('error', () => {
            response.writeHead(502, { 'Content-Type': 'application/json' });
            response.end(JSON.stringify({ message: 'API サーバーに接続できません。' }));
        });
        request.pipe(proxy);
        return;
    }
    let pathname;
    try { pathname = decodeURIComponent(new URL(request.url, 'http://localhost').pathname); }
    catch (_) { response.writeHead(400); response.end(); return; }
    let file = path.resolve(root, '.' + pathname);
    if (!file.startsWith(root + path.sep)) file = path.join(root, 'index.html');
    if (!fs.existsSync(file) || !fs.statSync(file).isFile()) {
        if (path.extname(pathname)) { response.writeHead(404); response.end(); return; }
        file = path.join(root, 'index.html');
    }
    if (!fs.existsSync(file)) { response.writeHead(503); response.end('Run npm run development first.'); return; }
    response.writeHead(200, { 'Content-Type': types[path.extname(file)] || 'application/octet-stream', 'Cache-Control': path.extname(file) === '.html' ? 'no-store' : 'no-cache' });
    fs.createReadStream(file).pipe(response);
}).listen(Number(process.env.PORT || 8080), '0.0.0.0', () => {
    console.log(`Frontend: http://localhost:${process.env.PORT || 8080}`);
});
