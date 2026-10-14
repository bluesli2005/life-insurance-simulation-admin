# 提交检查、CI 与 Coverage

## 检查内容

同一入口 `scripts/check.sh` 用于本地与 GitHub Actions：

- 前端：Node 14.21.3，vue-tsc 1.0.24 / TypeScript 4.9.5 对 JavaScript 与 Vue 2.6 SFC 执行 checkJs/noEmit；测试和 Storybook 示例由独立测试流程负责。
- 后端：PHP 7.4.33，PHPStan 1.4.10 / Larastan 1.0.3 level 5 检查 backend/app；app/config/database/routes/scripts/tests 的 PHP 文件另外进行语法检查。
- 前端 Jest 全套测试与 coverage，后端 PHPUnit 全套测试与 PCOV coverage。
- 提交暂存区保护与 coverage 汇总脚本的回归测试。

检查失败返回非零状态。类型检查失败时仍继续执行测试，以便显示测试结果和已生成的 coverage。没有设定未经约定的覆盖率门槛。

## 本地提交前检查

先按 INSTALLATION.md 准备依赖、运行中的 backend/mysql 和独立 laravel_testing 数据库。新增开发依赖后应重新按锁文件安装：

```bash
docker run --rm -v "$PWD/backend:/app" -w /app composer:2.2 composer install --no-interaction --prefer-dist
docker run --rm -v "$PWD/frontend:/frontend" -w /frontend -e CYPRESS_INSTALL_BINARY=0 node:14.21.3-buster npm ci
```

每个新 clone 都需要启用仓库 hook：

```bash
git config --local core.hooksPath .githooks
```

提交会运行 `bash scripts/check.sh --docker`。该 hook 检查工作区与暂存区的前后台代码和检查配置是否一致；存在未暂存修改或新代码文件时先拒绝提交，以避免测试通过的代码与实际提交内容不同。它不会替你暂存、stash 或删除修改。对同一源码文件的部分暂存，需要先完成暂存或手动分离改动，再提交。

手动运行同一套检查：

```bash
bash scripts/check.sh --docker
```

已有匹配版本的 PHP/Node/MySQL 环境时可执行 `bash scripts/check.sh`。DB_HOST 可按环境覆盖，DB_DATABASE 在 phpunit.xml 中始终固定为 laravel_testing，不使用开发数据库。

## GitHub Actions

`.github/workflows/ci.yml` 在 `main` 分支 push、pull_request 和手动触发时运行。PR 分支的 push 由 pull_request 检查，避免同一更新重复运行。CI 使用 Node 14、PHP 7.4、PCOV 和独立 MySQL 5.7.44 服务；按 composer.lock/package-lock.json 安装依赖。工作流仅申请 contents: read 与 pull-requests: write；后者用于更新 PR 的覆盖率评论。

测试结束后，运行页面的 Summary 显示覆盖率表；同仓库 PR 的 Conversation 页面由 CI 创建或更新一条覆盖率评论，附完整报告所在的运行链接。来自 fork 或 Dependabot 的 PR 通常只有只读 token，因此仍可在 Actions Summary 和 Artifacts 查看报告。Artifacts 中的 `coverage-<commit SHA>` 包含前后台 HTML 与机器可读报告，保留 14 天。工作流失败时也尝试上传已生成的报告。分支保护是否要求该检查通过，由仓库维护者另外配置。

## Coverage 输出与口径

| 输出 | 用途 |
| --- | --- |
| `frontend/coverage/index.html` | 前端逐文件、逐行 HTML 报告。 |
| `frontend/coverage/coverage-summary.json`、`lcov.info` | 前端汇总与机器可读覆盖率。 |
| `backend/coverage/html/index.html` | 后端逐类、逐行 HTML 报告。 |
| `backend/coverage/clover.xml` | 后端机器可读覆盖率。 |
| `coverage/summary.md` | 前后台覆盖率汇总；同时打印到终端和 GitHub Step Summary。 |

前端统计 src 中的 JavaScript 与 Vue 文件，排除测试和 stories；后端统计 backend/app。前端使用兼容 Vue 2/Jest 26 的 vue-jest 4 和 test 环境 Istanbul 插件直接插桩 Vue 脚本，通过小型 transformer 防止 Jest 对编译后 SFC 再次插桩。测试 Babel 使用 Node 14 目标，生产 Babel 保留原来的浏览器目标。

Vue coverage 统计脚本代码，不统计编译生成的模板 render 函数；没有可执行脚本的组件可能显示 0/0。PCOV 提供 PHP 行覆盖率，不提供分支覆盖率；报告明确显示该区别。覆盖率表示代码执行情况，不代表所有业务组合、并发或浏览器交互已经验证。Playwright/Cypress 保留原有手动入口，本检查流程执行 Jest/PHPUnit。

报告目录由 Git 忽略。缺少某一端报告时汇总会显示 Not generated 并返回失败，不展示旧的汇总输入。

参考：[GitHub workflow artifacts](https://docs.github.com/en/actions/concepts/workflows-and-actions/workflow-artifacts)、[Vue Language Tools](https://github.com/vuejs/language-tools)、[Larastan](https://github.com/larastan/larastan)。
