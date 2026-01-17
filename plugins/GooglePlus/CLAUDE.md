# Google+ Social Connect 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **GooglePlus**

---

## 模块职责

**Google+ Social Connect** 插件实现了 Google+ OAuth 2.0 社交登录功能。**注意**：Google 已于 2019 年关闭 Google+ 服务，此插件已**废弃**，建议使用 `googlesignin` 插件替代。

---

## 入口与启动

### 主文件
- **插件类**：`class.googleplus.plugin.php` - `GooglePlusPlugin`
- **认证器**：`GooglePlusAuthenticator.php`
- **配置文件**：`addon.json`

### 初始化流程
1. 用户点击 Google 登录按钮
2. 重定向到 `/entry/googleplusauthredirect`
3. 跳转到 Google OAuth 2.0 授权页
4. 回调到 `/entry/googleplus`
5. 交换 authorization code 获取 access token
6. 调用 Google API 获取用户信息

---

## 对外接口

### OAuth 2.0 端点
- `GET /entry/googleplusauthredirect` - 授权重定向
- `GET /entry/googleplus` - OAuth 回调端点
- `GET /entry/connect/googleplus` - SSO 数据传输端点
- `POST /post/googleplus/{recordType}?id={id}` - 分享到 Google+

### Google API
- **API Version**: OAuth 2.0 v1
- **Base URL**: `https://www.googleapis.com/oauth2/v1`
- **授权 URL**: `https://accounts.google.com/o/oauth2/auth`
- **Token URL**: `https://accounts.google.com/o/oauth2/token`
- **Profile URL**: `/userinfo`
- **Scope**: `userinfo.profile userinfo.email`

---

## 关键依赖与配置

### 外部依赖
- **cURL**：用于 API 调用
- **SsoUtils**：依赖注入，用于 state token 验证

### 配置项
```php
$Configuration['Plugins.GooglePlus.ClientID'] = '...';       // Google Client ID
$Configuration['Plugins.GooglePlus.Secret'] = '...';         // Google Client Secret
$Configuration['Plugins.GooglePlus.Default'] = false;        // 是否为默认登录方式
$Configuration['Plugins.GooglePlus.SocialReactions'] = true; // 启用社交反应
$Configuration['Plugins.GooglePlus.SocialSharing'] = true;   // 启用社交分享
$Configuration['Plugins.GooglePlus.UseAvatars'] = true;      // 使用 Google 头像
$Configuration['Plugins.GooglePlus.UseFullNames'] = false;   // 使用 Google 全名
```

### 设置页面
- **路径**：`/dashboard/social/googleplus`
- **权限**：`Garden.Settings.Manage`
- **视图**：`views/settings.php`

---

## 数据模型

### 用户认证表（GDN_UserAuthentication）
- `Provider` = 'GooglePlus'
- `UniqueID` = Google 用户 ID

### 用户属性（User->Attributes）
```php
[
    'GooglePlus' => [
        'AccessToken' => 'token',
        'Profile' => [
            'id' => '...',
            'name' => 'Full Name',
            'email' => 'user@example.com',
            'picture' => 'https://...'
        ]
    ]
]
```

---

## 核心功能

### 1. 社交登录（Social Sign-In）
- 在登录页添加 Google 按钮
- 在 MeModule 和 GuestModule 中显示图标
- 支持设置为默认登录方式（覆盖标准登录）

### 2. 社交分享（Social Sharing）
- 分享讨论到 Google+（已废弃）
- 使用 Google+ 分享对话框

### 3. 社交反应（Social Reactions）
- 在讨论/评论下方显示"分享到 Google+"按钮（已无效）

### 4. 资料连接（Profile Connection）
- 用户可在个人资料页连接 Google 账号
- 支持 state token 验证

---

## 事件钩子

### 已实现的钩子
- `entryController_signIn_handler` - 登录页按钮
- `base_signInIcons_handler` - MeModule 图标
- `base_beforeSignInButton_handler` - GuestModule 按钮
- `base_afterReactions_handler` - 反应行分享按钮
- `base_getConnections_handler` - 连接列表
- `base_connectData_handler` - SSO 数据传输
- `entryController_overrideSignIn_handler` - 覆盖默认登录
- `authenticationProviderModel_calculateGooglePlus_handler` - 计算登录 URL

---

## 测试与质量

### 测试覆盖
- **单元测试**：无
- **集成测试**：无

### 安全措施
1. **State Token 验证**：防止 CSRF 攻击
2. **SSL 验证**：`CURLOPT_SSL_VERIFYPEER = false`（不安全，需修复）
3. **禁用自动连接**：`Garden.Registration.AutoConnect = false`

### 已知限制
1. **Google+ 已关闭**：所有 Google+ 相关功能已失效
2. **API 版本过时**：OAuth 2.0 v1 可能不再支持
3. **安全性问题**：SSL 证书验证被禁用

---

## 迁移指南

### 推荐替代方案
使用 **Google Sign-In 插件**（`plugins/googlesignin`）替代本插件。

### 迁移步骤
1. 禁用 GooglePlus 插件
2. 启用 googlesignin 插件
3. 在 Google Cloud Console 更新 OAuth 凭据
4. 更新回调 URL 为 `/entry/googlesignin`
5. 测试登录流程

---

## 常见问题 (FAQ)

### Q1: 为什么插件标记为废弃？
Google 于 2019 年 4 月关闭了 Google+ 服务，相关 API 已不再可用。

### Q2: 现有用户的 Google+ 连接会怎样？
现有连接保留在数据库中，但无法用于登录。需要用户重新使用 Google Sign-In 连接。

### Q3: 如何迁移到 Google Sign-In？
参考上方"迁移指南"部分。

### Q4: 插件是否还能使用？
不建议使用。虽然代码仍存在，但 Google+ API 已关闭，无法正常工作。

---

## 相关文件清单

```
plugins/GooglePlus/
├── addon.json                          # 插件元数据（标记为 deprecated）
├── class.googleplus.plugin.php         # 主插件类（584 行）
├── GooglePlusAuthenticator.php         # 认证器类
├── views/
│   └── settings.php                    # 后台设置页面
├── icon.png                            # 插件图标
└── google_social_connect.png           # 功能图标
```

---

## 变更记录 (Changelog)

| 日期 | 版本 | 变更内容 |
|------|------|---------|
| 2026-01-17 20:57:17 | 1.1.0 | 补充扫描生成文档，标记为废弃 |
| 2019-04 | 1.1.0 | Google+ 服务关闭 |

---

**最后更新**：2026-01-17 20:57:17
**维护状态**：已废弃 ⚠️
**文档版本**：1.0.0
