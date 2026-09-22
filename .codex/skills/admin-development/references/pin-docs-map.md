# Admin 使用的 Pin 文档索引

`docs` 是独立文档仓库：`https://github.com/ipiner/docs`。文档用于理解约定，当前 Admin 依赖源码和相邻测试用于确认版本行为。

| Admin 任务 | 优先阅读 |
| --- | --- |
| 模块目录、领域命名和自动推导 | `guide/module.md` |
| Model 和公共模型能力 | `model/index.md`、`model/cache.md` |
| CRUD、事务、版本号和生命周期 | `model/service.md` |
| Action、规则、上下文、Fake | `features/action.md` |
| 列表筛选、排序和分页 | `model/queryable.md`、`features/validation.md` |
| Route 枚举、处理器和属性 | `features/routing.md` |
| Controller、统一响应和 Result | `features/controller.md`、`features/response.md` |
| 错误码和异常 | `features/errors.md`、`features/exception-handler.md` |
| HTTP 测试链路和断言 | `testing/http-tests.md`、`testing/helpers.md` |
| Admin 使用的 Pin 扩展包 | `packages/*.md`、`security/*.md` |

在线文档入口：`https://ipiner.cn`。当文档与当前 Composer 依赖不一致时，以 `admin/vendor`、相邻 `pin` 源码和 Admin 测试为准。
