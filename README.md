# シミュレーション申込・管理

Laravel 6.18 + Vue 2 的保险模拟申请管理系统。开发阶段、版本要求及验收记录见[开发计划](DEVELOPMENT_PLAN.md)，系统分层见[架构说明](ARCHITECTURE.md)。

## 首次安装

从空白环境安装，请按[首次安装说明](INSTALLATION.md)逐步执行。项目使用 Docker 运行 PHP 7.4 和 MySQL 5.7；宿主机只需 Docker Desktop、nvm/Node.js。

安装完成后访问 <http://127.0.0.1:8000/login>。本地默认管理员：`admin@example.com` / `password`。请勿在生产环境使用此凭据。

登录、注册、密码确认、找回、重置和修改界面由 Vue 管理。可在 `/register` 注册账号，登录后在 `/admin/password` 修改密码。账号资料保存在 `users` 表，`users.role_id` 关联独立的 `roles` 表；密码仅保存哈希。邮箱验证暂未启用，找回密码需要有效 SMTP 配置。

页面统一使用 `resources/spa.html` 静态入口和 Vue；应用路由不再渲染 Blade。后台从 `/admin/api/v1/session` 读取登录用户及权限，表单通过 Laravel 的 Session 与 CSRF Cookie 提交。原有三份 `.blade.php` 页面已删除；`resources/views` 仅保留空目录占位。

角色权限由 `roles` 表中的布尔字段控制：`super_admin`（最高管理者）可查看、新增、编辑、删除申入并在 `/admin/users` 管理用户角色；`editor` 可查看、新增、编辑；`viewer` 只读。公开注册默认 `viewer`，仅有用户管理权限的账号能分配角色。最高管理者还能在用户管理页将其他账号标记为“削除済み”或恢复；这只修改 `users.status`，不删除数据库记录。被标记删除的账号不能登录或重置密码，已有会话在下次受保护请求时失效。`admin@example.com` 和当前账号不能标记删除。注册仍无审核和邮箱验证，公开部署前应限制注册入口。

## 常用命令

```sh
# 启动/停止服务
docker compose up -d
docker compose down

# 数据库迁移和示例数据
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force

# 前端构建和测试
npm ci
npm run dev
npm run production
npm run storybook
npm run build-storybook
npm test
npm run test:coverage

# 后端测试（使用独立的 laravel_testing 数据库）
docker compose exec -T mysql mysql -uroot -proot -e 'CREATE DATABASE IF NOT EXISTS laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
docker compose exec -T app php vendor/bin/phpunit
npm run test:php:coverage
```

## 数据库

Docker Compose 使用 MySQL 5.7.44。数据库 `laravel`，用户名/密码 `root` / `root`；容器内地址 `mysql:3306`，宿主机地址 `127.0.0.1:3306`，字符集 `utf8mb4`。数据保存在命名卷 `mysql57_data`。`docker compose down` 不会删除数据；不要使用 `docker compose down -v`，除非确认要永久删除数据库。

主要业务表 `simulation_applications` 保存保险模拟申请：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `id` | BIGINT | 主键 |
| `application_number` | VARCHAR(50) | 申込番号，唯一 |
| `applicant_name` / `insured_name` | VARCHAR(100) | 申请人 / 被保险人姓名（日文示例） |
| `insured_birth_date` | DATE | 被保险人生年月日 |
| `beneficiary_name` | VARCHAR(100)，可空 | 受取人氏名 |
| `coverage_amount` / `premium_amount` | DECIMAL(15,2) | 保険金額 / 保険料 |
| `currency` | CHAR(3) | 通貨，默认 `JPY` |
| `status` | VARCHAR(20) | `draft`、`submitted`、`approved`、`rejected`、`cancelled` |
| `effective_date` / `expiry_date` | DATE | 适用开始日 / 结束日（可空） |
| `notes` | TEXT，可空 | 日文备注 |
| `created_at` / `updated_at` | TIMESTAMP | 创建、更新时间 |

Laravel 还使用 `users` 管理账号、`roles` 管理角色与权限、`password_resets` 保存密码重置令牌、`failed_jobs` 保存失败队列任务；`migrations` 记录迁移。`users.role_id` 外键引用 `roles.id`，默认关联 `viewer`；`users.status` 默认 `active`，软删除时改为 `deleted`，恢复时改回 `active`，不会物理删除用户行。`roles` 包含唯一 `code`、日文 `name`，以及 `can_write_applications`、`can_delete_applications`、`can_manage_users` 三个权限字段；已登录用户默认可以查看申入。业务表按 `status`、`effective_date` 建立联合索引。本地 Seeder 只在 `users` 表为空时创建最高管理者，重跑不会覆盖已注册账号或修改后的密码；另提供 120 条日文示例申请（五种状态各 24 条）。Seeder 只在 `local`、`testing` 环境运行。

## 项目文件说明

逐个说明项目文件的职责、关键调用关系，以及哪些是 Laravel 脚手架、构建生成文件或目录占位文件，请看[项目文件说明](PROJECT_FILES.md)。该清单按仓库中的项目文件维护；`vendor/`、`node_modules/`、本地 `.env` 和测试覆盖率报告属于依赖、私有配置或运行产物，不逐项列举。

查看表和迁移状态：

```sh
docker compose exec app php artisan migrate:status
docker compose exec mysql mysql -uroot -proot laravel -e 'SHOW TABLES; DESCRIBE simulation_applications;'
```

测试使用独立数据库 `laravel_testing`，PHPUnit 会刷新该库。不要把测试环境指向开发库 `laravel`。
PHP 覆盖率使用 Docker PHP 镜像中的 PCOV。Dockerfile 更新后执行 `docker compose up -d --build app`；HTML 报告输出到 `coverage/php`。

Storybook 使用与现有 Vue 2 / Webpack 4 / Node 14 配套的 6.5.16。执行 `npm run storybook` 后访问 <http://localhost:6006>；`npm run build-storybook` 会生成 `storybook-static/` 静态站点。当前 Storybook 主版本已不再维护 Vue 2 支持；升级 Storybook 前需先规划 Vue 3 或 Node/Webpack 工具链升级。
