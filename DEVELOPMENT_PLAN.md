# 开发与维护计划

更新日期：2026-10-10。本文以当前前后端分离架构为准，历史阶段仅用于追踪，不作为当前启动方式。

## 当前目标与约束

- 独立 Vue SPA 与 Laravel API；frontend/backend 分别管理依赖、入口、构建和测试。
- 固定 PHP 7.4.33、Laravel 6.18.0、Vue 2.6.14、Node 14.21.3/npm 6.14.18、MySQL 5.7.44；本次不升级。
- 页面／消息使用日文，Session + CSRF 认证，后端数据库权限与 Gate 校验。
- MySQL 开发库与测试库分离；不清空开发数据、不删除旧 volume、不自动提交／推送。
- Storybook 已启用，基础组件放在 frontend/src/components，配置仅在 frontend/.storybook。

## 已完成范围

认证含登录／退出／记住登录、注册默认 viewer、密码确认／修改／找回／重置；邮箱验证未开放。

申込支持显式搜索、状态过滤、10/20/30/50 分页、新增确认、详情、编辑和删除。权限管理支持角色分配与账号标记删除／恢复；保留保护账号与最后最高管理员约束。

管理页为左菜单、右主体，路由区分菜单选中状态。表单使用 novalidate 并显示日文校验消息。权限下拉使用 BaseSelect。详情编辑／删除按钮为蓝／红，hover 加深并增加阴影；未将该设计扩展到其他页面。

共用 BaseToast 显示用户管理成功消息：顶部居中、浅黄色半透明背景、深色字，默认 3 秒后以 0.3 秒淡出；计时器重置和销毁清理有测试。

## 实施历史

| 日期 | 阶段 | 记录 |
| --- | --- | --- |
| 2026-10-08 | 初版实现 | Laravel/Vue 内嵌管理系统、Docker 环境、CRUD、日文示例数据与安装文档完成；后续已迁移为独立前后端 |
| 2026-10-09 | 文件迁移 | 提交 6fa45f7，先只迁移文件，166 个文件内容不变 |
| 2026-10-09 | 架构修改 | 提交 2165eeb；独立服务与构建、统一 /api/v1、Session/CSRF/CORS、Router 和 Axios、重置邮件链接及测试路径 |
| 2026-10-09～10 | 界面维护 | 原生校验关闭、侧栏、BaseSelect、详情按钮及 BaseToast；保持未提交，供用户审查 |
| 2026-10-10 | 文档与目录整理 | 文档统一为当前架构；删除根目录迁移遗留空目录及未使用的覆盖率／Storybook 生成目录 |

分离阶段历史验收：Jest 27 tests，PHPUnit 50 tests/232 assertions，38 项 HTTP 检查，前端 development/production 与 Storybook、独立镜像和后端干净依赖安装通过。该结果属于当时验收；当前维护验证另行列出。

## 本次维护与验收

文档同步 README、ARCHITECTURE、INSTALLATION、PROJECT_FILES 和本文。目录清理前核对挂载、构建入口与引用；确认为空的根目录 `.storybook`、app、bootstrap、config、database、public、resources、routes、scripts、storage、tests 已不再使用。清理 root coverage、root storybook-static 和 frontend/storybook-static 的过期可生成内容。

保留 frontend/src、frontend/public、frontend/scripts、frontend/.storybook、frontend/dist、frontend/node_modules，以及 backend 全部源码／配置／依赖／运行目录。目录含义以 PROJECT_FILES.md 为准。数据库、环境文件、APP_KEY 和 Docker volume 均不在清理范围。

本次实际验证（2026-10-10）：

- Jest：9 个套件、30 项测试通过；无缺少 label 的警告。
- PHPUnit：50 项测试、232 条断言通过，使用 laravel_testing。
- 独立前端 production 构建通过。
- Storybook 构建通过，输出位于一次性容器 /tmp/insurance-storybook-check，容器退出后移除；存在旧依赖弃用和资源体积警告。
- Markdown 相对链接、14 个删除目录、保留运行路径、git diff --check 和 Compose config 检查通过。
- 实际服务只读检查：frontend /login 返回 200；backend /api/v1/auth/csrf 返回 204。
- 未修改应用源码或用户现有改动；未操作开发业务数据／数据库 volume；未提交或推送。


## 验收标准与回退

- frontend Jest、production、Storybook 构建通过；backend PHPUnit 在 laravel_testing 运行通过。
- 文档相对链接指向实际文件，目录清单与当前工作树一致。
- Compose 正确引用 frontend/backend，删除目录无 tracked 文件，实际运行所需目录保留。
- 不撤销用户已有修改、不增加提交、不推送、不改变数据库业务数据。

本次修改前已备份文档与待清理目录到本次任务工作区的 `work/docs-cleanup-backup-20261010.tar.gz`。需要回退时，仅恢复相应文档／目录，不覆盖其他源码或环境配置。备份含旧构建报告，不含环境密钥或业务数据库。

## 后续范围与限制

真实 SMTP、正式生产服务／HTTPS、旧业务数据导入仍需单独验收。邮箱验证、审批流程／历史、文件上传、报表／导出、批量操作、API Token/OAuth 不在当前功能范围。

旧版本依赖约束继续保留。Toast 计时和淡出规则通过代码与测试验证；实际保存后的动画视觉验收尚未记录，不能将其描述为已完成浏览器动画验收。
