# シミュレーション申込・管理

Laravel 6.18 + Vue 2 的保险模拟申请管理系统。开发阶段、版本要求及验收记录见[开发计划](DEVELOPMENT_PLAN.md)，系统分层见[架构说明](ARCHITECTURE.md)。

## 首次安装

从空白环境安装，请按[首次安装说明](INSTALLATION.md)逐步执行。项目使用 Docker 运行 PHP 7.4 和 MySQL 5.7；宿主机只需 Docker Desktop、nvm/Node.js。

安装完成后访问 <http://127.0.0.1:8000/login>。本地默认管理员：`admin@example.com` / `password`。请勿在生产环境使用此凭据。

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
npm test

# 后端测试（使用独立的 laravel_testing 数据库）
docker compose exec -T mysql mysql -uroot -proot -e 'CREATE DATABASE IF NOT EXISTS laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
docker compose exec -T app php vendor/bin/phpunit
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

Laravel 还使用 `users` 管理账号、`password_resets` 保存密码重置令牌、`failed_jobs` 保存失败队列任务；`migrations` 记录迁移。业务表按 `status`、`effective_date` 建立联合索引。Seeder 创建或补齐本地管理员，并提供 120 条日文示例申请（五种状态各 24 条）；只在 `local`、`testing` 环境运行。

查看表和迁移状态：

```sh
docker compose exec app php artisan migrate:status
docker compose exec mysql mysql -uroot -proot laravel -e 'SHOW TABLES; DESCRIBE simulation_applications;'
```

测试使用独立数据库 `laravel_testing`，PHPUnit 会刷新该库。不要把测试环境指向开发库 `laravel`。
