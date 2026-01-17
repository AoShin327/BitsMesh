# Google Sign-In 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **googlesignin**

---

## 模块职责

**Google Sign-In** 插件是 Google+ 插件的现代替代方案，实现了基于 OAuth 2.0 的 Google 账号登录功能，使用 OpenID Connect 协议。

---

## 入口与启动

### 主文件
- **插件类**：`class.googlesignin.plugin.php` - `GoogleSignInPlugin`
- **基类**：继承自 `Gdn_OAuth2`
- **配置文件**：`addon.json`

### 初始化流程
1. 插件继承 `Gdn_OAuth2` 基类
2. 设置 `providerKey` 为 'googlesignin'
3. 配置 OAuth 2.0 端点和参数
4. 自动处理 OAuth 流程

---

## 对外接口

### OAuth 2.0 端点（自动配置）
- `GET /entry/googlesignin` - 开始 OAuth 流程
- `GET /settings/googlesignin` - 插件设置页面

### Google OpenID Connect API
```php
const AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
const TOKEN_URL = 'https://oauth2.googleapis.com/token';
const PROFILE_URL = 'https://openidconnect.googleapis.com/v1/userinfo';
const ACCEPTED_SCOPE = 'email openid profile';
```

---

## 关键依赖与配置

### 外部依赖
- **基类**：`Gdn_OAuth2`（核心 OAuth 2.0 实现）
- **模型**：`Gdn_AuthenticationProviderModel`

### 配置项（存储在 GDN_UserAuthenticationProvider 表）
- `AuthenticationKey` = 'googlesignin'
- `AssociationKey` = Google Client ID
- `AssociationSecret` = Google Client Secret
- `IsDefault` = 是否为默认登录方式

### Profile 字段映射
```php
'ProfileKeyName' => 'name'              // 用户显示名
'ProfileKeyUniqueID' => 'sub'           // Google 用户唯一 ID（Subject）
'ProfileKeyFullName' => null            // 不使用单独的全名字段
```

---

## 核心功能

### 1. OAuth 2.0 自动化
- 继承 `Gdn_OAuth2` 基类，自动处理：
  - 授权请求构建
  - Token 交换
  - 用户信息获取
  - 错误处理

### 2. 设置页面
- 动态生成配置表单
- 验证必填字段（Client ID, Secret）
- 显示重定向 URL 提示
- 支持设置为默认登录方式

### 3. 重定向 URL 提示
```php
$redirectUrls = Gdn::request()->url('/entry/googlesignin', true, true);
// 同时返回 HTTP 和 HTTPS 版本
```

---

## 事件钩子

### 已实现的钩子
插件依赖父类 `Gdn_OAuth2` 的标准钩子实现。

---

## 测试与质量

### 测试覆盖
- **单元测试**：无
- **集成测试**：无

### 安全措施
1. **OpenID Connect**：使用标准 OIDC 协议
2. **SSL 强制**：所有 API 调用通过 HTTPS
3. **State 参数**：继承自父类的 CSRF 保护
4. **字段验证**：设置页面强制验证 Client ID 和 Secret

---

## 与 Google+ 插件的区别

| 特性 | GooglePlus（废弃） | GoogleSignIn（推荐） |
|------|-------------------|---------------------|
| API 协议 | OAuth 2.0 + Google+ API | OAuth 2.0 + OpenID Connect |
| 用户唯一 ID | Google+ Profile ID | OpenID Connect `sub` 字段 |
| 实现方式 | 自定义实现 | 继承 `Gdn_OAuth2` |
| 维护状态 | 已废弃 | 活跃 |
| 最低版本 | 2.2 | 2.6 |

---

## 常见问题 (FAQ)

### Q1: 如何获取 Google Client ID 和 Secret？
1. 访问 [Google Cloud Console](https://console.cloud.google.com/)
2. 创建项目 > APIs & Services > Credentials
3. 创建 OAuth 2.0 客户端 ID
4. 添加授权重定向 URI：`https://yourdomain.com/entry/googlesignin`

### Q2: 为什么要从 GooglePlus 迁移到 GoogleSignIn？
Google+ 服务已关闭，GoogleSignIn 使用标准 OpenID Connect 协议，更稳定可靠。

### Q3: 设置页面在哪里？
访问 `/settings/googlesignin` 或 Dashboard > Settings > Google Sign-In。

### Q4: 如何设置为默认登录方式？
在设置页面勾选"Make this connection your default signin method"。

### Q5: 支持哪些权限范围（Scope）？
固定为 `email openid profile`，包含基本用户信息和邮箱地址。

---

## 相关文件清单

```
plugins/googlesignin/
├── addon.json                          # 插件元数据
├── class.googlesignin.plugin.php       # 主插件类（136 行，简洁）
├── views/
│   └── settings.twig                   # 后台设置页面（Twig 模板）
├── icon.png                            # 插件图标
└── google_signin.png                   # 功能图标
```

---

## 变更记录 (Changelog)

| 日期 | 版本 | 变更内容 |
|------|------|---------|
| 2026-01-17 20:57:17 | 1.0.0 | 补充扫描生成文档 |

---

**最后更新**：2026-01-17 20:57:17
**维护状态**：活跃 ✅
**文档版本**：1.0.0
