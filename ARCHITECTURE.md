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
  ├─ GET /login
  │    └─ Laravel 登录表单
  └─ GET /admin/{path}
       └─ auth 中间件 -> admin.blade.php -> Vue AdminApp
            ├─ Vue Router -> 一览 / 登记 / 详情 / 编辑
            ├─ Vuex -> 列表、筛选、分页状态
            └─ Axios -> /admin/api/v1/simulation-applications
                 └─ web + auth + CSRF -> Controller
                      -> Form Request 校验 -> Eloquent -> MySQL
```

`/admin/{path}` 由 Laravel 返回同一 Blade 入口，因此 Vue Router 的 history 路由可直接刷新。未登录访问管理页会进入登录页面。业务 API 使用 Laravel Web Session 认证和 CSRF，不使用单独的 Token 服务。

## 3. 后端边界

- `routes/web.php` 注册登录、登出、管理页入口和同源业务 API。
- `SimulationApplicationController` 负责 CRUD、关键词搜索、状态筛选和分页。
- `SimulationApplicationRequest` 校验新增/更新字段；`SimulationApplicationIndexRequest` 校验列表查询参数。
- `SimulationApplicationResource` 统一 JSON 响应格式。
- `SimulationApplication` 定义状态常量及可写字段。
- `simulation_applications` 表由 `2026_10_08_000000_create_simulation_applications_table.php` 创建；按状态和生效日建立联合索引。

API 基础路径：`/admin/api/v1/simulation-applications`

| 方法 | 路径 | 用途 |
| --- | --- | --- |
| GET | `/` | 列表、搜索、筛选、分页 |
| POST | `/` | 新增申请 |
| GET | `/{id}` | 申请详情 |
| PUT / PATCH | `/{id}` | 更新申请 |
| DELETE | `/{id}` | 删除申请 |

本地管理员及示例申请 Seeder 仅在 `local`、`testing` 环境运行。标准示例共 120 条，五种状态各 24 条；姓名和备注为日文。重跑会刷新标准样例的姓名和备注，不重复创建记录，也不改动其余业务字段。

## 4. 前端边界

- `resources/views/admin.blade.php` 注入 CSRF token 和当前管理员名称，并载入 Mix 编译产物。
- `resources/js/app.js` 创建 Vue 实例并装配 Router、Vuex。
- `resources/js/router/index.js` 定义 `/admin/applications` 下的页面路由。
- `resources/js/api/simulationApplications.js` 封装同源 API 请求。
- `resources/js/store/index.js` 管理申请列表、分页、筛选、加载和错误状态。
- `resources/js/components/` 保存表单控件、表格、错误提示及内容状态组件。
- `resources/js/views/` 保存管理布局、申请一览、登记、详情和编辑页面。

界面显示文案为日文；API 校验错误由日文 Laravel validation 字典提供。

## 5. 数据库与测试隔离

- 开发数据库：`laravel`，用户名和密码均为 `root`。
- 测试数据库：`laravel_testing`，PHPUnit 配置强制指定该库。
- PHPUnit Feature Test 使用 `RefreshDatabase`，只清理测试数据库。
- 前端 Jest 测试覆盖基础表单控件、字段错误和列表搜索提交行为。
- 生产构建使用 `npm run production` 输出 `public/js`、`public/css` 和 Mix manifest。

安装、迁移、Seeder 和测试命令以 [README.md](README.md) 为准。

## 6. 保留的 Laravel 脚手架

项目中仍保留 Laravel 初始工程自带的控制器、语言文件、路由文件、默认测试和 `resources/views/welcome.blade.php` 等脚手架内容。当前路由未使用 Welcome 页。按用户此前要求，阶段E仅确认并记录其用途，不删除这些文件或依赖；如需精简，另行确认后执行。
