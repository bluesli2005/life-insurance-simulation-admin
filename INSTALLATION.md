# 首次安装说明

适用于首次在本机安装本项目。所有命令在项目根目录执行。安装会初始化本地开发数据库并写入示例申请数据；不要直接用于生产环境。

## 1. 准备环境

- 安装并启动 Docker Desktop，确认 Docker 可以运行容器。
- 安装 nvm。项目通过 `.nvmrc` 固定 Node.js `14.21.3`，对应 npm `6.14.18`。
- 克隆或取得项目代码，并在终端进入项目根目录。

本机不需要安装 PHP、MySQL 或 Composer：PHP 7.4 和 MySQL 5.7 在 Docker 中运行，Composer 通过 Composer 容器执行。

## 2. 配置项目与前端依赖

```sh
cp .env.example .env
nvm install
nvm use
node --version
npm --version
npm ci
npm run production
```

版本检查应分别显示 `v14.21.3` 和 `6.14.18`。如果 `nvm` 命令不可用，先按 nvm 官方说明安装并重新打开终端。`.env.example` 已配置本地数据库 `laravel` 和开发账号；Docker Compose 会将应用容器的数据库地址设为 `mysql`。

## 3. 启动 MySQL 并安装 PHP 依赖

```sh
docker compose up -d mysql
docker compose ps
docker run --rm -v "$PWD":/app -w /app composer:2.2 install
```

等待 MySQL 显示为运行/健康状态。首次运行 `composer:2.2` 时 Docker 可能需要下载镜像；Composer 会把 PHP 依赖安装到项目的 `vendor` 目录。

## 4. 构建应用并初始化数据库

```sh
docker compose build app
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan migrate --force
docker compose run --rm app php artisan db:seed --force
docker compose up -d app
```

`key:generate` 会将应用密钥写入本地 `.env`。迁移会建立数据表，Seeder 会创建本地管理员并写入 120 条日文示例申请。

## 5. 登录并确认

打开 <http://127.0.0.1:8000/login>，使用本地默认账号登录：

- 邮箱：`admin@example.com`
- 密码：`password`

可在首次运行 Seeder 前于 `.env` 设置 `ADMIN_EMAIL`、`ADMIN_PASSWORD`，以创建自定义本地初始账号。只要 `users` 表已有账号，重跑 Seeder 不会改写邮箱或密码。此默认账号仅供本地开发，不可用于生产环境。

登录、注册及密码相关页面由 Vue 渲染。可在 `/register` 注册账号，在登录后的 `/admin/password` 修改密码。角色定义和权限保存在独立的 `roles` 表，用户通过 `users.role_id` 关联；新账号默认 `viewer`，只能查看申入。`editor` 可新增和编辑；`super_admin` 还可删除申入，并在 `/admin/users` 分配角色、标记删除或恢复其他账号。用户删除仅将 `users.status` 改为 `deleted`，不移除记录；被删除账号不能登录。默认 `admin@example.com` 为最高管理者，不能标记删除。邮箱验证和注册审核仍关闭。若要接收密码重置邮件，请在 `.env` 配置 `MAIL_HOST`、`MAIL_PORT`、`MAIL_USERNAME`、`MAIL_PASSWORD`、`MAIL_ENCRYPTION` 和 `MAIL_FROM_ADDRESS`，然后重启应用容器。

所有页面经 Laravel 路由返回同一份静态 `resources/spa.html`，由 Vue 显示；不需要 Blade 参与页面渲染。修改 Vue/样式后运行 `npm run dev` 重新生成前端资源，再刷新浏览器。

## 数据库连接信息

| 配置 | 值 |
| --- | --- |
| 数据库 | `laravel` |
| 用户名 / 密码 | `root` / `root` |
| 应用容器地址 | `mysql:3306` |
| 本机客户端地址 | `127.0.0.1:3306` |
| 字符集 | `utf8mb4` |

查看数据库表：

```sh
docker compose exec mysql mysql -uroot -proot laravel -e 'SHOW TABLES;'
```

## 日常操作

```sh
# 启动和停止（停止容器不删除数据库）
docker compose up -d
docker compose down

# 拉取应用日志
docker compose logs -f app
docker compose logs -f mysql

# 更新数据库结构和示例数据
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
```

MySQL 数据保存在 Docker 命名卷 `mysql57_data`。不要执行 `docker compose down -v`，除非确认要永久删除本地数据库数据。

## 常见问题

- **Docker 命令无法连接：** 确认 Docker Desktop 已启动，再运行 `docker compose ps`。
- **端口已被占用：** 本项目默认使用本机 `8000` 和 `3306` 端口；先确认其他服务未占用。
- **提示缺少 `vendor/autoload.php`：** 在项目根目录重新执行 Composer 安装命令：
  `docker run --rm -v "$PWD":/app -w /app composer:2.2 install`
- **页面提示应用密钥缺失：** 执行 `docker compose run --rm app php artisan key:generate`，再启动应用。
- **数据库尚未就绪：** 检查 `docker compose ps` 与 `docker compose logs mysql`，待 MySQL 健康后再运行迁移。
