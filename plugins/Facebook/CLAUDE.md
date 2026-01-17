# Facebook Social Connect 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **Facebook**

---

## 模块职责

**Facebook Social Connect** 插件实现了 Facebook OAuth 2.0 社交登录功能，允许用户使用 Facebook 账号登录论坛，并可选择分享论坛内容到 Facebook。

---

## 入口与启动

### 主文件
- **插件类**：`class.facebook.plugin.php` - `FacebookPlugin`
- **认证器**：`FacebookAuthenticator.php`
- **配置文件**：`addon.json`

### 初始化流程
1. 用户点击 Facebook 登录按钮
2. 重定向到 `/entry/facebook`
3. 跳转到 Facebook OAuth 2.0 授权页
4. 回调到 `/entry/connect/facebook`
5. 交换 authorization code 获取 access token
6. 获取用户信息并完成登录

---

## 对外接口

### OAuth 2.0 端点
- `GET /entry/facebook` - 重定向到授权 URI
- `GET /entry/connect/facebook` - OAuth 回调端点
- `GET /profile/facebookconnect` - 用户资料连接回调

### Facebook Graph API
- **API Version**: 2.7
- **Base URL**: `https://graph.facebook.com/v2.7/`
- **授权 URL**: `https://graph.facebook.com/oauth/authorize`
- **Token URL**: `https://graph.facebook.com/oauth/access_token`
- **Profile URL**: `https://graph.facebook.com/me?fields=name,id,email`
- **头像 URL**: `//graph.facebook.com/{user_id}/picture?width=200&height=200`

### 分享接口
- **分享 URL**: `https://www.facebook.com/sharer/sharer.php?u={url}`

---

## 关键依赖与配置

### 外部依赖
- **cURL**：必须启用（在 `setup()` 中检查）
- **SsoUtils**：依赖注入，用于 state token 验证

### 配置项
```php
$Configuration['Plugins.Facebook.ApplicationID'] = '...';    // Facebook App ID
$Configuration['Plugins.Facebook.Secret'] = '...';           // Facebook App Secret
$Configuration['Plugins.Facebook.Scope'] = 'email';          // 请求权限范围
$Configuration['Plugins.Facebook.SocialSignIn'] = true;      // 启用社交登录
$Configuration['Plugins.Facebook.SocialReactions'] = true;   // 启用社交反应
$Configuration['Plugins.Facebook.UseFacebookNames'] = false; // 使用 Facebook 全名
$Configuration['Garden.Registration.SendConnectEmail'] = false; // 连接时发送邮件
```

### 设置页面
- **路径**：`/dashboard/social/facebook`
- **权限**：`Garden.Settings.Manage`
- **视图**：`views/settings.php`

---

## 数据模型

### 用户认证表（GDN_UserAuthentication）
- `Provider` = 'Facebook'
- `UniqueID` = Facebook 用户 ID

### 用户属性（User->Attributes）
```php
[
    'Facebook' => [
        'AccessToken' => 'long-lived-token',
        'Profile' => [
            'id' => '...',
            'name' => 'Full Name',
            'email' => 'user@example.com'
        ]
    ]
]
```

---

## 核心功能

### 1. 社交登录（Social Sign-In）
- 在登录页添加 Facebook 按钮
- 在 MeModule 和 GuestModule 中显示图标
- 移动端主题支持
- 支持设置为默认登录方式

### 2. 社交反应（Social Reactions）
- 在讨论/评论下方显示"分享到 Facebook"按钮
- 弹出窗口方式打开 Facebook 分享对话框

### 3. 资料连接（Profile Connection）
- 用户可在个人资料页连接/断开 Facebook 账号
- 支持 CSRF 保护（state token）

### 4. 用户名处理
- 可选使用 Facebook 全名作为论坛用户名
- 自动调整用户名验证规则（允许空格、最小 3 字符）

---

## 事件钩子

### 已实现的钩子
- `entryController_signIn_handler` - 登录页按钮
- `base_signInIcons_handler` - MeModule 图标
- `base_beforeSignInButton_handler` - GuestModule 按钮
- `base_beforeSignInLink_handler` - 移动端链接
- `base_afterReactions_handler` - 反应行分享按钮
- `base_getConnections_handler` - 连接列表
- `base_connectData_handler` - SSO 数据传输
- `profileController_facebookConnect_create` - 资料连接处理

---

## 测试与质量

### 测试覆盖
- **单元测试**：无
- **集成测试**：无
- **手动测试**：需要 Facebook 开发者账号

### 安全措施
1. **State Token 验证**：防止 CSRF 攻击（通过 `SsoUtils`）
2. **SSL 强制**：所有 API 调用使用 HTTPS（`CURLOPT_PROTOCOLS = CURLPROTO_HTTPS`）
3. **Token 刷新**：检测过期 token 并重新授权
4. **禁用自动连接**：`Garden.Registration.AutoConnect = false`

### 已知限制
1. API 版本固定为 2.7（可能需要更新）
2. 需要在 Facebook App 配置有效 OAuth 重定向 URI
3. Email 权限可能被用户拒绝

---

## 常见问题 (FAQ)

### Q1: 如何获取 Facebook App ID 和 Secret？
访问 [Facebook for Developers](https://developers.facebook.com/apps/)，创建应用并在"设置 > 基本"中查看。

### Q2: 回调 URL 应该设置成什么？
```
https://yourdomain.com/entry/connect/facebook
```

### Q3: 为什么无法获取用户邮箱？
检查 Facebook App 的权限配置，确保请求了 `email` 权限，并通过应用审核。

### Q4: 如何更新 Facebook API 版本？
修改 `FacebookPlugin` 类中的常量：
```php
const API_VERSION = '12.0'; // 更新为最新版本
```

### Q5: State token 验证失败怎么办？
确保服务器会话正常工作，检查 `SsoUtils` 服务是否正确注入。

---

## 相关文件清单

```
plugins/Facebook/
├── addon.json                          # 插件元数据
├── class.facebook.plugin.php           # 主插件类（672 行）
├── FacebookAuthenticator.php           # 认证器类
├── views/
│   └── settings.php                    # 后台设置页面
├── design/
│   └── help-newapp.png                 # 帮助图片
├── icon.png                            # 插件图标
└── facebook_social_connect.png         # 功能图标
```

---

## 变更记录 (Changelog)

| 日期 | 版本 | 变更内容 |
|------|------|---------|
| 2026-01-17 20:57:17 | 1.2.0 | 补充扫描生成文档 |

---

**最后更新**：2026-01-17 20:57:17
**维护状态**：活跃
**文档版本**：1.0.0
