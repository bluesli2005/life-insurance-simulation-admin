# 首次安装说明

在 `{USERPATH}/develop/life-insurance-simulation-admin` 仓库根目录执行。保留 PHP 7.4、Laravel 6.18、Vue 2、MySQL 5.7；Apple Silicon 的 PHP/MySQL 使用 Docker amd64，避免直接用主机新 PHP 运行旧 Laravel。

## 1. 准备工具

安装并启动 Docker Desktop，确认 Docker Compose 可用。独立前端开发使用 Node 14.21.3/npm 6.14.18；只通过 Docker 构建前端则无需主机 Node。

```bash
docker version
docker compose version
```

确保本地 8080、8001 未被占用。MySQL 默认没有主机端口映射。

## 2. 创建后端环境文件

首次安装且 backend/.env 不存在时：

```bash
cp backend/.env.example backend/.env
```

已有文件应保留。开发数据库用户名和密码保持 `root/root`，服务间使用 `DB_HOST=mysql`。Compose 的 environment 优先于 backend/.env；自定义 `FRONTEND_URL`、`CORS_ALLOWED_ORIGINS` 和构建变量时可通过根目录的本地 `.env` 或 shell 环境设置。所有真实环境文件均忽略，不提交。

## 3. 安装后端依赖

从根目录使用固定 Composer 2.2：

```bash
docker run --rm -v "$PWD/backend:/app" -w /app composer:2.2 composer install --no-interaction --prefer-dist
```

安装严格使用 composer.lock 和 PHP 7.4 platform 配置。backend/vendor 保留在后端目录，前端无需它。若环境中已有 vendor，此命令检查并补齐锁定依赖。

## 4. 构建与初始化密钥

```bash
docker compose build
docker compose run --rm --no-deps backend php artisan key:generate
```

仅在首次创建环境文件后生成 APP_KEY；已有密钥不要随意更换，否则旧 Cookie、Session 和加密数据会失效。前端 Dockerfile 自动执行 npm ci 和 production 构建，最终静态镜像只包含 dist。

## 5. 启动服务与初始化新库

```bash
docker compose up -d
docker compose exec -T backend php artisan migrate --seed
```

新 Compose project 使用独立 `life-insurance-separated_mysql57_data` volume，不复用旧项目 volume。迁移命令只适用于该新环境；旧业务数据导入需要先备份并明确来源。不要清空原库或删除旧 volume。

样例 120 条申请；新 local/testing 用户表为空时创建管理员 `admin@example.com` / `password`。Seeder 不覆盖已有账号。

打开 `http://localhost:8080/login`，确认日文登录、列表和权限。后端 `http://localhost:8001/api/v1/auth/csrf` 返回 204；直接访问后端 `/login` 返回 JSON 404。

## 6. 创建测试库与测试

```bash
docker compose exec -T mysql mysql -uroot -proot -e 'CREATE DATABASE IF NOT EXISTS laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
docker compose exec -T backend php vendor/bin/phpunit
```

PHPUnit 的 RefreshDatabase 只使用 laravel_testing，不能改为开发库。

前端使用 Node 14/npm 6，进入 frontend：

```bash
npm ci
npm test
npm run development
npm run production
npm run build-storybook
```

也可从根目录使用 Docker 固定 Node：

```bash
docker run --rm -v "$PWD/frontend:/frontend" -w /frontend node:14.21.3-buster npm ci
docker run --rm -v "$PWD/frontend:/frontend" -w /frontend node:14.21.3-buster npm test
```

## 7. 前端开发与直连

`frontend/npm run dev` 先构建再启动 Node 标准库静态服务，默认代理到 `http://localhost:8001`。实际命令应在 frontend 目录运行：

```bash
npm run dev
```

若默认 8080 已由 Compose 使用：

```bash
PORT=8082 npm run dev
```

将 `http://localhost:8082` 加入 Compose 后端 CORS 环境变量并重建容器。热更新未启用；另开终端 `npm run watch` 后手动刷新。

跨端口直接调用后端：

```bash
MIX_API_BASE_URL=http://localhost:8001 docker compose build frontend
docker compose up -d --no-deps frontend
```

此时浏览器向 8001 发 API 请求；withCredentials 和 X-XSRF-TOKEN 由共用 client 管理。默认 CORS 允许 localhost:8080/8081，额外 origin 要显式添加。恢复代理模式时取消 MIX_API_BASE_URL 并重新构建 frontend。不要混用 localhost 和 127.0.0.1。

## 8. 邮件和常见问题

- 密码重置链接由后端 FRONTEND_URL 生成。实际发送需正确 SMTP、端口、账号与 TLS 配置；本地通知/日志验证不等于真实送达。
- 419：刷新页面获取新 CSRF Cookie，不自动重复新增、修改或删除。
- 401：登录或重新登录；已标记删除账号也会被拒绝。
- CORS：检查浏览器 origin、后端允许列表、凭证和统一 hostname。
- 502：后端服务未就绪；检查 `docker compose logs backend`。
- 文件迁移后原 8000 服务的旧挂载配置不可直接使用。数据仍在原 volume；分离版入口为 8080/8001。
- 停止分离服务可使用 `docker compose stop`，保留数据库 volume。

PHP Artisan serve 和此 Compose 用于开发/验收，不代表正式生产部署已加固。
