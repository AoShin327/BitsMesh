[根目录](../../CLAUDE.md) > [applications](../) > **dashboard**

---

# Dashboard 应用模块

## 变更记录

| 日期 | 变更内容 |
|------|---------|
| 2026-01-17 20:48:21 | 初始化模块文档 |

---

## 模块职责

**Dashboard** 是 Vanilla Forums 的管理后台应用，负责整个论坛系统的配置、管理和监控。

**核心功能**：
- 用户管理（用户、角色、权限）
- 内容管理（分类、讨论、评论审核）
- 系统配置（站点设置、路由、缓存）
- 插件与主题管理
- 分析与统计
- 导入/导出工具
- API 管理与文档

---

## 入口与启动

### 应用配置
**文件**：`addon.json`

```json
{
  "key": "dashboard",
  "name": "Dashboard",
  "type": "addon",
  "priority": 5,
  "allowDisable": false,
  "hidden": true
}
```

### 主要控制器
- **主仪表盘**：`controllers/class.dashboardcontroller.php`
- **设置管理**：`controllers/class.settingscontroller.php`
- **用户管理**：`controllers/class.usercontroller.php`
- **角色管理**：`controllers/class.rolecontroller.php`
- **插件管理**：`controllers/class.plugincontroller.php`
- **路由管理**：`controllers/class.routescontroller.php`
- **安装/设置**：`controllers/class.setupcontroller.php`

---

## 对外接口

### REST API

**位置**：`controllers/api/*ApiController.php`

| 端点 | 控制器 | 说明 |
|------|--------|------|
| `/api/v2/users` | `UsersApiController` | 用户 CRUD |
| `/api/v2/roles` | `RolesApiController` | 角色管理 |
| `/api/v2/media` | `MediaApiController` | 媒体上传 |
| `/api/v2/tokens` | `TokensApiController` | API 令牌 |
| `/api/v2/authenticate` | `AuthenticateApiController` | 认证 |

### 前端 React 组件

**构建配置**：
- **入口文件**：`src/scripts/entries/admin.ts`, `src/scripts/entries/forum.ts`
- **构建输出**：`dist/admin/`, `dist/forum/`
- **包管理**：`yarn.lock` 存在（使用 Yarn）

**主要组件**：
- 用户管理界面
- 角色权限编辑器
- 分类树管理
- 主题预览
- API 文档（Swagger UI 集成）

---

## 关键依赖与配置

### PHP 依赖
- `Gdn::controller()` - 控制器基类
- `Gdn::userModel()` - 用户模型
- `Gdn::permissionModel()` - 权限模型
- `Gdn::config()` - 配置访问

### 前端依赖（推测）
- React
- TypeScript
- Redux（状态管理）
- Quill（富文本编辑器）

### 配置文件
- **默认设置**：`/conf/config-defaults.php`
- **运行时配置**：`/conf/config.php`
- **路由配置**：`$Configuration['Routes']`

---

## 数据模型

### 主要数据表（推测）
- `GDN_User` - 用户表
- `GDN_Role` - 角色表
- `GDN_Permission` - 权限表
- `GDN_UserRole` - 用户-角色关联
- `GDN_Session` - 会话表
- `GDN_Activity` - 活动日志
- `GDN_Log` - 系统日志
- `GDN_Media` - 媒体文件
- `GDN_Invitation` - 邀请码

### 模型文件
**位置**：`models/`

| 模型类 | 职责 |
|--------|------|
| `UserModel` | 用户 CRUD 与验证 |
| `RoleModel` | 角色管理 |
| `PermissionModel` | 权限分配 |
| `ActivityModel` | 活动流 |
| `MediaModel` | 文件上传 |
| `SessionModel` | 会话管理 |
| `LogModel` | 日志记录 |

---

## 测试与质量

### 测试文件
**位置**：未在当前扫描中发现专用测试目录

**建议补充**：
- 单元测试：`tests/models/`
- API 测试：`tests/api/`
- 集成测试：`tests/integration/`

### 代码质量工具
- **PHP 代码风格**：PSR-2
- **静态分析**：未配置 PHPStan/Psalm
- **前端检查**：ESLint, TSLint

---

## 常见问题 (FAQ)

### Q1: 如何添加新的管理页面？
**A**: 在 `controllers/` 下创建新控制器，继承 `DashboardController`，并配置路由。

### Q2: 如何修改用户权限？
**A**: 通过 `/settings/roles` 管理界面或使用 `PermissionModel::saveAll()`。

### Q3: API 如何认证？
**A**: 使用 API 令牌（`/api/v2/tokens`）或 Session Cookie。

### Q4: 前端如何热重载？
**A**:
```bash
cd applications/dashboard
yarn dev
```
然后配置 `$Configuration['HotReload']['IP'] = '127.0.0.1';`

---

## 相关文件清单

### 关键目录结构
```
dashboard/
├── addon.json                 # 应用配置
├── controllers/               # 控制器
│   ├── api/                  # REST API 控制器
│   ├── class.dashboardcontroller.php
│   ├── class.settingscontroller.php
│   └── ...
├── models/                    # 数据模型
│   ├── class.usermodel.php
│   ├── class.rolemodel.php
│   └── ...
├── views/                     # Smarty 视图模板
│   ├── settings/
│   ├── users/
│   └── ...
├── src/                       # TypeScript 源码
│   └── scripts/
│       ├── entries/          # 入口文件
│       └── ...
├── dist/                      # 构建产物
│   ├── admin/
│   └── forum/
├── design/                    # CSS 样式
│   ├── admin.css
│   └── ...
└── yarn.lock                  # 前端依赖锁定
```

### 配置影响
- **启用状态**：`$Configuration['EnabledApplications']['Dashboard']`（默认启用，不可禁用）
- **主题支持**：Dashboard 使用独立样式，不继承主题
- **权限控制**：`Garden.Settings.Manage`, `Garden.Users.Add`, `Garden.Roles.Manage` 等

---

**最后更新**：2026-01-17 20:48:21
