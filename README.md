# 生命保険シミュレーション申込・管理

日文管理系统。Vue SPA 与 Laravel API 分别位于 `frontend/`、`backend/`，依赖、构建、测试和运行服务完全分离。根目录仅保留部署配置、项目规范和文档。

## 技术与入口

| 模块 | 技术 | 本地入口 |
| --- | --- | --- |
| frontend | Vue 2.6.14、Vue Router 3.5.4、Vuex 3.6.2、Axios、Laravel Mix 5.0.9 | http://localhost:8080/login |
| backend | PHP 7.4.33、Laravel 6.18.0、Session/CSRF/Gate | http://localhost:8001/api/v1 |
| database | MySQL 5.7.44 | Compose 内部 mysql:3306，无主机端口 |

前端使用 Node 14.21.3/npm 6.14.18、Jest 和 Storybook 6.5.16。固定旧版本按项目要求保留；当前 PHP Artisan 服务仅用于开发／验收，正式部署需另行配置应用服务、HTTPS 和运维策略。

## 安装与运行

首次安装按 [INSTALLATION.md](INSTALLATION.md) 准备依赖、环境文件、APP_KEY 和数据库。已完成初始化后，在仓库根目录执行：

```bash
docker compose up -d --build
```

Compose project 为 `life-insurance-separated`，新数据库 volume 为 `life-insurance-separated_mysql57_data`。旧业务数据库不会自动导入，也不会被覆盖。开发样例管理员为 `admin@example.com` / `password`，只在空的 local/testing 用户表创建，Seeder 不覆盖已有账号。

## 前端登录地址

默认 Compose 环境请打开：**[http://localhost:8080/login](http://localhost:8080/login)**。登录后进入 [申込一覧](http://localhost:8080/admin/applications)。8080 是前端页面端口，8001 是后端 API 端口，不能用 `http://localhost:8001/login` 登录。

请始终使用 `localhost`。默认 CORS 仅允许 `http://localhost:8080` 和 `http://localhost:8081`，不允许 `http://127.0.0.1:8080`；使用后者打开页面会导致登录 POST 返回 403，当前界面会显示「この操作を行う権限がありません。」。此情况是来源地址被拒绝，不是账号权限或密码校验失败。改为上述登录地址后重新登录。

## 当前功能与界面

- 登录、记住登录、退出、注册默认只读；密码确认、修改、找回与重置。邮箱验证未开放。
- 申込列表使用搜索按钮提交，支持状态筛选及 10/20/30/50 条分页；新增确认、详情、编辑及删除。
- 管理页左侧显示申込一覧、権限管理（按权限显示）、パスワード変更；当前模块高亮，申込子页面保持列表菜单选中。
- 所有表单使用 `novalidate`，通过页面内日文消息显示校验错误。服务端校验与权限检查继续生效。
- 権限管理使用 `BaseSelect`；保存、删除／恢复成功消息使用顶部共用 `BaseToast`。浅黄色半透明背景、深色文字，3 秒后启动 0.3 秒淡出动画；减少动态效果设置下不播放淡出。
- 申込詳細编辑按钮为蓝色，删除按钮为红色；hover 时加深颜色并增加轻微阴影。该样式只应用于详情页。

后端数据库和 Gate 决定权限：viewer 只读，editor 可新增／编辑，super_admin 可删除及管理用户。账号标记删除会保留数据并禁止登录；保护账号和最后最高管理员约束继续生效。

## 前端开发与检查

在 `frontend/` 使用 Node 14/npm 6：

```bash
npm ci
npm run dev
npm run watch
```

`dev` 先构建再启动 8080 静态／API 代理服务；`watch` 在另一终端重新编译，浏览器手动刷新。后端需在 8001 运行。已有 Compose frontend 占用 8080 时，可使用 `PORT=8082 npm run dev`，并将该 origin 加入后端 CORS。

```bash
npm test
npm run production
npm run storybook
npm run build-storybook
```

Storybook 入口为 http://localhost:6007，不需要后端。静态产物在 `frontend/storybook-static/`；生产页面产物在 `frontend/dist/`，不复制到 backend，也不提交到 Git。构建产物可按需重新生成，不能删除正在使用的静态服务目录。

后端测试在仓库根目录执行，使用独立 `laravel_testing` 数据库：

```bash
docker compose exec -T backend php vendor/bin/phpunit
```

不要对开发库运行 `migrate:fresh`，不要使用 `docker compose down -v` 删除数据。

## API 与配置

API 统一为 `/api/v1`，使用 JSON、Session Cookie 和 CSRF。默认由 frontend 代理到 backend；跨端口直连需设置构建变量 `MIX_API_BASE_URL`、后端明确的 `CORS_ALLOWED_ORIGINS` 和 Cookie 凭证。统一使用 localhost，避免与 127.0.0.1 混用。前端变量为公开信息，数据库／SMTP 等秘密配置仅放在后端。

密码重置邮件链接由 `FRONTEND_URL` 控制，真实 SMTP 投递尚未验收。

架构：[ARCHITECTURE.md](ARCHITECTURE.md)；目录：[PROJECT_FILES.md](PROJECT_FILES.md)；实施和验收记录：[DEVELOPMENT_PLAN.md](DEVELOPMENT_PLAN.md)。
