<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

## シミュレーション申込・管理

Laravel 6.18 + Vue 2 的保险模拟申请管理系统。开发阶段、版本要求及验收记录见 [DEVELOPMENT_PLAN.md](DEVELOPMENT_PLAN.md)。

### 本地启动

```sh
docker compose up -d --build
```

打开 http://127.0.0.1:8000/login。MySQL 运行在 Docker 中，数据库名为 `laravel`，本地开发账号为 `root` / `root`。

首次初始化数据库和本地管理员：

```sh
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
```

默认管理员为 `admin@example.com` / `password`。可通过 `.env` 中的 `ADMIN_EMAIL` 和 `ADMIN_PASSWORD` 覆盖。Seeder 只在 `local`、`testing` 环境运行，示例凭据不得用于生产环境。

### 数据库构造

#### 数据库连接

Docker Compose 首次启动时会创建 MySQL 5.7.44 数据库：

| 配置项 | 开发数据库 |
| --- | --- |
| 数据库名 | `laravel` |
| 用户名 / 密码 | `root` / `root` |
| 字符集 | `utf8mb4` |
| 应用容器连接地址 | `mysql:3306` |
| 本机连接地址 | `127.0.0.1:3306` |

MySQL 数据保存在 Docker 命名卷 `mysql57_data` 中。停止或重建容器不会删除该卷；不要使用 `docker compose down -v`，除非确认要永久删除本地数据库。

#### 数据表

Laravel Migration 管理表结构。首次构建后执行迁移和 Seeder：

```sh
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
```

主要业务表 `simulation_applications` 保存保险模拟申请，字段如下：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `id` | BIGINT | 主键 |
| `application_number` | VARCHAR(50) | 申込番号，唯一 |
| `applicant_name` | VARCHAR(100) | 申込者氏名（日文示例姓名） |
| `insured_name` | VARCHAR(100) | 被保険者氏名（日文示例姓名） |
| `insured_birth_date` | DATE | 被保険者生年月日 |
| `beneficiary_name` | VARCHAR(100)，可空 | 受取人氏名 |
| `coverage_amount` | DECIMAL(15,2) | 保険金額 |
| `premium_amount` | DECIMAL(15,2) | 保険料 |
| `currency` | CHAR(3) | 通貨，默认 `JPY` |
| `status` | VARCHAR(20) | 状态：`draft`、`submitted`、`approved`、`rejected`、`cancelled` |
| `effective_date` | DATE | 适用开始日 |
| `expiry_date` | DATE，可空 | 适用结束日 |
| `notes` | TEXT，可空 | 备注 |
| `created_at` / `updated_at` | TIMESTAMP | 创建、更新时间 |

此外，Laravel 使用 `users` 保存管理员账号，`password_resets` 保存密码重置令牌，`failed_jobs` 保存失败队列任务；`migrations` 由 Laravel 自动维护迁移记录。业务申请按 `status`、`effective_date` 建立联合索引。

默认 Seeder 会创建或补齐 1 个本地管理员，以及 120 条示例申请（五种状态各 24 条）。示例申请姓名和备注为日文，Faker 区域为 `ja_JP`。重复运行会保留既有申请的其他业务字段，并将标准示例申请的姓名和备注更新为日文。Seeder 仅在 `local`、`testing` 环境执行。

查看迁移状态和表结构：

```sh
docker compose exec app php artisan migrate:status
docker compose exec mysql mysql -uroot -proot laravel -e 'SHOW TABLES; DESCRIBE simulation_applications;'
```

测试使用独立数据库 `laravel_testing`，不会清理开发库。创建测试库及运行迁移/测试：

```sh
docker compose exec -T mysql mysql -uroot -proot -e 'CREATE DATABASE IF NOT EXISTS laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
docker compose exec -e APP_ENV=testing -e DB_DATABASE=laravel_testing app php artisan migrate --force
docker compose exec -T app php vendor/bin/phpunit
```

PHPUnit 配置会自动连接 `laravel_testing`，并在测试中刷新该库。请勿将测试环境变量改指向 `laravel` 开发数据库。

### 前端构建

```sh
npm ci
npm run dev
npm run production
```

### 后端测试

测试使用独立的 MySQL 数据库，不会清理 `laravel` 开发库：

```sh
docker compose exec -T mysql mysql -uroot -proot -e 'CREATE DATABASE IF NOT EXISTS laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
docker compose exec -T app php vendor/bin/phpunit
```

Seeder 在 `local`、`testing` 环境为每种状态创建 24 条申请示例数据。

<!-- Laravel framework template content below is retained until project documentation consolidation. -->

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[British Software Development](https://www.britishsoftware.co)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- [UserInsights](https://userinsights.com)
- [Fragrantica](https://www.fragrantica.com)
- [SOFTonSOFA](https://softonsofa.com/)
- [User10](https://user10.com)
- [Soumettre.fr](https://soumettre.fr/)
- [CodeBrisk](https://codebrisk.com)
- [1Forge](https://1forge.com)
- [TECPRESSO](https://tecpresso.co.jp/)
- [Runtime Converter](http://runtimeconverter.com/)
- [WebL'Agence](https://weblagence.com/)
- [Invoice Ninja](https://www.invoiceninja.com)
- [iMi digital](https://www.imi-digital.de/)
- [Earthlink](https://www.earthlink.ro/)
- [Steadfast Collective](https://steadfastcollective.com/)
- [We Are The Robots Inc.](https://watr.mx/)
- [Understand.io](https://www.understand.io/)
- [Abdel Elrafa](https://abdelelrafa.com)
- [Hyper Host](https://hyper.host)
- [Appoly](https://www.appoly.co.uk)
- [OP.GG](https://op.gg)

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
