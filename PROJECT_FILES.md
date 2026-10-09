# 项目文件说明

本文按目录说明仓库中每个项目文件的职责。Laravel 框架脚手架文件也列在内，并标明它们的作用；“脚手架”表示文件由 Laravel 项目结构提供，不代表运行时无用。`vendor/`、`node_modules/`、个人 `.env`、缓存、日志和覆盖率报告由安装或运行产生，不属于项目源文件清单。

## 根目录

- `.babelrc`：配置 Babel 如何转换前端 JavaScript，使 Vue 和 Jest 使用项目兼容的语法。
- `.editorconfig`：统一编辑器的缩进、换行符和文件末尾格式。
- `.env.example`：新环境的 Laravel 配置模板，提供应用、数据库、缓存、邮件等环境变量名称；不放个人密钥。
- `.gitattributes`：配置 Git 对文本换行、导出归档等文件属性的处理方式。
- `.gitignore`：声明不提交的本地环境配置、依赖目录、缓存、日志及构建报告。
- `.nvmrc`：指定项目使用的 Node.js 版本，供 nvm 切换运行时。
- `.styleci.yml`：配置 PHP 代码风格检查规则。
- `.storybook/main.js`：配置 Storybook 扫描 Vue 组件故事、加载 Essentials 插件、使用 Webpack 4 并编译项目 Sass。
- `.storybook/preview.js`：为所有故事加载项目全局样式，并设置 Controls、Actions 和故事排序。
- `ARCHITECTURE.md`：说明系统分层、前后端请求流、认证授权和数据库职责。
- `DEVELOPMENT_PLAN.md`：记录需求、数据库设计、开发阶段、验收结果及回滚方式。
- `Dockerfile`：构建运行 Laravel 的 PHP 7.4 应用镜像，并安装项目所需扩展及工具。
- `docker-compose.yml`：定义 PHP 应用和 MySQL 服务、端口、共享目录、数据库环境变量及持久化数据卷。
- `INSTALLATION.md`：说明从首次安装到启动项目、登录和日常维护的操作步骤。
- `README.md`：项目入口文档，包含概览、安装链接、常用命令、数据库说明和本文件清单入口。
- `artisan`：Laravel 命令行入口；执行迁移、Seeder、路由检查和清缓存等命令时由 PHP 调用。
- `composer.json`：声明 PHP 版本约束、Laravel 依赖、自动加载规则及 Composer 生命周期脚本。
- `composer.lock`：锁定 PHP 依赖的具体版本，保证不同机器安装相同依赖集合。
- `jest.config.js`：配置 Jest 如何发现前端测试、编译 Vue 单文件组件和收集覆盖率。
- `package.json`：声明 Node/npm 版本、前端依赖，以及开发构建、生产构建、测试和覆盖率命令。
- `package-lock.json`：锁定 npm 依赖的确切版本，供 `npm ci` 可重复安装。
- `phpunit.xml`：配置 PHPUnit 的测试目录、环境变量和独立测试数据库连接。
- `server.php`：Laravel 本地 PHP 开发服务器的请求转发入口。
- `webpack.mix.js`：定义 Laravel Mix 如何编译 Vue/JavaScript、Sass，并输出到 `public/`。
- `scripts/package-discover.php`：Composer 安装后运行 Laravel 包自动发现；项目脚本用于兼容 PHP 7.4 环境。

## `app/`：Laravel 应用逻辑

### `app/Console/`、`app/Exceptions/`

- `app/Console/Kernel.php`：注册 Artisan 命令和计划任务；当前没有额外的定时业务任务。
- `app/Exceptions/Handler.php`：集中记录异常并将异常转换为 HTTP 或 API 响应。

### `app/Http/`

- `app/Http/Kernel.php`：登记全局、Web、API 中间件，并定义 `auth`、`can` 等路由中间件别名。
- `app/Http/Controllers/Controller.php`：Laravel 控制器基类，供业务控制器继承。
- `app/Http/Controllers/Api/AdminSessionController.php`：返回当前登录用户 ID、名称、角色和权限，供 Vue 初始化后台会话。
- `app/Http/Controllers/Api/SimulationApplicationController.php`：处理保险申入列表、搜索、筛选、分页、详情、新增、更新和删除。
- `app/Http/Controllers/Api/UserRoleController.php`：返回用户及角色权限信息，分配角色，并由最高管理者修改其他账号的启用状态。
- `app/Http/Controllers/Auth/ChangePasswordController.php`：验证当前密码和新密码，更新密码哈希与 remember token，并清除二次确认时间。
- `app/Http/Controllers/Auth/ConfirmPasswordController.php`：显示并处理访问敏感页面前的密码二次确认。
- `app/Http/Controllers/Auth/ForgotPasswordController.php`：显示忘记密码页面并发送密码重置邮件；拒绝已标记删除的账号。
- `app/Http/Controllers/Auth/LoginController.php`：显示登录入口、验证账号密码并退出登录；拒绝已标记删除的账号。
- `app/Http/Controllers/Auth/RegisterController.php`：处理公开注册，新账号默认关联只读角色。
- `app/Http/Controllers/Auth/ResetPasswordController.php`：校验邮件令牌并重置密码；拒绝已标记删除的账号。
- `app/Http/Controllers/Auth/VerificationController.php`：Laravel 邮箱验证控制器脚手架；验证路由当前保持关闭。
- `app/Http/Middleware/Authenticate.php`：检查认证状态，并在每次受保护请求时重新核对账号状态；停用账号会注销当前会话。
- `app/Http/Middleware/CheckForMaintenanceMode.php`：维护模式中间件脚手架，维护期间阻止普通请求。
- `app/Http/Middleware/EncryptCookies.php`：加密和解密浏览器 Cookie，保护 Session 等 Cookie 内容。
- `app/Http/Middleware/RedirectIfAuthenticated.php`：访客中间件；已登录用户访问登录或注册页时跳转后台。
- `app/Http/Middleware/TrimStrings.php`：清理请求中字符串字段首尾空格的中间件。
- `app/Http/Middleware/TrustProxies.php`：指定可信代理，正确读取代理转发的协议和客户端地址。
- `app/Http/Middleware/VerifyCsrfToken.php`：校验 Web 表单和同源 API 请求中的 CSRF 令牌。
- `app/Http/Requests/SimulationApplicationIndexRequest.php`：验证申入列表的搜索词、状态、页码和每页数量。
- `app/Http/Requests/SimulationApplicationRequest.php`：集中验证新建和修改申入的字段、日期、金额及状态。
- `app/Http/Resources/SimulationApplicationResource.php`：将申入模型转换为稳定的 API JSON 字段结构。
- `app/Models/SimulationApplication.php`：定义申入模型、可批量写入字段和业务状态常量。
- `app/Providers/AppServiceProvider.php`：应用服务提供者；用于注册全局服务和应用启动配置。
- `app/Providers/AuthServiceProvider.php`：定义申请写入、删除和用户管理的 Gate 权限，并从数据库角色字段判断授权。
- `app/Providers/BroadcastServiceProvider.php`：广播服务提供者脚手架；项目当前没有实时广播功能。
- `app/Providers/EventServiceProvider.php`：登记 Laravel 事件与监听器；当前没有额外业务事件映射。
- `app/Providers/RouteServiceProvider.php`：配置路由加载、路由命名空间和认证后默认跳转地址。
- `app/Role.php`：角色模型；将三个角色权限字段转换为布尔值，供 Gate 判断授权。
- `app/User.php`：用户认证模型；关联角色，定义账号状态常量和可写用户字段。

## `bootstrap/`：应用启动

- `bootstrap/app.php`：创建 Laravel 应用容器，并绑定 HTTP、CLI 和异常处理核心类。
- `bootstrap/cache/.gitignore`：保留框架缓存目录结构，忽略运行时生成的缓存文件。

## `config/`：Laravel 配置

- `config/app.php`：应用名称、环境、时区、语言、密钥和服务提供者配置。
- `config/auth.php`：用户认证 Guard、Session 驱动和用户数据提供者配置。
- `config/broadcasting.php`：Laravel 广播驱动配置；当前项目未启用广播。
- `config/cache.php`：缓存存储驱动和缓存前缀配置。
- `config/database.php`：MySQL、SQLite 等数据库连接参数及迁移表配置。
- `config/filesystems.php`：本地和公开文件存储磁盘配置。
- `config/hashing.php`：密码哈希算法及 bcrypt 强度配置。
- `config/logging.php`：日志通道、日志级别和日志文件配置。
- `config/mail.php`：邮件发送方式、SMTP 地址、认证信息和发件人配置。
- `config/queue.php`：队列驱动和失败任务记录表配置。
- `config/services.php`：第三方服务凭证配置入口。
- `config/session.php`：会话存储、有效期、Cookie 和 CSRF 相关 Session 设置。
- `config/view.php`：视图文件路径及编译缓存目录；项目页面由静态入口和 Vue 渲染。

## `database/`：表结构、测试数据和初始化数据

- `database/.gitignore`：忽略本地 SQLite 数据库文件，避免误提交个人数据库。
- `database/factories/SimulationApplicationFactory.php`：为测试生成申入模型所需的字段数据。
- `database/factories/UserFactory.php`：为测试生成用户模型所需的账号数据。
- `database/migrations/2014_10_12_000000_create_users_table.php`：创建 Laravel 用户表及初始认证字段。
- `database/migrations/2014_10_12_100000_create_password_resets_table.php`：创建密码重置令牌表。
- `database/migrations/2019_08_19_000000_create_failed_jobs_table.php`：创建队列失败任务记录表。
- `database/migrations/2026_10_08_000000_create_simulation_applications_table.php`：创建保险模拟申入表及列表查询索引。
- `database/migrations/2026_10_09_000000_add_role_to_users_table.php`：为现有用户结构补充角色关联所需的过渡字段。
- `database/migrations/2026_10_09_010000_create_roles_table.php`：创建角色和权限表，将用户角色迁移到 `users.role_id` 外键。
- `database/migrations/2026_10_09_020000_add_status_to_users_table.php`：为用户添加 `active` / `deleted` 状态，支持逻辑删除和恢复。
- `database/seeds/DatabaseSeeder.php`：Laravel Seeder 总入口，按顺序调用本地管理员和申入示例 Seeder。
- `database/seeds/DevelopmentAdminSeeder.php`：仅在本地或测试环境、且用户表为空时创建初始最高管理者和角色。
- `database/seeds/SimulationApplicationSeeder.php`：创建或刷新 120 条日文示例申入，不重复创建标准样例。

## `public/`：Web 入口和公开资源

- `public/.htaccess`：Apache 重写规则，将应用请求转到 Laravel 前端控制器。
- `public/favicon.ico`：浏览器标签页显示的网站图标。
- `public/index.php`：Web 请求的 Laravel 启动入口。
- `public/css/app.css`：Laravel Mix 编译生成的样式文件；修改源样式请编辑 `resources/sass/app.scss`。
- `public/js/app.js`：Laravel Mix 编译生成的 Vue 应用；修改前端逻辑请编辑 `resources/js/` 下的源文件。
- `public/js/app.js.LICENSE.txt`：构建器生成的 JavaScript 第三方依赖许可证清单。
- `public/mix-manifest.json`：Laravel Mix 生成的资源文件映射，供页面加载带版本的 CSS/JS。
- `public/robots.txt`：控制搜索引擎爬虫是否抓取网站内容。
- `public/web.config`：IIS 服务器的 URL 重写和 Web 入口配置。

## `resources/`：Vue 源码、页面入口、语言和样式

### `resources/` 根目录

- `resources/spa.html`：所有页面共用的静态 HTML 壳，提供 Vue 挂载点和构建资源入口，不包含 Blade 模板逻辑。
- `resources/sass/app.scss`：全站 Sass 样式源文件，编译后输出到 `public/css/app.css`。
- `resources/views/.gitkeep`：保留 Laravel 预期的视图目录；实际页面不使用 Blade 文件。

### `resources/js/`

- `resources/js/app.js`：创建 Vue 应用并装配路由、Vuex、Axios 和页面启动逻辑。
- `resources/js/bootstrap.js`：初始化 Axios 默认配置、CSRF Cookie 读取和全局前端依赖。
- `resources/js/api/simulationApplications.js`：封装申入 API 的请求方法，供 Vue 页面复用。
- `resources/js/router/index.js`：定义后台 Vue 路由和页面组件映射。
- `resources/js/store/index.js`：存储当前用户会话、申入列表、分页、筛选、加载和错误状态。
- `resources/js/components/ApplicationsCreateConfirmation.vue`：显示新申入确认信息，确认后才提交创建请求。
- `resources/js/components/ApplicationsCreateConfirmation.stories.js`：展示申入确认页的待提交和提交中状态。
- `resources/js/components/AuthForm.vue`：复用认证页面的表单布局和提交状态处理。
- `resources/js/components/BaseButton.vue`：统一按钮外观、禁用和提交中的状态。
- `resources/js/components/BaseButton.stories.js`：展示主要、次要和禁用按钮，提供按钮类型、样式与禁用状态控件。
- `resources/js/components/BaseErrorMessage.vue`：以一致样式显示字段或表单错误。
- `resources/js/components/BaseErrorMessage.stories.js`：展示字段错误信息及可交互的错误文案。
- `resources/js/components/BaseInput.vue`：封装带标签和校验错误的文本输入框。
- `resources/js/components/BaseInput.stories.js`：展示普通文本和日期输入，并提供输入类型等控件。
- `resources/js/components/BaseSelect.vue`：封装带标签和错误提示的下拉选择框。
- `resources/js/components/BaseSelect.stories.js`：展示申入状态下拉框、选项及必填状态。
- `resources/js/components/BaseTable.vue`：封装管理页面中重复使用的表格布局。
- `resources/js/components/BaseTable.stories.js`：用日文申入样例展示表格列、数据行和操作插槽。
- `resources/js/components/BaseTextarea.vue`：封装带标签和错误提示的多行文本框。
- `resources/js/components/BaseTextarea.stories.js`：展示备注输入框及其双向输入状态。
- `resources/js/components/ContentState.vue`：统一显示加载中、空结果和错误等内容状态。
- `resources/js/components/ContentState.stories.js`：展示加载中、无结果、错误和正常内容状态，并提供重试事件操作。
- `resources/js/components/SimulationApplicationForm.vue`：复用申入新增和修改表单字段、输入绑定及校验显示。
- `resources/js/components/SimulationApplicationForm.stories.js`：展示空白申入表单和带服务端校验错误的表单。
- `resources/js/components/__tests__/form-components.spec.js`：验证基础表单控件和错误提示的渲染行为。
- `resources/js/views/AdminApp.vue`：管理后台布局入口，装配导航、会话信息和子页面。
- `resources/js/views/ApplicationsCreate.vue`：收集新申入字段并进入确认步骤，确认后调用创建 API。
- `resources/js/views/ApplicationsEdit.vue`：读取既有申入并提交修改。
- `resources/js/views/ApplicationsIndex.vue`：显示申入列表，支持搜索、状态筛选、分页和授权操作。
- `resources/js/views/ApplicationsShow.vue`：显示单条申入详情并提供可用操作入口。
- `resources/js/views/AuthApp.vue`：根据当前 URL 装载登录、注册、密码确认或密码找回页面。
- `resources/js/views/PasswordChange.vue`：提供登录用户验证旧密码并设置新密码的页面。
- `resources/js/views/UserRoles.vue`：管理用户角色，并允许最高管理者逻辑删除或恢复其他账号。
- `resources/js/views/auth/ConfirmPassword.vue`：输入当前密码以确认敏感操作身份。
- `resources/js/views/auth/EmailVerification.vue`：邮箱验证页面组件；对应验证路由尚未开放。
- `resources/js/views/auth/ForgotPassword.vue`：提交邮箱以申请密码重置邮件。
- `resources/js/views/auth/Login.vue`：提交邮箱和密码进行登录。
- `resources/js/views/auth/Register.vue`：创建新用户账号；页面不提供自选角色提权入口。
- `resources/js/views/auth/ResetPassword.vue`：使用邮件令牌设置新密码。
- `resources/js/views/__tests__/AdminApp.spec.js`：验证后台布局、会话信息和权限入口。
- `resources/js/views/__tests__/ApplicationsCreate.spec.js`：验证申入新增和确认后提交流程。
- `resources/js/views/__tests__/ApplicationsIndex.spec.js`：验证申入列表、搜索和筛选交互。
- `resources/js/views/__tests__/UserRoles.spec.js`：验证角色保存、用户逻辑删除、恢复及最高管理者界面权限。
- `resources/js/views/auth/__tests__/auth-pages.spec.js`：验证登录、注册、密码确认和密码找回页面交互。

### `resources/lang/`

- `resources/lang/en/auth.php`：Laravel 认证相关英文消息。
- `resources/lang/en/pagination.php`：Laravel 分页控件英文标签。
- `resources/lang/en/passwords.php`：Laravel 密码重置流程英文消息。
- `resources/lang/en/validation.php`：Laravel 输入校验英文消息。
- `resources/lang/ja/auth.php`：Laravel 认证相关日文消息。
- `resources/lang/ja/passwords.php`：Laravel 密码重置流程日文消息。
- `resources/lang/ja/validation.php`：Laravel 输入校验日文消息。

## `routes/`：HTTP 路由

- `routes/web.php`：注册登录、注册、密码管理、静态 SPA 页面和受 Session/CSRF 保护的后台 API。
- `routes/api.php`：Laravel API 路由脚手架；当前业务 API 使用 Web Session，因此主要路由在 `web.php`。
- `routes/channels.php`：Laravel 私有广播频道授权脚手架；当前没有启用广播。
- `routes/console.php`：定义可在 Artisan 命令行执行的闭包命令；当前没有额外业务命令。

## `storage/`：运行时目录占位

以下 `.gitignore` 文件用于保留 Laravel 写入日志、缓存、会话、编译视图和上传文件的目录。实际运行数据由 Git 忽略。

- `storage/app/.gitignore`：保留应用私有文件存储目录。
- `storage/app/public/.gitignore`：保留应用公开文件存储目录。
- `storage/framework/.gitignore`：保留 Laravel 框架运行时目录。
- `storage/framework/cache/.gitignore`：保留框架缓存目录。
- `storage/framework/cache/data/.gitignore`：保留文件缓存数据目录。
- `storage/framework/sessions/.gitignore`：保留文件 Session 存储目录。
- `storage/framework/testing/.gitignore`：保留测试期间的框架临时目录。
- `storage/framework/views/.gitignore`：保留 Blade 编译视图缓存目录；项目页面不依赖 Blade 模板。
- `storage/logs/.gitignore`：保留 Laravel 日志目录。

## `tests/`：自动化测试

- `tests/CreatesApplication.php`：从 Laravel 启动脚本创建测试用应用实例。
- `tests/TestCase.php`：项目 PHPUnit 基类，提供 Laravel 测试环境。
- `tests/Feature/AccountManagementTest.php`：验证注册、密码修改、密码确认和密码重置等账号流程。
- `tests/Feature/AuthPagesTest.php`：验证认证页面路由返回 SPA 页面及相关 HTTP 状态。
- `tests/Feature/AuthenticationTest.php`：验证登录、退出和未登录访问保护行为。
- `tests/Feature/ExampleTest.php`：Laravel 默认示例 Feature 测试脚手架。
- `tests/Feature/FrameworkScaffoldTest.php`：确认保留的 Laravel 框架路由和基础设施可用。
- `tests/Feature/RetainedAuthScaffoldTest.php`：验证保留的认证控制器脚手架与当前认证路由配置。
- `tests/Feature/RoleAuthorizationTest.php`：验证角色权限边界、角色分配和最高管理者保护。
- `tests/Feature/SimulationApplicationsApiTest.php`：验证申入 API 的查询、校验、创建、更新和删除。
- `tests/Feature/UserDeletionTest.php`：验证用户逻辑删除和恢复、访问限制、权限及关联数据保留。
- `tests/Unit/ExampleTest.php`：Laravel 默认示例 Unit 测试脚手架。
- `tests/Unit/SimulationApplicationTest.php`：验证申入模型的状态和字段行为。

## 当前测试未使用的目录占位文件

- `bootstrap/cache/.gitignore`、`database/.gitignore`、`storage/**/.gitignore`：只用于让 Git 保留空目录并忽略运行文件，不含业务逻辑。
- `resources/views/.gitkeep`：只保留 Laravel 的视图目录；页面由 `resources/spa.html` 和 Vue 负责。
