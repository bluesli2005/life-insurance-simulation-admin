# 系统架构

## 前后端边界

```text
浏览器
  页面／资源 → frontend nginx :8080
  /api/v1    → frontend 代理 → backend PHP :8000 → mysql:3306
  可选直连   → localhost:8001/api/v1（明确 CORS origin + credentials）

frontend/                         backend/
Vue 2 / Router / Vuex             Laravel 6.18 / PHP 7.4
Axios / Jest / Storybook          Session / CSRF / Gate / PHPUnit
npm + webpack.mix.js              Composer + artisan
dist/ 静态产物                    public/index.php API 入口
```

根目录仅组织部署、规范和文档。前端构建不依赖后端源码、PHP 或数据库；后端不安装 npm、不读取 frontend/dist、不返回管理页面。两端通过 HTTP 通信，可以分别提取为独立仓库。

## 前端组织与交互

- `src/app.js` 创建 Vue 实例；`App.vue` 根据路由与会话选择 `AdminApp` 或 `AuthApp`。
- `router/index.js` 管理认证和申込／用户管理页面；history fallback 由 frontend 服务处理。静态资源缺失返回 404，API 不进入页面 fallback。
- `AdminApp.vue` 管理头部账号、左侧菜单和右侧主体。Vue Router 的 active-class 区分当前模块，申込新增／详情／编辑页面保持申込一覧选中。権限管理菜单按会话权限显示。
- `store/index.js` 保存会话、权限和申込列表状态；搜索按钮触发查询，分页参数与后端统一。
- `api/client.js` 共用 Axios 配置与错误处理；`auth.js`、`simulationApplications.js` 封装接口。
- 基础控件位于 `src/components/`；权限下拉使用 `BaseSelect`，支持禁用状态；`AuthForm` 和 `SimulationApplicationForm` 共用认证／申込表单。
- 三个表单入口都启用 `novalidate`。页面用日文行内错误反馈，后端继续做最终校验；保留输入类型和必要约束属性。
- `BaseToast` 通过 `message`、`duration` 接收提示，默认 3000ms 后发出 `dismiss`，父页面清空消息，Vue transition 执行 300ms fade-out。新消息重置计时，组件销毁清理计时器。顶部固定居中，背景 `rgba(254, 249, 195, .65)`，深色字，`role=status`；减少动态效果设置下禁用动画。
- 用户管理成功消息接入 Toast；失败消息继续显示页面 error。详情页编辑／删除为蓝／红按钮，hover 加深背景并显示轻微阴影，禁用删除按钮不响应 hover。

## 认证与权限

启动 GET `/api/v1/auth/csrf` 初始化 Cookie；Router 查询 `/auth/session` 更新数据库权限。匿名保护路由转 /login，已登录 guest 页面转 /admin/applications；网络失败显示日文反馈。

Axios 使用 JSON 请求头、withCredentials、XSRF-TOKEN Cookie 和 X-XSRF-TOKEN header。401 清除会话并导航；419 不自动重放写操作。

Laravel API 路由使用 `session.api`：Cookie 加密、Cookie 响应、Session、CSRF 和模型绑定；认证使用 web guard。ApiCors 处理明确 origin 的预检并向错误响应添加 CORS 信息。登录／注册更新 Session，退出销毁会话。

权限由数据库 roles 和 Gate 判定。viewer 只读，editor 可创建／编辑，super_admin 可删除和管理用户。前端按钮仅辅助交互，后端独立验证权限。被标记 deleted 的账号不能登录、重置密码或继续使用旧会话；保护账号和最后最高管理员规则保留。

## API 路径

下表统一以 `/api/v1` 为前缀：

| 方法 | 路径 | 用途 |
| --- | --- | --- |
| GET | /auth/csrf | CSRF 初始化，204 |
| POST | /auth/login、/auth/logout | 登录／退出，204 |
| POST | /auth/register | 默认 viewer 注册，201 |
| GET | /auth/session | 用户与 Gate 权限 |
| POST | /auth/password/confirm、/auth/password/change | 密码确认／修改 |
| POST | /auth/password/email、/auth/password/reset | 找回／重置 |
| GET / POST | /simulation-applications | 搜索分页／创建 |
| GET / PUT / PATCH / DELETE | /simulation-applications/{id} | 详情／更新／删除 |
| GET | /users | 用户与角色矩阵 |
| PATCH | /users/{id}/role、/users/{id}/status | 权限／账号状态更新 |

错误状态：401 未认证、403 无权限／不允许 origin、404 不存在、409 已登录调用 guest 接口、419 CSRF 失效、422 输入校验、429 限流。业务 Resource 保留 data、links、meta 和 validation errors。

Password Broker 通过后端 `FRONTEND_URL` 生成 `/password/reset/{token}?email=...` 邮件链接；重置后保留自动登录行为。邮箱验证未开放，相关框架扩展点保留。

## 运行与数据

Compose project 为 `life-insurance-separated`；backend 仅挂载 `./backend`，frontend 镜像仅以 `./frontend` 构建，最终使用 nginx 静态镜像。8001、8080 绑定回环地址；MySQL 无主机端口，使用独立 volume。

开发库 `laravel`、测试库 `laravel_testing` 分离；开发账号 root/root。原业务数据库不自动导入；目录清理不操作数据库或 Docker volume。Session Cookie HttpOnly、SameSite=lax；跨站部署需单独配置和验证 HTTPS、Cookie domain 与 SameSite。

`frontend/node_modules`、`backend/vendor` 为当前依赖；`frontend/dist` 为独立静态服务产物；`backend/bootstrap/cache` 和 `backend/storage` 为 Laravel 必要运行目录，均不能当作迁移遗留目录删除。coverage 和 storybook-static 是可重新生成的报告／构建产物。

## 验证与限制

Jest 覆盖组件、页面、请求和路由，Toast 测试验证计时与销毁；Storybook 用于基础控件预览。PHPUnit 覆盖认证、业务、权限、账号状态、CORS 和 CSRF。PHPUnit 通过不等于真实 CSRF／Cookie／SMTP 已验收，HTTP 和浏览器验证需单独记录。

固定旧版本依赖、正式生产应用服务／HTTPS、旧数据导入、真实 SMTP 投递仍为限制。本次维护记录与实际验证结果见 DEVELOPMENT_PLAN.md。
