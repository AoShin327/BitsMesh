[根目录](../../CLAUDE.md) > **library**

---

# Library 核心库

## 变更记录

| 日期 | 变更内容 |
|------|---------|
| 2026-01-17 20:48:21 | 初始化模块文档 |

---

## 模块职责

**Library** 是 Vanilla Forums 的框架核心，提供 MVC、数据库、缓存、会话、路由等基础设施。

**核心组件**：
- **MVC 框架**：Controller, Model, View
- **数据库抽象层**：MySQL Driver, SQL Builder
- **缓存系统**：Memcached, File Cache, Dirty Cache
- **会话管理**：Session, Cookie Identity
- **路由系统**：Dispatcher, Router
- **认证系统**：Authenticator, OAuth, Password
- **模板引擎**：Smarty 集成
- **格式化工具**：BBCode, Markdown, HTML
- **依赖注入**：Garden Container

---

## 关键目录结构

```
library/
├── core/                      # 核心类（旧命名空间）
│   ├── class.controller.php  # 控制器基类
│   ├── class.model.php       # 模型基类
│   ├── class.dispatcher.php  # 请求调度器
│   ├── class.router.php      # 路由器
│   ├── class.gdn.php         # 全局静态容器
│   ├── class.session.php     # 会话管理
│   ├── class.cache.php       # 缓存接口
│   ├── class.form.php        # 表单构建
│   ├── class.validation.php  # 数据验证
│   ├── class.format.php      # 内容格式化
│   ├── class.pluginmanager.php # 插件管理
│   ├── class.thememanager.php  # 主题管理
│   └── functions.*.php       # 全局函数库
├── database/                  # 数据库层
│   ├── class.database.php    # 数据库主类
│   ├── class.mysqldriver.php # MySQL 驱动
│   ├── class.sqldriver.php   # SQL 抽象基类
│   ├── class.dataset.php     # 数据集
│   └── class.databasestructure.php # Schema 管理
├── Garden/                    # 现代化组件（PSR 命名空间）
│   ├── Container/            # DI 容器
│   ├── Web/                  # HTTP 请求/响应
│   ├── Schema/               # 数据验证
│   └── ...
└── Vanilla/                   # Vanilla 特定组件
    └── ...
```

---

## 核心类说明

### MVC 基础类

#### Gdn_Controller
**文件**：`core/class.controller.php`

**职责**：所有控制器的基类

**关键方法**：
```php
$this->render();           // 渲染视图
$this->permission();       // 权限检查
$this->setData();          // 设置视图数据
$this->jsonTarget();       // AJAX 响应
$this->fireEvent();        // 触发插件事件
```

#### Gdn_Model
**文件**：`core/class.model.php`

**职责**：所有模型的基类

**关键方法**：
```php
$this->SQL;                // SQL 查询构建器
$this->Database;           // 数据库连接
$this->Validation;         // 验证对象
$this->save($FormPostValues); // 保存数据
```

### 数据库层

#### Gdn_Database
**文件**：`database/class.database.php`

**使用示例**：
```php
$db = Gdn::database();
$result = $db->sql()
    ->select('*')
    ->from('Discussion')
    ->where('CategoryID', 5)
    ->get();
```

#### Gdn_DatabaseStructure
**文件**：`database/class.databasestructure.php`

**Schema 定义**：
```php
Gdn::structure()
    ->table('MyTable')
    ->primaryKey('MyID')
    ->column('Name', 'varchar(100)', false)
    ->column('DateInserted', 'datetime', false)
    ->set();
```

### 缓存系统

**支持类型**：
- `Gdn_Memcached` - Memcached 缓存
- `Gdn_FileCache` - 文件缓存
- `Gdn_Dirtycache` - 内存缓存（默认）

**使用示例**：
```php
$cache = Gdn::cache();
$cache->store('key', 'value', [
    Gdn_Cache::FEATURE_EXPIRY => 3600
]);
$value = $cache->get('key');
```

### 会话管理

**文件**：`core/class.session.php`

**使用示例**：
```php
$session = Gdn::session();
$userID = $session->UserID;
$isAdmin = $session->checkPermission('Garden.Settings.Manage');
```

### 认证系统

**认证器**：
- `Gdn_Authenticator_Password` - 密码认证
- `Gdn_OAuth2` - OAuth 2.0
- `Gdn_Auth` - 认证管理器

**使用示例**：
```php
$auth = Gdn::authenticator();
$identity = $auth->authenticate();
```

### 路由与调度

**文件**：
- `core/class.dispatcher.php` - 请求调度
- `core/class.router.php` - URL 路由

**路由配置**：
```php
$Configuration['Routes']['profile'] = 'profile/%s';
$Configuration['Routes']['DefaultController'] = 'discussions';
```

### 插件系统

**文件**：`core/class.pluginmanager.php`

**插件钩子**：
```php
// 在插件中
class MyPlugin extends Gdn_Plugin {
    public function discussionController_render_before($sender) {
        // 在讨论页面渲染前执行
    }
}
```

### 格式化工具

**文件**：`core/class.format.php`

**支持格式**：
- `Html` - 原始 HTML
- `Markdown` - Markdown 语法
- `BBCode` - BBCode 语法
- `Text` - 纯文本
- `Rich` - Rich Editor 格式

**使用示例**：
```php
$formatted = Gdn_Format::to($content, 'Markdown');
```

---

## Garden 现代化组件

### Garden\Container
**位置**：`Garden/Container/`

**职责**：PSR-11 兼容的依赖注入容器

**使用示例**：
```php
$container = Gdn::getContainer();
$container->setInstance('MyService', $instance);
$service = $container->get('MyService');
```

### Garden\Web
**位置**：`Garden/Web/`

**职责**：PSR-7 风格的 HTTP 请求/响应处理

**核心类**：
- `RequestInterface` - HTTP 请求接口
- `Data` - API 响应数据
- `Dispatcher` - 新一代路由调度器
- `Cookie` - Cookie 管理

---

## 常见问题 (FAQ)

### Q1: 如何扩展核心类？
**A**: 不建议直接修改 Library，使用插件钩子或继承后覆盖。

### Q2: 如何添加全局函数？
**A**: 在 `core/functions.custom.php` 中添加（如果不存在则创建）。

### Q3: 如何优化数据库查询？
**A**: 使用 `->cache()` 方法缓存查询结果，启用 Query Cache。

### Q4: 旧命名空间（Gdn_*）与新命名空间（Garden\*）的关系？
**A**: 新代码推荐使用 `Garden\*` 命名空间，旧代码向后兼容。

---

## 测试覆盖

**单元测试**：部分核心类有测试，位于 `vendor/vanilla/*/tests/`

**建议补充测试**：
- 数据库层测试
- 缓存一致性测试
- 路由解析测试
- 权限验证测试

---

**最后更新**：2026-01-17 20:48:21
