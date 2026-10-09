# 生命保険シミュレーション申込・管理

日文管理系统。前端 Vue SPA 与 Laravel API 已拆分为独立目录、依赖、构建和服务。

## 技术与目录

- `frontend/`：Vue 2.6.14、Vue Router 3.5.4、Vuex 3.6.2、Axios、Laravel Mix 5.0.9、Jest、Storybook 6.5.16。Node 14.21.3/npm 6.14.18。
- `backend/`：PHP 7.4.33、Laravel 6.18.0、Composer 锁文件、PHPUnit、日文服务端校验与邮件。
- `docker-compose.yml`：独立 frontend、backend、MySQL 5.7.44 服务；项目名 `life-insurance-separated`，使用新数据库 volume。

这些旧版本按项目约束保留；本次没有进行版本升级。当前 PHP Artisan 服务用于开发与验收；正式生产运行需另外配置 PHP 应用服务、HTTPS 和运维策略。

## 首次安装与运行

按 [INSTALLATION.md](INSTALLATION.md) 从头安装。依赖、环境和数据库已准备好时，从仓库根目录执行：

```bash
docker compose up -d --build
```

- 管理页面：`http://localhost:8080/login`
- 后端 API：`http://localhost:8001/api/v1`
- 开发样例管理员：`admin@example.com` / `password`；仅在空的 local/testing 用户表由 Seeder 创建。已有数据库账号不会被覆盖。

原项目数据库 volume 未迁移或覆盖。新版本默认使用独立数据库，首次安装会创建样例数据；旧业务数据需要另外备份并导入。原端口 8000 的旧服务配置不能直接运行改造后的目录。

## 功能

登录、记住登录、退出、注册默认只读；密码确认/修改/找回/重置；申请列表、按钮搜索、状态筛选、10/20/30/50 条分页、创建确认、详情、编辑和删除；角色分配、账号标记删除/恢复。页面与错误文案保持日文。邮箱验证继续关闭。

权限由后端数据库与 Gate 判定：viewer 只读，editor 可新增/编辑，super_admin 可删除及管理用户。保留最高管理员及保护账号约束。

## 独立前端开发

```bash
cd frontend
npm ci
npm run dev
```

需 Node 14/npm 6，后端需已在 8001 运行。`npm run dev` 先构建再启动 8080 静态/代理服务；另开终端运行 `npm run watch` 重新编译，浏览器手动刷新。已有 Compose frontend 占用 8080 时可设置 `PORT=8082`，并将对应 origin 加入后端 CORS 配置。

```bash
npm test
npm run production
npm run storybook
npm run build-storybook
```

Storybook 为 `http://localhost:6007`，不需要后端。前端产物在 `frontend/dist/`；不复制到 backend/public，不提交生成产物。生产镜像只挂载 frontend 构建上下文。

## API 与部署

认证及业务接口统一 `/api/v1`；所有响应为 JSON 或 204，后端不返回页面。保留 Session Cookie + CSRF，未登录 API 返回 401，权限不足 403，CSRF 失效 419，校验 422，限流 429，已登录调用 guest 接口 409。

默认 8080 静态服务代理 `/api/*` 到 backend。直连不同端口时，设置前端构建变量 `MIX_API_BASE_URL=http://localhost:8001` 并重新构建；设置后端 `CORS_ALLOWED_ORIGINS` 为明确的前端 origin。Cookie 凭证模式不允许 CORS 通配符。统一使用 localhost，避免与 127.0.0.1 混用。

后端 `FRONTEND_URL` 控制密码重置邮件链接；真实邮件需配置并验证 SMTP。生产子域或跨站部署需单独验证 Cookie domain、SameSite、HTTPS 和浏览器策略。

## 后端测试

开发库为 `laravel`，PHPUnit 强制使用 `laravel_testing`。首次创建测试库见安装说明。

```bash
docker compose exec -T backend php vendor/bin/phpunit
docker compose exec -T backend php vendor/bin/phpunit --coverage-text --coverage-html=coverage/php
```

不要对开发库运行 `migrate:fresh`。不要通过 `docker compose down -v` 删除已有数据。

架构见 [ARCHITECTURE.md](ARCHITECTURE.md)，目录见 [PROJECT_FILES.md](PROJECT_FILES.md)，实施与验收记录见 [DEVELOPMENT_PLAN.md](DEVELOPMENT_PLAN.md)。
