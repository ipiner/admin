# Pin Admin

基于 [Pin](https://github.com/ipiner/pin) 开发的极简后台管理系统 API 示例，展示 Pin 在实际项目中的应用。

## 主要功能

- 管理员、角色、菜单管理
- 基于菜单编码的接口访问控制
- 登录日志、操作日志、行为日志、上传日志
- API 文档生成

<div>
  <a href="https://admin.ipiner.cn/api" target="_blank">
    <img alt="Api Docs" width="100%" src="https://admin.ipiner.cn/api/scalar.png">
  </a>
</div>

## 快速开始

```bash
composer create-project ipiner/admin
```

配置数据库：

```ini
DEFAULT_DB_HOST=127.0.0.1
DEFAULT_DB_PORT=3306
DEFAULT_DB_DATABASE=admin
DEFAULT_DB_USERNAME=root
DEFAULT_DB_PASSWORD=
```

初始化数据：

```bash
php artisan migrate --seed
```

API 文档：

```text
http://your-domain/docs/api
```

## 演示

- URL：https://admin.ipiner.cn
- 帐号：test-admin
- 密码：test@123
- 验证码：倒序输入
