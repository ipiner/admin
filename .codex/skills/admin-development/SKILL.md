---
name: admin-development
description: 在基于 Pin 的 Admin 应用中开发或修改业务模块和 API；先复用 Pin 的 Model、ModelService、Action、Route、Queryable 和响应约定，再使用 Laravel 原生能力。
metadata:
  short-description: 按 Pin 约定开发 Admin 业务模块
---

# Admin 应用开发

这个 Skill 只服务于 [`ipiner/admin`](https://github.com/ipiner/admin) 应用。Admin 是 Pin 的使用方和示例应用；业务代码应遵循本仓库已有模块结构，同时优先使用 Pin 提供的基础能力。

## 信息来源和优先级

1. 当前 `admin/app`、`admin/tests` 和相邻模块的实现。
2. `https://github.com/ipiner/docs` 中对应的 Pin 设计文档。
3. Admin 的 `vendor/ipiner/pin` 实际依赖或相邻的 `pin` 源码，用于确认当前版本的真实签名。
4. Laravel 原生文档和扩展点。

当文档示例、已安装依赖和相邻代码不一致时，先以当前依赖的真实类签名和测试为准；不要为了追随文档把 Admin 改成另一版本的 Pin。

## 默认业务形状

新增模型驱动的 API 时，先按模块拆分并检查是否已有对应领域：

- 模型：优先使用应用的 `App\\Models\\Model`，或按模块推导规则放在对应 `Models` 目录。
- Service：标准增删改查继承 `Pin\\Services\\ModelService`，保留泛型并使用 `create`、`update`、`delete`、`pagination` 和生命周期钩子。
- Action：继承 `Pin\\Action\\Action`，在 `rules()` 中定义输入和查询规则，在 `handle()` 中编排一次业务动作；创建、更新等共享规则放在领域 Action 基类。
- Controller：继承 `App\\Http\\Controllers\\Controller`，通过依赖注入接收 Action 或 Service，只做 HTTP 编排并返回 `success()`/`error()`。
- Route：使用 `Routable` 路由枚举和 Pin 的自动推导；URI、处理器、权限和属性沿用现有写法。
- Resource：需要转换响应字段时使用 JsonResource，保持 API 输出稳定。
- Factory/Test：在 `database/factories` 和 `tests/Modules` 或现有对应目录中补齐测试数据和接口测试。

## Pin 优先决策

- 标准 CRUD 不在 Controller 中直接调用 Eloquent；先用 `ModelService`。
- 单一业务流程、输入验证、权限判断和关系同步优先放在 Action 或 Service 生命周期中。
- 列表接口用 `QueryableRules` 声明可查询字段，验证后交给 Action 的 `queryable()`、模型查询或 Service 的 `pagination()`。
- API 返回使用 Pin 的 `ApiResponse`、`success()`、`error()` 和 Result；不要为单个接口手写另一种响应结构。
- 错误使用项目错误枚举、`Pin\\Errors\\IError` 和 Pin 异常机制；不要散落业务错误数字和字符串。
- 只有 Pin 没有对应能力、需要 Laravel 基础设施或第三方集成时，才直接使用 Laravel。即使下沉，也保留 Admin 的路由、错误、响应和测试约定。

## 模块与安全边界

- 先根据 `App\\Modules\\{Module}`、`App\\Routes\\{Module}` 和领域名称确认自动推导结果，再决定是否显式指定类。
- 参照 `Content/Article`、`Content/ArticleCategory`、`System/Admin` 和 `System/Role` 的现有组织方式，不新造 Repository、FormRequest 或平行 Service 层，除非现有 Pin 约定无法表达需求。
- 权限、管理员/角色关系、操作日志和文件上传等跨模型流程必须保留事务、授权和审计行为；不要只为通过接口测试而绕过现有 Guard、Service 或事件。
- 如果发现需求实际缺少 Pin 能力，应先记录缺口；涉及公共框架能力时修改 `pin` 仓库，而不是在 Admin 中复制一份框架实现。

## 验证方式

- 新增或修改端点时同步检查 Route、Controller、Action、Service、Model、Resource、错误码、Factory 和测试。
- 优先使用路由枚举的 `testing()`、Action 的 `fake()`、`created()`、`updated()`、`deleted()` 和 `paginated()` 验证完整请求链路。
- 运行 Admin 受影响的 Pest/PHPUnit、Pint 和 PHPStan 检查，命令以当前仓库配置为准。
- 完成说明中报告：复用了哪些 Pin 能力、哪些地方使用了 Laravel，以及是否发现需要回补 `pin` 或 `docs` 的公共能力。

## 相关文档

按需阅读 [references/pin-docs-map.md](references/pin-docs-map.md)，再查看当前模块的源码和测试，不要一次性加载整个文档站点。
