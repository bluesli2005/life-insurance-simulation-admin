# シミュレーション申込・管理（admin画面）开发计划

## 1. 文档目的

本文记录“シミュレーション申込・管理（admin画面）”的需求、技术约束、数据库设计、开发阶段、验收条件、回滚方法和进度。

当前阶段 A、B、C 已完成。每个阶段完成后先验证并报告结果，再进入下一阶段。

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
  -> Blade 登录页面 / Vue 2 管理页面入口
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

最低限度范围：

- 管理员登录。
- 管理员登出。
- 未登录访问管理页面时跳转到登录页。
- 不开放用户注册。
- 不实现忘记密码和邮件发送。
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
| `/admin/applications` | 申込一覧 | 搜索、筛选、分页、进入详情 |
| `/admin/applications/create` | 申込登録 | 新建申请 |
| `/admin/applications/:id` | 申込詳細 | 显示申请详情、编辑和删除入口 |
| `/admin/applications/:id/edit` | 申込編集 | 修改申请 |

管理页面使用 Vue Router。Laravel 为 `/admin` 下的 Vue 路由提供同一个 Blade 入口，并使用 `auth` middleware 保护。

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
| `remember_token` | varchar(100) nullable | Laravel 认证字段 |
| `created_at` / `updated_at` | timestamp | Laravel 时间戳 |

当前只有管理员用户，不增加角色表和权限表。

### 9.2 `simulation_applications`

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

未知管理路径回到申込一覧。Laravel 登录页不由 Vue Router 管理。

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
- Node.js/npm 主机版本仍高于项目目标版本；当前 `npm run dev` 和 `npm run production` 已通过，但 Node.js 14.21.3 仍应通过 `.nvmrc` 或独立容器复核。

环境备注：Composer 安全策略会阻止 Laravel 6.18 的已知安全风险依赖。本地按客户固定版本生成锁文件时使用了一次性 `--no-security-blocking`；生产环境不得忽略安全审计。`composer.json` 已固定 PHP 7.4.33 平台，`composer.lock` 已降到 PHP 7.4 可用依赖。

已验证：

- `docker compose ps`：PHP 7.4 应用和 MySQL 5.7.44 均为运行状态，MySQL 健康检查通过。
- `docker exec ... php -v`：PHP 7.4.33。
- `docker exec ... php artisan --version`：Laravel Framework 6.18.0。
- `curl -I http://127.0.0.1:8000/admin/applications`：HTTP 200，响应头显示 PHP 7.4.33。
- Laravel `DB::connection()->getPdo()`：MySQL 连接成功。

回滚：删除新项目目录。原 `php-test` 项目不受影响。

### 阶段 B：登录功能

状态：已完成

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
- 注册页面不存在。
- 默认管理员只能在本地和测试环境使用。

回滚：移除认证路由和脚手架，保留阶段 A 基础项目。

已完成：

- 增加日文登录页、登录/登出路由和表单验证提示。
- Composer 开发依赖已安装 Laravel UI 1.3.0。
- `/admin/*` 管理入口通过 `auth` 中间件保护，未登录访问跳转到 `/login`。
- 未启用注册和密码重置；对应页面返回 404。
- 增加开发管理员 Seeder，只允许在 `local` 和 `testing` 环境执行，支持 `ADMIN_EMAIL`、`ADMIN_PASSWORD` 环境变量；示例账号为 `admin@example.com` / `password`，仅限本地环境。
- 默认语言设为日文。

验证结果：

- 数据库迁移和默认开发管理员 Seeder 成功。
- `/login` 返回 200；未登录访问 `/admin/applications` 返回 302 并跳转 `/login`。
- `/register` 和 `/password/reset` 返回 404。
- `php artisan route:list` 确认 `/admin/{path?}` 使用 `web,auth` 中间件。
- `npm run production` 成功。

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
- 增加 120 条本地/测试示例申请，五种状态各 24 条；Seeder 使用 `firstOrCreate`，重复运行不重复插入或覆盖现有申请。
- 增加认证保护的同源 REST API、Form Request、JSON Resource，支持 CRUD、关键词搜索、状态筛选和白名单分页。
- 业务 API 放在 Web Session/CSRF 中间件组中，和管理页面共用登录态。
- 增加 Feature Test；测试使用单独的 `laravel_testing` MySQL 数据库，避免触碰开发数据。

验证结果：

- 开发库中管理员 1 个、申请 120 条，五种状态各 24 条。
- API 未登录返回 401；登录后可以 CRUD，未找到记录返回 JSON 404，非法数据和分页参数返回 422。
- Seeder 重复执行不增加记录，也不覆盖已编辑的申请。
- `php artisan route:list` 显示全部业务 API 使用 `web,auth` 中间件。
- `php vendor/bin/phpunit` 完整套件：9 项测试、46 条断言通过。
- 独立 `laravel_testing` 数据库完成迁移，开发库仍保留 1 个管理员和 120 条申请。

### 阶段 D：Vue 管理页面

状态：未开始

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

### 阶段 E：最终验证和文档

状态：未开始

内容：

- 完整回归测试。
- 验证首次安装手顺。
- 验证数据库迁移和 Seeder。
- 验证开发和生产构建。
- 编写 README 和架构文档。
- 清理默认示例文件和未使用设置。

验收：

- 新环境可按 README 完成首次构建。
- 后端测试、前端测试和生产构建通过。
- 项目不存在 Storybook 依赖和配置。
- 项目不存在未使用的 Laravel Welcome 示例代码。
- 文档路径统一使用 `{USERPATH}`。

回滚：文档和清理操作按 Git diff 单独恢复，不影响已验证功能。

## 16. 不在本次范围

- 公开注册。
- 忘记密码和邮件发送。
- 多角色权限。
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
| 2026-10-08 | 阶段 C | 已完成 | 申请表、模型、Factory、120 条幂等示例 Seeder、认证保护的 CRUD/搜索/筛选/分页 API 均完成；PHPUnit 9 项测试和 46 条断言通过，开发库数据完整 |
| - | 阶段 D | 未开始 | - |
| - | 阶段 E | 未开始 | - |
