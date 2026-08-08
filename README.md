# Pin Admin

基于 [Pin](https://github.com/ipiner/pin) 开发的极简后台管理系统 API 示例，展示 Pin 在实际项目中的应用。

## 主要功能

- 管理员、角色、菜单管理
- 基于菜单编码的接口访问控制
- 登录日志、操作日志、行为日志、上传日志
- API 文档生成

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

