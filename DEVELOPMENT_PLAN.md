# シミュレーション申込・管理（admin画面）开发计划

## 1. 文档目的

本文记录“シミュレーション申込・管理（admin画面）”的需求、技术约束、数据库设计、开发阶段、验收条件、回滚方法和进度。

阶段 A 至 E 均已完成。各阶段均按验收条件验证，并记录未删除的默认脚手架范围。

## 2. 项目信息

| 项目 | 内容 |
| --- | --- |
| 项目名 | `life-insurance-simulation-admin` |
| 系统名 | シミュレーション申込・管理（admin画面） |
| 项目目录 | `{USERPATH}/life-insurance-simulation-admin` |
| 系统类型 | Laravel 内嵌 Vue 的管理系统 |
| 主要语言 | 页面显示文字全部使用日文 |
| 数据库 | Docker 内 MySQL 5.7 |
| 本机容器运行时 | Docker Desktop |
| Storybook | 暂不导入 |
| 数据来源 | 新建 MySQL 示例数据，不迁移旧 PostgreSQL 数据 |

## 3. 固定技术版本

客户指定版本作为强制约束，不自动升级主版本。

| 技术 | 版本 |
| --- | --- |
| PHP | 7.4 |
| Laravel | 6.18 |
| MySQL | 5.7 |
| Vue | 2.6.14 |
| Vue Router | 3.5.x 中与 Vue 2 兼容的固定版本 |
| Vuex | 3.6.2 |
| Laravel Mix | 5.x 中与当前依赖兼容的固定版本 |
| Laravel UI | 1.x |
| Node.js | 14.21.3 |
| npm | 6.14.18 |
| Webpack | Laravel Mix 5 对应版本 |

所有 Composer 和 npm 依赖必须写入锁文件。不得使用未固定的 `latest` 版本。安装时先验证 PHP 扩展、Composer、Node.js、npm 和 MySQL 的实际版本。

## 4. 版本风险

PHP 7.4、Laravel 6、Vue 2 和 Node.js 14 均属于停止官方维护的旧版本。本项目按客户环境进行兼容构建，不把依赖升级作为开发范围。

风险控制：

- 只安装与指定技术栈兼容的依赖。
- 不引入当前功能不需要的第三方包。
- 保存 `composer.lock` 和 `package-lock.json`。
- README 明确记录旧版本风险和本地运行版本。
- MySQL 5.7 使用 Docker 容器运行，避免在 macOS 主机安装已停止维护的 OpenSSL 1.1。
- 管理页面必须登录后访问。
- 不将测试账号密码用于生产环境。
- 上线前由客户确认基础设施层的访问限制和 TLS 配置。

## 5. 目标架构

```text
浏览器
  -> Laravel Web Route
  -> 静态 resources/spa.html / Vue 2 认证与管理页面入口
  -> Vue Router
  -> Vuex
  -> Axios
  -> Laravel Session + CSRF 保护的 JSON Route
  -> Controller / Form Request / Resource
  -> Eloquent
  -> MySQL 5.7
```

Laravel 和 Vue 位于同一个项目：

```text
life-insurance-simulation-admin/
├── app/
│   ├── Http/Controllers/
│   ├── Http/Requests/
│   ├── Http/Resources/
│   └── Models/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeds/
├── resources/
│   ├── js/
│   │   ├── api/
│   │   ├── components/
│   │   ├── router/
│   │   ├── store/
│   │   └── views/
│   ├── sass/
│   └── views/
├── routes/
│   ├── web.php
│   └── console.php
├── tests/
├── webpack.mix.js
├── composer.json
└── package.json
```

## 6. 认证设计

使用 Laravel Session 认证和 `laravel/ui` 1.x 提供的 Laravel 6 兼容认证基础。

当前认证范围（阶段 B 初版方案已按后续需求调整）：

- 管理员登录。
- 管理员登出。
- 未登录访问管理页面时跳转到登录页。
- 开放管理员注册；账号存储在 `users` 表，密码以哈希形式保存。
- 注册账号默认 `viewer`；`editor` 可新增/修改申入，`super_admin` 还可删除申入、管理用户角色，并对其他用户执行可恢复的逻辑删除。`admin@example.com` 为最高管理者；后端 Gate 按数据库角色授权。
- 角色及权限定义保存在独立的 `roles` 表，`users.role_id` 外键关联；不再以 `users.role` 字符串作为权限来源。
- 登录后可验证当前密码并修改密码；开发 Seeder 仅在 `users` 表为空时创建初始账号。
- 邮箱验证组件已准备，路由保持关闭。
- 支持忘记密码邮件和密码重置；实际投递需要有效 SMTP 配置。
- 支持已登录用户的密码二次确认。
- 登录失败时显示日文错误。
- 登录成功后进入申込一览。
- 所有新增、修改和删除请求使用 CSRF 保护。

本地 Seeder 管理员：

```text
メールアドレス：admin@example.com
パスワード：password
```

该账号仅用于本地和测试环境。生产环境不得使用默认密码。

## 7. 页面和路由

| 路径 | 页面 | 主要功能 |
| --- | --- | --- |
| `/login` | ログイン | 管理员登录 |
| `/register` | 管理者登録 | 管理员注册 |
| `/password/reset` | パスワード再設定 | 申请密码重置邮件 |
| `/password/reset/{token}` | 新しいパスワードの設定 | 使用邮件令牌重置密码 |
| `/password/confirm` | パスワードの再確認 | 确认当前管理员密码 |
| `/admin/password` | パスワード変更 | 登录后修改密码 |
| `/admin/users` | 権限管理 | 最高管理者分配用户角色 |
| `/admin/applications` | 申込一覧 | 搜索、筛选、分页、进入详情 |
| `/admin/applications/create` | 申込登録 | 新建申请 |
| `/admin/applications/:id` | 申込詳細 | 显示申请详情、编辑和删除入口 |
| `/admin/applications/:id/edit` | 申込編集 | 修改申请 |

管理页面和认证页面均由 Vue 管理。Laravel 通过不含模板语法的 `resources/spa.html` 提供挂载点，不再渲染 Blade；`/admin` 路由使用 `auth` middleware 保护。`/register` 已开放，`/email/verify` 仍为 404。新注册账号可进入后台查看申入，但无写入权限；注册审核仍未启用。

## 8. 页面显示文字

所有可见文字使用日文，包括：

- 页面标题和导航。
- 表头和字段标签。
- 按钮。
- 搜索条件。
- 状态名称。
- 加载中、空数据和错误提示。
- 表单校验错误。
- 删除确认。
- 登录和登出。

主要用语：

| 中文含义 | 日文显示 |
| --- | --- |
| 申请一览 | 申込一覧 |
| 新建申请 | 申込登録 |
| 申请详情 | 申込詳細 |
| 编辑申请 | 申込編集 |
| 搜索 | 検索 |
| 重置 | リセット |
| 保存 | 保存 |
| 删除 | 削除 |
| 取消 | キャンセル |
| 上一页 | 前へ |
| 下一页 | 次へ |

## 9. 数据库设计

### 9.1 `users`

使用 Laravel 认证基础结构。

| 字段 | 类型 | 规则 |
| --- | --- | --- |
| `id` | bigint | 主键 |
| `name` | varchar(255) | 必填 |
| `email` | varchar(255) | 必填、唯一 |
| `password` | varchar(255) | 必填、Hash 保存 |
| `role_id` | unsigned bigint | 必填、外键引用 `roles.id`，默认查看者 |
| `status` | varchar(20) | 默认 `active`；逻辑删除为 `deleted`，可恢复 |
| `remember_token` | varchar(100) nullable | Laravel 认证字段 |
| `created_at` / `updated_at` | timestamp | Laravel 时间戳 |

### 9.2 `roles`

| 字段 | 类型 | 规则 |
| --- | --- | --- |
| `id` | bigint | 主键 |
| `code` | varchar(255) | 唯一；`viewer`、`editor`、`super_admin` |
| `name` | varchar(255) | 日文显示名称 |
| `can_write_applications` | boolean | 新增、修改申入 |
| `can_delete_applications` | boolean | 删除申入 |
| `can_manage_users` | boolean | 查询用户并分配角色 |

三个角色定义随迁移写入数据库。已登录用户均可查看申入；Gate 读取上述权限字段，前端读取同一授权结果控制操作入口。

### 9.3 `simulation_applications`

| 字段 | 类型 | 规则 |
| --- | --- | --- |
| `id` | bigint | 主键 |
| `application_number` | varchar(50) | 必填、唯一 |
| `applicant_name` | varchar(100) | 申込者氏名、必填 |
| `insured_name` | varchar(100) | 被保険者氏名、必填 |
| `insured_birth_date` | date | 被保険者生年月日、必填、不能晚于当前日期 |
| `beneficiary_name` | varchar(100) nullable | 受取人氏名 |
| `coverage_amount` | decimal(15,2) | 保険金額、必须大于 0 |
| `premium_amount` | decimal(15,2) | 保険料、必须大于 0 |
| `currency` | char(3) | 默认 `JPY` |
| `status` | varchar(20) | 必填、限定状态值 |
| `effective_date` | date | 適用開始日、必填 |
| `expiry_date` | date nullable | 適用終了日、不能早于开始日 |
| `notes` | text nullable | 備考、最多 1000 字符 |
| `created_at` / `updated_at` | timestamp | Laravel 时间戳 |

不增加以下内容：

- 负责人字段。
- 审批历史。
- 软删除。
- 审计日志。
- 附件。
- 独立的申请人、被保险人和受益人表。

### 9.3 状态

| 数据值 | 日文显示 |
| --- | --- |
| `draft` | 下書き |
| `submitted` | 申込済み |
| `approved` | 承認済み |
| `rejected` | 却下 |
| `cancelled` | 取消 |

## 10. 初始数据

Seeder 创建：

- 1 个本地管理员。
- 120 条 `simulation_applications` 示例数据。
- 五种状态均包含示例数据。
- 申请编号唯一。
- Seeder 可重复执行，不重复插入，也不覆盖用户已修改的数据。

不导入当前 PostgreSQL 数据。

## 11. API 设计

JSON 请求由登录 Session 和 CSRF 保护。建议使用以下同源路径：

```text
GET    /admin/api/v1/simulation-applications
POST   /admin/api/v1/simulation-applications
GET    /admin/api/v1/simulation-applications/{simulation_application}
PUT    /admin/api/v1/simulation-applications/{simulation_application}
PATCH  /admin/api/v1/simulation-applications/{simulation_application}
DELETE /admin/api/v1/simulation-applications/{simulation_application}
```

列表参数：

| 参数 | 规则 |
| --- | --- |
| `search` | 可空、字符串、最多 100 字符 |
| `status` | 可空、必须是五种状态之一 |
| `page` | 可空、整数、最小 1 |
| `per_page` | 可空，只允许 10、20、30、50，默认 10 |

`search` 搜索：

- 申込番号。
- 申込者氏名。
- 被保険者氏名。
- 受取人氏名。
- 備考。

响应使用稳定的 JSON `data` envelope。分页响应同时包含 `meta` 和 `links`。

## 12. 前端设计

### 12.1 Vue Router

管理以下页面：

- 申込一覧。
- 申込登録。
- 申込詳細。
- 申込編集。

未知管理路径回到申込一覧。认证 Router 开放登录、注册、确认密码和找回/重置密码路径；邮箱验证页面暂不接入路由。

### 12.2 Vuex

最低限度使用 Vuex 管理：

- 申込列表数据。
- 搜索条件。
- 当前页和每页条数。
- 加载状态和 API 错误。

表单编辑状态保留在页面组件内，不为单页表单增加额外 Vuex 模块。

### 12.3 通用组件

计划创建：

- `BaseInput`
- `BaseSelect`
- `BaseTextarea`
- `BaseButton`
- `BaseErrorMessage`
- `BaseTable`
- `ContentState`
- `SimulationApplicationForm`

组件只提供当前页面实际需要的 props 和事件，不增加设计系统或复杂抽象。

### 12.4 一览页面

- 默认每页 10 条。
- 可选择 10、20、30、50 条。
- 搜索在点击“検索”后执行，不按输入实时请求。
- 修改每页条数后返回第 1 页并重新读取。
- 状态筛选与关键词一起提交。
- “リセット”清除搜索和状态，保留默认每页 10 条。

## 13. 校验规则

前端提供必要的即时提示，后端 Form Request 作为最终校验边界。

最低限度规则：

- 申请编号必填、最多 50 字符、唯一。
- 申请人和被保险人姓名必填、最多 100 字符。
- 出生日期必填且不得晚于当前日期。
- 保额和保费必须大于 0。
- 币种固定三字符，默认 `JPY`。
- 状态必须是定义值。
- 开始日期必填。
- 结束日期不得早于开始日期。
- 备注最多 1000 字符。

## 14. 测试范围

### 14.1 Laravel

- 登录成功和失败。
- 登出。
- 未登录访问管理页面时被拒绝或跳转。
- CRUD。
- 404 JSON。
- 422 校验错误。
- 申请编号唯一性。
- 关键词搜索。
- 状态筛选。
- 分页和每页条数白名单。
- Seeder 重复执行。

### 14.2 Vue

使用与 Vue 2 兼容的 Vue Test Utils 和测试工具，只覆盖必要行为：

- 通用输入控件的 props 和事件。
- 表单提交和字段错误显示。
- 列表搜索条件。
- 分页与每页条数切换。

暂不导入 Storybook，不做视觉回归测试。

## 15. 开发阶段

### 阶段 A：基础环境

状态：已完成（Docker 运行环境）

内容：

- 创建 Laravel 6.18 项目。
- 固定 Composer 依赖。
- 创建 Node.js 14 和 npm 6 配置。
- 安装 Vue 2、Vue Router 3、Vuex 3 和 Laravel Mix 5。
- 安装 Docker Desktop。
- 使用 Docker 运行 MySQL 5.7，用户名和密码均为 `root`。
- 建立 Blade + Vue 管理入口。
- 建立日文页面基础布局。

验收：

- PHP、Composer、Node.js、npm 和 MySQL 版本符合要求。
- Laravel 可以启动。
- MySQL 连接成功。
- `npm run dev` 成功。
- `npm run production` 成功。
- 首页不出现 Vue 或 Mix 编译错误。

已完成：

- 创建 Laravel 6.18.0 基础工程，并生成 `composer.lock`。
- 固定 Laravel Mix 5.0.9、Vue 2.6.14、Vue Router 3.5.4、Vuex 3.6.2 和 Vue Template Compiler 2.6.14，并生成 `package-lock.json`。
- 增加 `.nvmrc`，指定 Node.js 14.21.3；`package.json` 记录 Node.js/npm 版本约束。
- 配置 `.env.example` 和本地 `.env` 的 MySQL 用户名、密码为 `root`。
- 建立 `admin.blade.php`、Vue Router、Vuex 初始化、日文管理布局和申込一覧基础页面。
- `npm run dev` 通过。
- `npm run production` 通过。
- 增加 `Dockerfile`，使用 PHP 7.4 CLI 和 `pdo_mysql` 扩展运行 Laravel。
- 增加 `docker-compose.yml`，使用 MySQL 5.7.44；数据库名为 `laravel`，用户名和密码均为 `root`。
- Docker Desktop 已启动，Docker Compose 版本为 5.5.1；应用容器和 MySQL 容器均已启动。
- 已删除 macOS Homebrew PHP 7.4，主机 PHP 恢复为 8.5；PHP 7.4 由 Docker 应用容器提供。
- 使用 Composer 2.2 生成 Laravel 6.18 可读取的依赖包清单，避免旧版 Laravel 与 Composer 2.10 清单格式不兼容。

未通过：

- 主机 PHP 为 8.5.11，未安装 PHP 7.4；如直接在主机执行 Laravel Artisan，会因旧版反射 API 弃用错误退出。项目运行必须使用 Docker。
- Node.js/npm 主机版本高于项目目标版本；阶段 E 已在 Node.js 14.21.3 / npm 6.14.18 容器中完成干净安装、测试和开发/生产构建验证。

环境备注：Composer 安全策略会阻止 Laravel 6.18 的已知安全风险依赖。本地按客户固定版本生成锁文件时使用了一次性 `--no-security-blocking`；生产环境不得忽略安全审计。`composer.json` 已固定 PHP 7.4.33 平台，`composer.lock` 已降到 PHP 7.4 可用依赖。

已验证：

- `docker compose ps`：PHP 7.4 应用和 MySQL 5.7.44 均为运行状态，MySQL 健康检查通过。
- `docker exec ... php -v`：PHP 7.4.33。
- `docker exec ... php artisan --version`：Laravel Framework 6.18.0。
- `curl -I http://127.0.0.1:8000/admin/applications`：HTTP 200，响应头显示 PHP 7.4.33。
- Laravel `DB::connection()->getPdo()`：MySQL 连接成功。

回滚：删除新项目目录。原 `php-test` 项目不受影响。

### 阶段 B：登录功能

状态：初版已完成，后续认证页面已扩展

内容：

- 安装 Laravel UI 1.x（锁定 1.3.0）。
- 复用项目已有认证 Controller 和阶段 A 的 Vue 基础，不重复生成脚手架。
- 删除注册和密码重置入口。
- 日文化登录页面和错误消息。
- 创建管理员 Seeder。
- 使用 `auth` middleware 保护管理页面和 JSON 路由。

验收：

- 管理员可以登录和登出。
- 未登录用户不能访问管理页面和业务 JSON。
- 注册后可使用数据库中的新账号登录；邮箱验证路由仍关闭。
- 默认管理员只能在本地和测试环境使用。

回滚：移除认证路由和脚手架，保留阶段 A 基础项目。

已完成：

- 增加日文登录页、登录/登出路由和表单验证提示。
- Composer 开发依赖已安装 Laravel UI 1.3.0。
- `/admin/*` 管理入口通过 `auth` 中间件保护，未登录访问跳转到 `/login`。
- 阶段 B 初版关闭注册和密码重置；后续开放注册、密码找回、重置、确认和修改页面；邮箱验证仍关闭。
- 开发管理员 Seeder 只允许在 `local` 和 `testing` 环境执行；仅在 `users` 表为空时读取 `ADMIN_EMAIL`、`ADMIN_PASSWORD` 创建初始账号，之后以数据库账号信息为准。
- 默认语言设为日文。

验证结果：

- 数据库迁移和默认开发管理员 Seeder 成功。
- `/login` 返回 200；未登录访问 `/admin/applications` 返回 302 并跳转 `/login`。
- `/register` 可访问并将新账号写入数据库；`/email/verify` 返回 404；认证页面由 Vue 组件渲染。
- 重置密码邮件由 Laravel 密码代理处理；实际投递需配置 SMTP。
- 当前 PHPUnit：39 项测试、181 条断言通过；PHP 类/方法/行覆盖率均为 100%（23/23、49/49、208/208）。
- 当前 Jest：6 个测试套件、19 项测试通过；项目整体行覆盖率为 54.12%。
- 注册、密码确认/找回/重置及登录后的密码修改均有后端路由；邮箱验证路由保持关闭。
- `php artisan route:list` 确认 `/admin/{path?}` 使用 `web,auth` 中间件。
- `npm run development` 成功，并已更新本地可读的前端资源。

后续角色权限扩展（2026-10-09）：

- 新增 `users.role` 迁移，默认 `viewer`；将现有 `admin@example.com` 提升为 `super_admin`。本地数据库已执行迁移并核对该账号角色。新安装时 Seeder 创建的初始账号为 `super_admin`。
- `viewer` 只读，`editor` 可读/新增/编辑，`super_admin` 还可删除和管理角色。公开注册不能提交角色提权；后端以 Gate 拒绝越权 API 请求（403）。
- `/admin/users` 供最高管理者分配角色。不能降级 `admin@example.com` 或最后一名最高管理者；修改结果写入数据库。
- 验收：角色边界、非法角色值、最高管理者保护、注册默认只读，均有后端测试；Vue 权限入口及角色保存有前端测试。
- 风险：公开注册尚无邮箱验证和人工审核；上线前应另加限制。现有旧版 PHP/Laravel/Vue 仍有维护风险。
- 回滚：先导出 `users` 表及角色，再回退 `2026_10_09_000000_add_role_to_users_table` 迁移和相应应用代码；回退迁移会删除全部角色数据，不应直接在生产库执行。

角色表迁移（2026-10-09）：

- 新增 `roles` 表，存放角色代码、日文名称和三个权限开关；`users.role_id` 外键关联，默认指向 `viewer`。迁移将旧 `users.role` 值逐一转为外键后才删除旧字段，未知旧值按只读处理。
- 已在本地开发库执行迁移：三条角色记录正确，`admin@example.com` 关联 `super_admin`；账号邮箱和密码未改。新安装按两个迁移顺序自动完成。
- `/admin/users` 从数据库获取角色选项并显示权限矩阵；页面只负责分配已有角色，不提供在线修改角色定义或权限开关。若后续需要编辑角色权限，须增加安全校验和单独验收。
- 验收：PHPUnit 38 项、167 条断言通过，`app/` 统计范围内的类/方法/行覆盖率均为 100%；Jest 18 项通过，开发构建通过。测试确认修改 `roles` 权限字段会改变实际后端授权。
- 回滚：先备份 `users` 与 `roles`。回退 `2026_10_09_010000_create_roles_table` 会把角色代码写回 `users.role` 并删除角色表和权限开关；如果角色权限曾定制，回退将丢失这些权限值，不得无备份执行。

用户逻辑删除扩展（2026-10-09）：

- `2026_10_09_020000_add_status_to_users_table` 为 `users` 增加默认 `active` 的 `status` 字段，已有账号保持可用；新安装执行 `php artisan migrate --force` 即可获得相同结构。
- `/admin/users` 显示账号状态；最高管理者可确认后将其他账号标记为 `deleted`，也可恢复为 `active`。接口为 `PATCH /admin/api/v1/users/{id}/status`，只更新状态，不执行物理删除；用户及其关联数据保持原样。
- 接口同时检查用户管理 Gate 和 `super_admin` 角色；其他角色即使获得用户管理权限也不能删除账号。禁止删除自己和 `admin@example.com`；最后一名可用最高管理者因此不会被删除。
- `deleted` 账号不能登录、请求或使用密码重置，也不能凭旧会话继续访问受保护路由。恢复后仍使用原有账号和密码。
- 验收：本地开发库迁移前后均为 2 条用户记录，迁移后均为 `active`，无账号丢失。覆盖逻辑删除/恢复、越权、受保护账号、旧会话、登录及密码重置；PHPUnit 44 项、209 条断言通过，`app/` 范围的类/方法/行覆盖率均为 100%；Jest 21 项通过，开发构建成功。
- 回滚：先备份 `users`，将需要保留的已删除账号恢复为 `active` 后再回退此迁移及应用代码；回退迁移会删除 `status` 字段及全部删除状态，勿在生产库无备份执行。

页面去 Blade 扩展（2026-10-09）：

- 所有实际页面路由改为返回同一份静态 `resources/spa.html`，由 Vue 根据 URL 渲染认证或后台界面；不再调用 `view()`、`Route::view()`。历史阶段 A 的 Blade 入口描述仅记录当时实现，当前以本节为准。
- 后台启动前请求受登录保护的 `GET /admin/api/v1/session` 获取用户名、日文角色名和 Gate 权限，写入 Vuex 后再挂载页面；不再通过 Blade 向 HTML 注入状态。
- 表单与退出登录经 Axios 发送同源请求，使用 Laravel 发出的 `XSRF-TOKEN` Cookie。静态页面禁缓存，避免旧 CSRF 页面；真实 HTTP 匿名请求验证错误登录返回 422 而非 419。
- 原 `admin.blade.php`、`auth/login.blade.php`、`welcome.blade.php` 当时未删除，现已按用户确认删除；`resources/views/.gitkeep` 仅保留空目录。其他脚手架文件未动。
- 删除后执行 `php artisan view:clear` 清除已编译的旧视图；PHPUnit 39 项、181 条断言通过。实际 HTTP `/login` 返回 200，未登录 `/admin/applications` 返回 302。已提交过的旧模板版本可从 Git 恢复；删除前未提交的模板改动未单独备份。
- 验收：PHPUnit 39 项、181 条断言通过，`app/` 统计范围内类/方法/行覆盖率 100%；Jest 19 项通过（整体行覆盖率 54.12%），前端开发构建通过。静态页面有 XSRF Cookie；真实匿名 POST 错误登录返回 422，未出现 419。回滚时恢复原页面路由、Controller 返回值及 Blade 状态注入，并恢复前端 CSRF 提交方式。

### 阶段 C：数据库和后端

状态：已完成

内容：

- 创建 `simulation_applications` migration。
- 创建 Model、Factory 和 Seeder。
- 生成 120 条示例数据。
- 创建 Controller、Form Request 和 Resource。
- 实现 CRUD、搜索、筛选和分页。
- 增加 Feature Test。

验收：

- MySQL 中有 1 个管理员和 120 条申请。
- Seeder 可重复执行。
- CRUD 正常。
- 非法参数返回 422。
- 未登录请求不能访问业务数据。
- Laravel 测试通过。

回滚：回滚业务 migration，移除业务 API 和模型；认证功能保留。

已实现：

- 按第 9 节字段定义建立 `simulation_applications` migration、Model 和 Factory。
- 增加 120 条本地/测试示例申请，五种状态各 24 条；Seeder 使用 `firstOrCreate` 防止重复插入，重复执行时只刷新标准示例的日文姓名和备注，保留其他申请字段。
- 示例申请姓名和备注使用日文；Faker 区域设置为 `ja_JP`，重跑本地 Seeder 会将既有 120 条标准示例申请的申请人、被保险人、受取人姓名及备注更新为日文。
- 增加认证保护的同源 REST API、Form Request、JSON Resource，支持 CRUD、关键词搜索、状态筛选和白名单分页。
- 业务 API 放在 Web Session/CSRF 中间件组中，和管理页面共用登录态。
- 增加 Feature Test；测试使用单独的 `laravel_testing` MySQL 数据库，避免触碰开发数据。

验证结果：

- 开发库中管理员 1 个、申请 120 条，五种状态各 24 条。
- API 未登录返回 401；登录后可以 CRUD，未找到记录返回 JSON 404，非法数据和分页参数返回 422。
- Seeder 重复执行不增加记录；标准示例姓名和备注统一刷新为日文，其他业务字段保持不变。
- `php artisan route:list` 显示全部业务 API 使用 `web,auth` 中间件。
- `php vendor/bin/phpunit` 完整套件：9 项测试、46 条断言通过。
- 独立 `laravel_testing` 数据库完成迁移，开发库仍保留 1 个管理员和 120 条申请。

### 阶段 D：Vue 管理页面

状态：已完成

内容：

- 配置 Vue Router 和 Vuex。
- 创建通用基础组件。
- 创建一览、详情、登记和编辑页面。
- 实现删除确认。
- 实现搜索、状态筛选、分页和每页条数。
- 实现加载、空数据和错误状态。
- 所有显示文字日文化。

验收：

- 登录后可以完成完整 CRUD。
- 搜索只在点击按钮后执行。
- 分页和每页条数正确。
- 页面刷新后路由仍可访问。
- 浏览器控制台无错误。
- Vue 测试和生产构建通过。

回滚：移除 Vue 业务页面和业务 store，后端 API 与登录功能保留。

已实现：

- 配置 Vue Router、Vuex 和同源 Axios API 客户端。
- 新增表格、按钮、输入框、选择框、文本域、字段错误和加载/错误/空状态等通用组件。
- 实现日文申请一览、详情、登记和编辑页面；支持删除确认、搜索、状态筛选、分页和每页条数。
- 搜索仅在提交搜索表单时发起请求；新增/编辑表单展示后端字段校验错误。
- 增加 Jest/Vue Test Utils 测试配置和前端组件/列表交互测试。

代码级验证结果：

- `npm test -- --watchAll=false`：2 个测试套件、5 项测试通过。
- `npm run production`：Laravel Mix 生产构建成功。
- Docker 容器内 `php vendor/bin/phpunit`：9 项测试、47 条断言通过。
- 浏览器已验证申请列表、关键词搜索、详情页刷新、登记页和编辑页；浏览器控制台未发现警告或错误。
- 本地数据库 120 条申请的申请人、被保险人、受取人姓名及备注均已更新为日文；全表英文字符检查结果为 0。
- 阶段 D 提交：`c402caf`（`Implement Stage D Vue admin screens`）。

### 阶段 E：最终验证和文档

状态：已完成

内容：

- 完整回归测试。
- 验证首次安装手顺。
- 验证数据库迁移和 Seeder。
- 验证开发和生产构建。
- 编写 README 和架构文档。
- 检查默认脚手架、Storybook 依赖和未使用设置；按用户此前要求保留现有文件，不删除 Laravel 默认脚手架。

验收：

- README 提供首次依赖安装、环境初始化、迁移、Seeder 和启动步骤；Node 14/npm 6 锁文件安装已在临时环境验证。
- 后端测试、前端测试和生产构建通过。
- 项目不存在 Storybook 依赖和配置。
- 阶段 E 当时保留 Laravel Welcome 模板和未使用默认脚手架；后续已按用户新要求删除 Welcome 模板。
- 项目目录在计划中使用 `{USERPATH}`，README/架构文档使用仓库相对链接。

验证结果：

- Node.js `14.21.3` / npm `6.14.18` 临时干净环境：`npm ci` 安装 1717 个包；Jest 5 项测试、开发构建、生产构建均通过。
- Composer 2.2 临时干净环境：根据 `composer.lock` 安装 91 个 PHP 包，自动加载和项目的 package discovery 脚本执行成功。锁文件包含旧版 Laravel/Symfony 弃用包警告，属项目指定旧技术栈风险。
- 当前项目：Jest 2 个套件、5 项测试通过；PHPUnit 9 项测试、47 条断言通过；`npm run development` 和 `npm run production` 均成功。
- 数据库迁移状态全部为已执行；开发库 120 条申请，英文姓名/备注记录数为 0，空备注数为 0。
- MySQL Compose 默认字符集/排序规则已固定为 `utf8mb4` / `utf8mb4_unicode_ci`；本地开发库默认字符集元数据同步完成，表和记录未重建或改写。
- 未发现 Storybook 依赖或配置。阶段 E 当时保留默认 Welcome 页面；后续删除模板的结果见上文，并在 [ARCHITECTURE.md](ARCHITECTURE.md) 记录。

回滚：文档和清理操作按 Git diff 单独恢复，不影响已验证功能。

## 16. 不在本次范围

- 邮箱验证和注册审核。
- 可在线编辑角色定义与权限开关的页面。
- 审批流程和审批历史。
- 负责人分配。
- 软删除和审计日志。
- 文件上传。
- 报表和导出。
- 批量操作。
- 多语言切换。
- Storybook。
- PostgreSQL 数据迁移。
- API Token 或 OAuth 认证。

## 17. 完成标准

- 使用客户指定版本完成构建。
- Laravel 内嵌 Vue 2 管理页面。
- 所有显示文字为日文。
- 管理员登录、登出和访问保护可用。
- 申込 CRUD、搜索、筛选和分页可用。
- MySQL 有 120 条可重复生成的示例数据。
- 前后端校验和错误显示可用。
- 后端测试、Vue 测试和生产构建全部通过。
- README 包含首次构建、启动、数据库更新和测试方法。
- 架构文档与实际代码一致。
- 不导入 Storybook。

## 18. 进度记录

| 日期 | 阶段 | 状态 | 结果 |
| --- | --- | --- | --- |
| 2026-10-08 | 需求收敛 | 已完成 | 技术版本、业务范围、认证、数据库、日文界面和 Storybook 方针已确认 |
| 2026-10-08 | 开发计划 | 已完成 | 已生成本文档并按阶段 A 实施 |
| 2026-10-08 | 阶段 A | 已完成（Docker 运行环境） | Laravel/Vue 基础工程、锁文件、Blade + Vue 管理入口、PHP 7.4 应用容器、MySQL 5.7.44 容器和 root/root 连接验证均完成；主机 PHP 7.4 已删除，后续 Laravel 命令使用 Docker |
| 2026-10-08 | 阶段 B | 已完成 | 日文登录/登出、管理入口 auth 保护、关闭注册和密码重置、local/testing 专用管理员 Seeder 均已完成；提交 `765577f` |
| 2026-10-08 | 阶段 C | 已完成 | 申请表、模型、Factory、120 条幂等示例 Seeder、认证保护的 CRUD/搜索/筛选/分页 API 均完成；当前后端回归为 9 项测试、47 条断言通过，开发库数据完整 |
| 2026-10-08 | 阶段 D | 已完成 | Vue 管理页及 CRUD 交互完成；前端 5 项测试、生产构建、浏览器主要页面/搜索/刷新验证及控制台检查通过；提交 `c402caf` |
| 2026-10-08 | 示例数据日文化 | 已完成 | 本地 120 条申请的申请人、被保险人、受取人姓名及备注均为日文；全表检查无英文内容 |
| 2026-10-08 | 阶段 E | 已完成 | README 首次安装和数据库说明、架构文档完成；Node 14/npm 6 与 Composer 2.2 临时干净安装，PHPUnit/Jest/开发与生产构建及浏览器联测通过；npm 锁文件已改为 npm 6 兼容格式；默认脚手架按用户要求保留 |


## 2026-10-09：前后端分离（两步执行）

用户要求先只迁移文件并提交，再修改文件，第二步完成后统一验收。

- 第一步已提交 `6fa45f7`：166 个文件内容完全一致的迁移；0 行新增、0 行删除。迁移前 Jest 21 tests，PHPUnit 44 tests/209 assertions 通过。
- 第二步：frontend/backend 独立构建和服务、统一 `/api/v1`、Session/CSRF/CORS、统一 Vue Router 与 Axios、前端重置邮件链接、Jest/Storybook/PHPUnit 路径更新、文档同步。
- 实测修复旧 Laravel 无效 XSRF 密文抛 500 的情况，改为 419 并补回归。
- 验证使用新 Compose project 和数据库 volume；原数据库未重建或覆盖。原旧服务不能直接使用迁移后的根目录配置。
- 最终验证：Jest 27 tests；PHPUnit 50 tests/232 assertions；38 项真实 HTTP 检查（包含旧 Session 和记住登录 Cookie 重放）；development/production 与 Storybook 构建；两端独立镜像构建、后端干净 Composer 安装、代理/直连浏览器登录退出、搜索及深层页面刷新通过。第二步待用户验收。
- 第二步保持未提交，供用户验收；不自动创建第二次 commit 或推送。
- 保留限制：旧版本依赖警告、真实 SMTP 未验证、正式生产服务/HTTPS 未配置、原业务数据未导入新验证库。

回退：审查第二步 diff，撤回本次改动并按第一步迁移记录恢复原目录；保留原数据库 volume 和实施前用户改动。不能仅回到文件迁移 commit 就宣称旧服务可运行，因为那时配置尚未调整。
