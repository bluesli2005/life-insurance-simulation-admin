# 系统架构

## 1. 前后端边界

```text
浏览器
  页面与资源：frontend 静态服务 :8080
  API：默认 /api/v1 经静态服务代理到 backend :8000
       或直接访问 localhost:8001/api/v1（CORS + credentials）

frontend/                       backend/
Vue 2 / Router / Vuex           Laravel 6.18 / PHP 7.4
统一 Axios client               Session / CSRF / Gate
独立 npm / Jest / Storybook      Composer / PHPUnit / MySQL 5.7
构建 frontend/dist              仅 backend/public/index.php
```

前端安装、构建、测试和 Storybook 不需要后端源码、PHP 或数据库。后端不安装 npm，不返回前端页面、不读取前端 dist。两者通过 HTTP 协议通信，可提取为各自仓库。

## 2. 认证与请求

前端启动 GET `/api/v1/auth/csrf` 初始化会话 Cookie。Router 查询 `/auth/session` 并加载数据库权限；匿名保护路由导航到 /login，已登录 guest 页面导航到 /admin/applications。网络失败显示日文信息，避免登录循环。

共用 Axios client 使用可配置 API 地址、JSON 请求头、withCredentials、XSRF-TOKEN Cookie 与 X-XSRF-TOKEN header。登录、注册、退出和密码请求也使用该 client。401 清除会话并导航；校验、权限、CSRF 与限流错误有对应日文反馈；419 不自动重放写操作。

后端 routes/api.php 使用 session.api 中间件组：Cookie 加密、响应 Cookie、Session、CSRF 和模型绑定；auth 使用 web guard。ApiCors 先处理明确 origin 的预检，所有服务响应使用 JSON，错误也携带 CORS 信息。登录、注册更新 Session，退出销毁会话。

## 3. API 路径

所有路径以 `/api/v1` 为前缀。

| 方法 | 路径 | 作用 |
| --- | --- | --- |
| GET | /auth/csrf | CSRF 初始化，204 |
| POST | /auth/login、/auth/logout | 登录/退出，204 |
| POST | /auth/register | 注册，201，默认 viewer |
| GET | /auth/session | 用户及 Gate 权限，200 |
| POST | /auth/password/confirm | 密码确认，204 |
| POST | /auth/password/change | 修改密码，200 |
| POST | /auth/password/email、/auth/password/reset | 找回/重置密码，200 |
| GET / POST | /simulation-applications | 列表/创建，200/201 |
| GET / PUT / PATCH / DELETE | /simulation-applications/{id} | 查询/更新/删除，200 |
| GET | /users | 用户及权限矩阵，200 |
| PATCH | /users/{id}/role、/users/{id}/status | 角色与状态更新，200 |

错误状态：401 未认证、403 无权限/不允许 origin、404 不存在、409 已登录调用 guest 接口、419 CSRF 无效、422 输入校验、429 限流。未知页面与旧 API 不做永久兼容。

业务响应保留 Resource 的 data、links、meta 与 validation errors。写操作由后端 Gate 校验，前端按钮显示仅辅助交互。账号 status 变为 deleted 后，登录、密码重置和已有会话请求均被拒绝；角色权限取 roles 表。保护账号与最后最高管理员规则保持。

## 4. 页面与密码邮件

frontend/src/app.js 创建统一 Vue 实例。App 根据路由切换 AdminApp/AuthApp，Router 管理所有开放页面；history fallback 由前端服务完成。前端静态 JS/CSS 缺失返回 404，API 不被 fallback 吞掉。

保留申请 CRUD、搜索按钮提交、分页、创建确认与用户管理。后端 Password Broker 负责密码重置；邮件通过现有通知扩展点，使用 FRONTEND_URL 生成 `/password/reset/{token}?email=...`。重置成功保留自动登录行为。邮箱验证未开放。

## 5. 部署与数据

Compose project 为 life-insurance-separated，backend 挂载 ./backend，frontend 镜像仅由 ./frontend 构建。8001/8080 绑定回环地址。MySQL 使用独立 volume，不暴露主机端口。

默认同源代理，支持同一 localhost 不同端口直连。CORS 允许明确 origin 并携带凭证，不能用通配符；Session Cookie HttpOnly、SameSite=lax。跨站或生产子域需单独配置与验证 HTTPS、Cookie domain、SameSite 等。

开发库 laravel 与测试库 laravel_testing 分离；开发账号 root/root。原数据库不自动迁入新环境。数据库、APP_KEY、SMTP 密码只在后端环境中；前端构建变量均为公开信息。

## 6. 验证与限制

Jest 覆盖原组件/页面及共用 client、路由守卫；PHPUnit 覆盖业务、认证、权限、账号状态、CORS 和 CSRF。实际 HTTP 验证另外检查 Cookie、Session 与无效 token，避免测试环境跳过 CSRF 产生误判。

依赖按旧版本约束保留。PHP Artisan serve 用于开发与验收；本次没有完成正式生产服务、HTTPS、旧数据导入或真实 SMTP 投递验收。
