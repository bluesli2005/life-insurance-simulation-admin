# 项目文件与边界

更新日期：2026-10-10。下列文件清单来自当前工作树，包括未提交的新组件，排除 Git 忽略的依赖、环境文件、运行缓存和生成产物。

## 目录结构

```text
仓库根目录/
├── docker-compose.yml           独立 frontend/backend/mysql 服务
├── *.md                         架构、安装、维护记录与目录说明
├── frontend/
│   ├── src/                     Vue 页面、基础组件、API、路由、状态、样式与 Jest
│   ├── public/                  静态页面模板、favicon 与 robots
│   ├── scripts/                 独立 Node 静态／API 代理服务
│   ├── .storybook/              基础控件 Storybook 配置
│   ├── dist/                    当前静态构建产物（忽略，按需构建）
│   └── node_modules/            本地前端依赖（忽略）
└── backend/
    ├── app/                     Controller、Request、Resource、Model、Middleware、Gate
    ├── bootstrap/、config/      Laravel 启动与配置，bootstrap/cache 为运行所需
    ├── database/                migrations、factories、seeds
    ├── public/                  API 的 PHP 入口，无 Vue 构建产物
    ├── resources/               服务端语言与框架视图扩展目录
    ├── routes/                  API 与 Laravel 路由配置
    ├── scripts/                 Composer package discovery 辅助脚本
    ├── storage/                 Laravel 文件、Session、缓存、日志等运行目录
    ├── tests/                   PHPUnit Feature／Unit 测试
    └── vendor/                  后端依赖（忽略）
```

前端入口为 src/app.js、App.vue 和 router/index.js；管理布局为 AdminApp.vue。权限下拉使用 BaseSelect，成功提示使用 BaseToast；Toast 示例和计时测试位于同一 components 模块。请求统一由 api/client.js 管理。

后端入口为 public/index.php，业务接口为 routes/api.php，Session/CORS/CSRF 在 Kernel 与 Middleware 中配置。composer.lock、package-lock.json 均保留，使用锁文件安装。

根目录迁移前的空目录已移除。未使用的 coverage 与 storybook-static 已清理；命令会在各端目录重新生成它们。当前 frontend/dist、依赖目录、后端运行目录及必要框架配置保留，不按“空目录”批量删除。

## 当前维护文件清单

```text
.editorconfig
.gitattributes
.gitignore
ARCHITECTURE.md
DEVELOPMENT_PLAN.md
INSTALLATION.md
PROJECT_FILES.md
README.md
backend/.dockerignore
backend/.env.example
backend/.styleci.yml
backend/Dockerfile
backend/app/Console/Kernel.php
backend/app/Exceptions/Handler.php
backend/app/Http/Controllers/Api/AdminSessionController.php
backend/app/Http/Controllers/Api/SimulationApplicationController.php
backend/app/Http/Controllers/Api/UserRoleController.php
backend/app/Http/Controllers/Auth/ChangePasswordController.php
backend/app/Http/Controllers/Auth/ConfirmPasswordController.php
backend/app/Http/Controllers/Auth/ForgotPasswordController.php
backend/app/Http/Controllers/Auth/LoginController.php
backend/app/Http/Controllers/Auth/RegisterController.php
backend/app/Http/Controllers/Auth/ResetPasswordController.php
backend/app/Http/Controllers/Auth/VerificationController.php
backend/app/Http/Controllers/Controller.php
backend/app/Http/Kernel.php
backend/app/Http/Middleware/ApiCors.php
backend/app/Http/Middleware/Authenticate.php
backend/app/Http/Middleware/CheckForMaintenanceMode.php
backend/app/Http/Middleware/EncryptCookies.php
backend/app/Http/Middleware/RedirectIfAuthenticated.php
backend/app/Http/Middleware/TrimStrings.php
backend/app/Http/Middleware/TrustProxies.php
backend/app/Http/Middleware/VerifyCsrfToken.php
backend/app/Http/Requests/SimulationApplicationIndexRequest.php
backend/app/Http/Requests/SimulationApplicationRequest.php
backend/app/Http/Resources/SimulationApplicationResource.php
backend/app/Models/SimulationApplication.php
backend/app/Providers/AppServiceProvider.php
backend/app/Providers/AuthServiceProvider.php
backend/app/Providers/BroadcastServiceProvider.php
backend/app/Providers/EventServiceProvider.php
backend/app/Providers/RouteServiceProvider.php
backend/app/Role.php
backend/app/User.php
backend/artisan
backend/bootstrap/app.php
backend/bootstrap/cache/.gitignore
backend/composer.json
backend/composer.lock
backend/config/app.php
backend/config/auth.php
backend/config/broadcasting.php
backend/config/cache.php
backend/config/cors.php
backend/config/database.php
backend/config/filesystems.php
backend/config/hashing.php
backend/config/logging.php
backend/config/mail.php
backend/config/queue.php
backend/config/services.php
backend/config/session.php
backend/config/view.php
backend/database/.gitignore
backend/database/factories/SimulationApplicationFactory.php
backend/database/factories/UserFactory.php
backend/database/migrations/2014_10_12_000000_create_users_table.php
backend/database/migrations/2014_10_12_100000_create_password_resets_table.php
backend/database/migrations/2019_08_19_000000_create_failed_jobs_table.php
backend/database/migrations/2026_10_08_000000_create_simulation_applications_table.php
backend/database/migrations/2026_10_09_000000_add_role_to_users_table.php
backend/database/migrations/2026_10_09_010000_create_roles_table.php
backend/database/migrations/2026_10_09_020000_add_status_to_users_table.php
backend/database/seeds/DatabaseSeeder.php
backend/database/seeds/DevelopmentAdminSeeder.php
backend/database/seeds/SimulationApplicationSeeder.php
backend/phpunit.xml
backend/public/.htaccess
backend/public/index.php
backend/public/web.config
backend/resources/lang/en/auth.php
backend/resources/lang/en/pagination.php
backend/resources/lang/en/passwords.php
backend/resources/lang/en/validation.php
backend/resources/lang/ja/auth.php
backend/resources/lang/ja/passwords.php
backend/resources/lang/ja/validation.php
backend/resources/views/.gitkeep
backend/routes/api.php
backend/routes/channels.php
backend/routes/console.php
backend/routes/web.php
backend/scripts/package-discover.php
backend/server.php
backend/storage/app/.gitignore
backend/storage/app/public/.gitignore
backend/storage/framework/.gitignore
backend/storage/framework/cache/.gitignore
backend/storage/framework/cache/data/.gitignore
backend/storage/framework/sessions/.gitignore
backend/storage/framework/testing/.gitignore
backend/storage/framework/views/.gitignore
backend/storage/logs/.gitignore
backend/tests/CreatesApplication.php
backend/tests/Feature/AccountManagementTest.php
backend/tests/Feature/ApiBoundaryTest.php
backend/tests/Feature/AuthPagesTest.php
backend/tests/Feature/AuthenticationTest.php
backend/tests/Feature/ExampleTest.php
backend/tests/Feature/FrameworkScaffoldTest.php
backend/tests/Feature/RetainedAuthScaffoldTest.php
backend/tests/Feature/RoleAuthorizationTest.php
backend/tests/Feature/SimulationApplicationsApiTest.php
backend/tests/Feature/UserDeletionTest.php
backend/tests/TestCase.php
backend/tests/Unit/ExampleTest.php
backend/tests/Unit/SimulationApplicationTest.php
docker-compose.yml
frontend/.babelrc
frontend/.dockerignore
frontend/.env.example
frontend/.nvmrc
frontend/.storybook/main.js
frontend/.storybook/preview.js
frontend/Dockerfile
frontend/jest.config.js
frontend/nginx.conf
frontend/package-lock.json
frontend/package.json
frontend/public/favicon.ico
frontend/public/index.html
frontend/public/robots.txt
frontend/scripts/serve.js
frontend/src/App.vue
frontend/src/api/__tests__/client.spec.js
frontend/src/api/auth.js
frontend/src/api/client.js
frontend/src/api/simulationApplications.js
frontend/src/app.js
frontend/src/components/ApplicationsCreateConfirmation.stories.js
frontend/src/components/ApplicationsCreateConfirmation.vue
frontend/src/components/AuthForm.vue
frontend/src/components/BaseButton.stories.js
frontend/src/components/BaseButton.vue
frontend/src/components/BaseErrorMessage.stories.js
frontend/src/components/BaseErrorMessage.vue
frontend/src/components/BaseInput.stories.js
frontend/src/components/BaseInput.vue
frontend/src/components/BaseSelect.stories.js
frontend/src/components/BaseSelect.vue
frontend/src/components/BaseTable.stories.js
frontend/src/components/BaseTable.vue
frontend/src/components/BaseTextarea.stories.js
frontend/src/components/BaseTextarea.vue
frontend/src/components/BaseToast.stories.js
frontend/src/components/BaseToast.vue
frontend/src/components/ContentState.stories.js
frontend/src/components/ContentState.vue
frontend/src/components/SimulationApplicationForm.stories.js
frontend/src/components/SimulationApplicationForm.vue
frontend/src/components/__tests__/BaseToast.spec.js
frontend/src/components/__tests__/form-components.spec.js
frontend/src/router/__tests__/session.spec.js
frontend/src/router/index.js
frontend/src/store/index.js
frontend/src/styles/app.scss
frontend/src/views/AdminApp.vue
frontend/src/views/ApplicationsCreate.vue
frontend/src/views/ApplicationsEdit.vue
frontend/src/views/ApplicationsIndex.vue
frontend/src/views/ApplicationsShow.vue
frontend/src/views/AuthApp.vue
frontend/src/views/PasswordChange.vue
frontend/src/views/UserRoles.vue
frontend/src/views/__tests__/AdminApp.spec.js
frontend/src/views/__tests__/ApplicationsCreate.spec.js
frontend/src/views/__tests__/ApplicationsIndex.spec.js
frontend/src/views/__tests__/UserRoles.spec.js
frontend/src/views/auth/ConfirmPassword.vue
frontend/src/views/auth/EmailVerification.vue
frontend/src/views/auth/ForgotPassword.vue
frontend/src/views/auth/Login.vue
frontend/src/views/auth/Register.vue
frontend/src/views/auth/ResetPassword.vue
frontend/src/views/auth/__tests__/auth-pages.spec.js
frontend/webpack.mix.js
```
