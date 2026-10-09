# 系统架构

## 1. 技术组成

| 层 | 技术 | 运行位置 |
| --- | --- | --- |
| 管理界面 | Vue 2、Vue Router、Vuex、Axios、Laravel Mix | 浏览器 |
| Web 与 API | Laravel 6.18、PHP 7.4、Eloquent | Docker `app` 容器 |
| 数据库 | MySQL 5.7.44 | Docker `mysql` 容器 |
| 前端测试 | Jest 26、Vue Test Utils 1 | Node.js 14 / npm 6 |
| 后端测试 | PHPUnit 9 | Docker `app` 容器，连接 `laravel_testing` |

本地 Docker Compose 将应用暴露在 `127.0.0.1:8000`，MySQL 暴露在 `127.0.0.1:3306`；MySQL 默认字符集/排序规则为 `utf8mb4` / `utf8mb4_unicode_ci`。应用容器通过服务名 `mysql:3306` 连接数据库。

## 2. 请求与界面流程

```text
浏览器
  ├─ GET /login、/register
  │    └─ Laravel 返回 resources/spa.html -> Vue AuthApp -> 登录/注册页面
  ├─ GET /password/*
  │    └─ Laravel 认证 Controller -> 同一静态 HTML 启动壳
  └─ GET /admin/{path}
       └─ auth 中间件 -> resources/spa.html -> GET /admin/api/v1/session -> Vue AdminApp
            ├─ Vue Router -> 一览 / 登记 / 详情 / 编辑 / 修改密码 / 权限管理
            ├─ Vuex -> 列表、筛选、分页状态
            └─ Axios -> /admin/api/v1/simulation-applications
                 └─ web + auth + CSRF -> Controller
                      -> Form Request 校验 -> Eloquent -> MySQL
```

登录、注册、密码和后台页面均由 Vue 管理。Laravel 只返回不含动态模板语法的 `resources/spa.html`；Vue 根据 URL 启动认证或后台界面。认证表单通过 Axios 使用 Laravel Session 和 `XSRF-TOKEN` Cookie 保护；退出登录也使用同源 POST。

`/admin/{path}` 由 Laravel 返回同一静态 HTML 入口，因此 Vue Router 的 history 路由可直接刷新。未登录访问管理页会进入登录页面。后台启动前请求受 `auth` 保护的 `/admin/api/v1/session`，获取姓名、角色名称和 Gate 权限并写入 Vuex；导航和操作按钮从该状态读取。业务 API 使用 Laravel Web Session 认证和 CSRF，不使用单独的 Token 服务。HTML 响应禁缓存，以免 CSRF Cookie 过期时复用旧页面。

## 3. 后端边界

- `routes/web.php` 注册登录、注册、登出、密码确认/重置/修改、管理页入口和同源业务 API；邮箱验证路由保持关闭。
- `RegisterController` 将新账号以 `viewer` 角色写入 `users` 表；`ChangePasswordController` 验证旧密码后更新哈希与 remember token。
- `AuthServiceProvider` 定义写入、删除和用户管理权限；后端 Gate 读取 `roles` 表的权限字段。`UserRoleController` 仅供有用户管理权限者列出用户和修改角色；禁止降级 `admin@example.com` 或最后一名最高管理者。
- `SimulationApplicationController` 负责 CRUD、关键词搜索、状态筛选和分页。
- `SimulationApplicationRequest` 校验新增/更新字段；`SimulationApplicationIndexRequest` 校验列表查询参数。
- `SimulationApplicationResource` 统一 JSON 响应格式。
- `SimulationApplication` 定义状态常量及可写字段。
- `simulation_applications` 表由 `2026_10_08_000000_create_simulation_applications_table.php` 创建；按状态和生效日建立联合索引。

API 基础路径：`/admin/api/v1/simulation-applications`

公开注册默认只读；`super_admin` 可读写、删除和管理角色，`editor` 可读写，`viewer` 只读。`roles` 表保存角色代码、日文名称及写入/删除/用户管理权限标记；`users.role_id` 外键引用它。权限管理 API：`GET /admin/api/v1/users`（返回用户和角色权限矩阵）、`PATCH /admin/api/v1/users/{id}/role`（分配已有角色）；注册请求中的角色字段不被接受。

| 方法 | 路径 | 用途 |
| --- | --- | --- |
| GET | `/` | 列表、搜索、筛选、分页 |
| POST | `/` | 新增申请 |
| GET | `/{id}` | 申请详情 |
| PUT / PATCH | `/{id}` | 更新申请 |
| DELETE | `/{id}` | 删除申请 |

本地管理员及示例申请 Seeder 仅在 `local`、`testing` 环境运行。标准示例共 120 条，五种状态各 24 条；姓名和备注为日文。重跑会刷新标准样例的姓名和备注，不重复创建记录，也不改动其余业务字段。

## 4. 前端边界

- `resources/spa.html` 是唯一实际使用的静态页面入口；不使用 Blade 模板。`AdminSessionController` 提供当前用户名、数据库角色名称和 Gate 权限标记。
- `resources/js/app.js` 创建 Vue 实例并装配 Router、Vuex。
- `resources/js/views/AuthApp.vue` 与 `resources/js/views/auth/` 管理登录、注册、密码确认和找回/重置密码；邮箱验证组件暂不开放。
- `resources/js/views/PasswordChange.vue` 管理登录后的密码修改表单；`UserRoles.vue` 管理角色分配界面。
- `resources/js/router/index.js` 定义 `/admin/applications` 下的页面路由。
- `resources/js/api/simulationApplications.js` 封装同源 API 请求。
- `resources/js/store/index.js` 管理当前会话权限、申请列表、分页、筛选、加载和错误状态。
- `resources/js/components/` 保存表单控件、表格、错误提示及内容状态组件。
- `resources/js/views/` 保存管理布局、申请一览、登记、详情和编辑页面。

界面显示文案为日文；API 校验错误由日文 Laravel validation/passwords 字典提供。重置密码邮件需在 `.env` 配置可用的 SMTP。

## 5. 数据库与测试隔离

- 开发数据库：`laravel`，用户名和密码均为 `root`。
- 管理员邮箱和密码哈希以 `users` 表为准；角色定义和权限以 `roles` 表为准，`users.role_id` 记录用户所属角色。开发 Seeder 仅在 `users` 为空时创建最高管理者；迁移保留了现有 `admin@example.com` 的最高权限。
- 测试数据库：`laravel_testing`，PHPUnit 配置强制指定该库。
- PHPUnit Feature Test 使用 `RefreshDatabase`，只清理测试数据库。
- 前端 Jest 测试覆盖基础表单控件、字段错误和列表搜索提交行为。
- 生产构建使用 `npm run production` 输出 `public/js`、`public/css` 和 Mix manifest。

安装、迁移、Seeder 和测试命令以 [README.md](README.md) 为准。

## 6. 保留的 Laravel 脚手架

项目中仍保留 Laravel 初始工程自带的控制器、语言文件、路由文件和默认测试等脚手架内容。原 `resources/views/admin.blade.php`、`resources/views/auth/login.blade.php`、`resources/views/welcome.blade.php` 已按用户确认删除；`resources/views/.gitkeep` 仅保留 Laravel 的视图目录，不参与页面渲染。
