# 首次安装说明

在终端将 `{USERPATH}` 替换为自己的主目录。项目保留 PHP 7.4、Laravel 6.18、Vue 2、Node 14/npm 6 和 MySQL 5.7；后端、数据库及前端正式构建由 Docker 提供匹配的运行时。

本文把命令标为必需、条件必需或可选。首次启动页面只需完成第 1～5 节；不运行测试时，无需在主机安装前端依赖、测试数据库或浏览器测试程序。

## 1. 环境与目录

必需：安装并启动 Docker Desktop，在仓库根目录执行后续 Compose 命令。8080、8001、3306 端口须可用。当前 MySQL 配置为 `3306:3306`，未限定主机绑定地址，仅适合受控的本地开发环境。

```bash
cd {USERPATH}/develop/life-insurance-simulation-admin
```

可选：遇到 Docker 或 Compose 报错时，先检查版本和配置。

```bash
docker version
docker compose version
docker compose config --quiet
```

## 2. 后端环境与依赖

条件必需：仅在 `backend/.env` 不存在时复制；已有文件保留，不能覆盖。

```bash
cp backend/.env.example backend/.env
```

开发库默认用户名/密码为 `root/root`。Compose 的数据库环境变量优先于 `backend/.env`；真实环境文件不提交。

必需（新检出的仓库）：安装锁定的 PHP 依赖。已有完整 `backend/vendor` 时可跳过。

```bash
docker run --rm -v "$PWD/backend:/app" -w /app composer:2.2 composer install --no-interaction --prefer-dist
```

## 3. 构建镜像与 APP_KEY

必需（首次或镜像缺失）：构建后端与前端镜像。前端镜像在 Node 14 容器内执行 `npm ci` 和生产构建，因此仅启动 Compose 页面无需主机 Node/npm，也无需单独构建前端。

```bash
docker compose build
```

条件必需：仅当 `backend/.env` 中的 `APP_KEY` 为空时执行一次。已有密钥不要重新生成，否则现有 Cookie、Session 和加密数据会失效。

```bash
docker compose run --rm --no-deps backend php artisan key:generate
```

静态镜像不运行浏览器测试；构建时跳过 Cypress 浏览器程序下载。主机要运行 Cypress 时，见第 7 节。

## 4. 启动与建表

必需：启动三个服务。首次使用新数据库时执行迁移；已有已迁移的数据库不必重复操作。迁移会变更数据库结构，请确认连接的是本项目开发库。

```bash
docker compose up -d
docker compose exec -T backend php artisan migrate
```

Compose project 为 `life-insurance-separated`，MySQL 使用独立 volume `life-insurance-separated_mysql57_data`；不会自动导入旧业务库。导入旧业务数据前，先备份并确认来源。不要对开发库执行 `migrate:fresh`，也不要用 `docker compose down -v` 删除数据。

可选：只在需要演示账号和 120 条示例申请时运行 Seeder。空的 local/testing 用户表会创建 `admin@example.com` / `password`；Seeder 不覆盖已有账号。

```bash
docker compose exec -T backend php artisan db:seed
```

可选：检查服务状态和登录页响应。

```bash
docker compose ps
curl -I http://localhost:8080/login
```

前端登录地址为 [http://localhost:8080/login](http://localhost:8080/login)，登录后进入 [申込一覧](http://localhost:8080/admin/applications)。8001 是后端 API 端口，不是登录页面。浏览器请使用 `localhost`，不要改成 `127.0.0.1`：默认 CORS 未允许后者作为前端 origin。

本机数据库客户端连接开发库：`127.0.0.1:3306`，数据库 `laravel`，用户名/密码 `root/root`。容器内后端使用 `DB_HOST=mysql` 和 `DB_PORT=3306`。

## 5. 必需步骤到此结束

到这里页面与 API 已可运行。以下测试、独立前端开发、Storybook 和直连 API 均为可选操作，只执行需要的部分。

## 6. 单元测试

可选：后端 PHPUnit。首次测试前，必须先创建独立测试库；它与开发库 `laravel` 分离。

```bash
docker compose exec -T mysql mysql -uroot -proot -e 'CREATE DATABASE IF NOT EXISTS laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
docker compose exec -T backend php vendor/bin/phpunit
```

`backend/phpunit.xml` 将测试数据库固定为 `laravel_testing`。不要把测试库改成开发库。

可选：前端 Jest 或本机前端工具。先在主机准备 Node 14.21.3/npm 6.14.18，再进入 `frontend` 安装一次依赖。仅运行 Compose 页面不需要以下命令。

```bash
cd frontend
npm ci
npm test
```

可选替代：若主机没有 Node 14，可从仓库根目录使用 Docker 运行 Jest。首次在 Docker 中安装依赖才执行第一行；`frontend/node_modules` 会写入 Linux 依赖，之后切换主机 npm 构建时应在主机重新运行 `npm ci`。

```bash
docker run --rm -e CYPRESS_INSTALL_BINARY=0 -v "$PWD/frontend:/frontend" -w /frontend node:14.21.3-buster npm ci
docker run --rm -v "$PWD/frontend:/frontend" -w /frontend node:14.21.3-buster npm test
```

## 7. 浏览器 E2E 测试

可选：先确保 Compose 服务已启动、`frontend/node_modules` 已安装，且运行测试的电脑装有 Google Chrome。以下命令在 `frontend/` 执行；Playwright 和 Cypress 的用例均提交空登录表单，不创建数据。

```bash
npm run test:e2e
npm run test:cypress
```

可选：需要 Playwright 交互界面时运行：

```bash
npm run test:e2e:ui
```

可选：需要 Cypress 交互界面时运行，并在界面中选择 Chrome 和用例：

```bash
npm run test:cypress:ui
```

条件必需（仅 Cypress 报浏览器程序缺失时）：在 `frontend/` 安装本机 Cypress 程序。`npm ci` 正常执行安装脚本时通常会自动安装。

```bash
npx cypress install
```

可选：确认 Cypress 程序已安装。

```bash
npx cypress verify
```

Playwright 固定为 1.34.3，Cypress 固定为 12.17.4，两者的包均支持 Node 14。当前浏览器 E2E 已在本机 Chrome/Node 24 上验证；Node 14 Docker 验证了依赖安装、Jest 和 Playwright 命令加载，未在该容器内运行浏览器。可选：测试其他前端地址时使用 `E2E_BASE_URL`，例如：

```bash
E2E_BASE_URL=http://localhost:8082 npm run test:e2e
```

## 8. 独立前端开发和 Storybook

可选：需先按第 6 节安装主机前端依赖，且后端 8001 已运行。以下命令在 `frontend/` 执行。`dev` 会先构建再启动静态服务，并把 `/api/` 同源代理到后端；默认占用 8080。

```bash
npm run dev
```

条件选择：Compose 前端占用 8080 时，用另一端口。默认代理模式不需要为 8082 额外配置 CORS。

```bash
PORT=8082 npm run dev
```

可选：热更新未启用；另一个终端运行 `watch` 后手动刷新浏览器。

```bash
npm run watch
```

可选：按需选择以下命令。`development` 生成开发资源，`production` 生成生产资源；Compose 构建已执行生产构建，不需要在主机重复运行。

```bash
npm run development
npm run production
```

可选：打开 Storybook 组件界面；此命令持续运行。

```bash
npm run storybook
```

可选：需要组件静态站时单独构建。

```bash
npm run build-storybook
```

Storybook 默认入口为 [http://localhost:6007](http://localhost:6007)，不需要后端。

## 9. 跨端口直连 API 与维护

可选：只在要让浏览器直接请求 8001 而不通过前端代理时，从仓库根目录重新构建前端。此模式必须确认后端 CORS 允许实际前端 origin；默认允许 `http://localhost:8080` 和 `http://localhost:8081`。不要混用 `localhost` 与 `127.0.0.1`。

```bash
MIX_API_BASE_URL=http://localhost:8001 docker compose build frontend
docker compose up -d --no-deps frontend
```

可选：前端源码改变后重建 Compose 静态服务；取消 `MIX_API_BASE_URL` 后同样执行以下命令可恢复默认代理模式。

```bash
docker compose build frontend
docker compose up -d --no-deps frontend
```

可选：排查后端 502 时查看日志。

```bash
docker compose logs backend
```

可选：结束本地服务时保留数据库 volume。

```bash
docker compose stop
```

密码重置邮件要真实送达，需另行配置 SMTP、端口、账号及 TLS。403 时先核对浏览器 origin；419 时刷新页面获取 CSRF Cookie，不能自动重放写操作；401 时重新登录。当前 PHP Artisan 服务和此 Compose 仅供开发/验收，不代表生产部署已加固。
