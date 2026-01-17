# Twitter Social Connect 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **Twitter**

---

## 模块职责

**Twitter Social Connect** 插件实现了 Twitter OAuth 1.0a 社交登录功能，允许用户使用 Twitter 账号登录论坛，并可选择分享论坛内容到 Twitter。

---

## 入口与启动

### 主文件
- **插件类**：`class.twitter.plugin.php` - `TwitterPlugin`
- **认证器**：`TwitterAuthenticator.php`
- **配置文件**：`addon.json`

### 初始化流程
1. 用户点击 Twitter 登录按钮
2. 重定向到 `/entry/twauthorize`
3. 跳转到 Twitter OAuth 授权页
4. 回调到 `/entry/connect/twitter`
5. 交换 access token 并获取用户信息

---

## 对外接口

### OAuth 授权端点
- `GET /entry/twauthorize` - 开始 OAuth 授权流程
- `GET /entry/twauthorize/profile` - 从用户资料页连接 Twitter
- `GET /profile/twitterconnect` - 用户资料连接回调

### API 集成
- **Twitter API Base**: `https://api.twitter.com/1.1/`
- **认证流程**：
  - `POST /oauth/request_token` - 获取请求令牌
  - `GET /oauth/authenticate` - 用户授权
  - `POST /oauth/access_token` - 交换访问令牌
- **用户信息**：
  - `GET /account/verify_credentials.json` - 获取用户信息
- **发推文**：
  - `POST /statuses/update.json` - 分享讨论/评论到 Twitter

---

## 关键依赖与配置

### 外部依赖
- **OAuth 库**（内置）：`OAuthConsumer`, `OAuthToken`, `OAuthRequest`, `OAuthSignatureMethod_HMAC_SHA1`
- **cURL**：必须启用（在 `setup()` 中检查）

### 配置项（存储在 `config.php`）
```php
$Configuration['Plugins.Twitter.ConsumerKey'] = '...';       // Twitter App API Key
$Configuration['Plugins.Twitter.Secret'] = '...';            // Twitter App API Secret
$Configuration['Plugins.Twitter.SocialSignIn'] = true;       // 启用社交登录
$Configuration['Plugins.Twitter.SocialReactions'] = true;    // 启用社交反应
$Configuration['Plugins.Twitter.SocialSharing'] = true;      // 启用社交分享
```

### 设置页面
- **路径**：`/dashboard/social/twitter`
- **权限**：`Garden.Settings.Manage`
- **视图**：`views/settings.php`

---

## 数据模型

### 用户认证表（GDN_UserAuthentication）
- `Provider` = 'Twitter'
- `UniqueID` = Twitter 用户 ID

### 用户属性（User->Attributes）
```php
[
    'Twitter' => [
        'AccessToken' => [token, secret],
        'Profile' => [
            'id' => '...',
            'screen_name' => '@username',
            'name' => 'Display Name',
            'profile_image_url_https' => '...'
        ]
    ]
]
```

### OAuth Token 存储（GDN_UserAuthenticationToken）
- `Token` - OAuth token
- `TokenSecret` - OAuth token secret
- `TokenType` - 'request' 或 'access'
- `ProviderKey` = 'Twitter'
- `ForeignUserKey` - 关联的用户 ID（可选）
- `Lifetime` - 5 分钟（300 秒）

---

## 核心功能

### 1. 社交登录（Social Sign-In）
- 在登录页添加 Twitter 按钮
- 在 MeModule 和 GuestModule 中显示图标
- 移动端主题支持

### 2. 社交分享（Social Sharing）
- 发布讨论时可选分享到 Twitter
- 发布评论时可选分享到 Twitter
- 自动截断内容至 140 字符（`sliceTwitter()` 函数）

### 3. 社交反应（Social Reactions）
- 在讨论/评论下方显示"分享到 Twitter"按钮
- 弹出窗口方式分享

### 4. 资料连接（Profile Connection）
- 用户可在个人资料页连接/断开 Twitter 账号
- 存储访问令牌用于后续 API 调用

---

## 事件钩子

### 已实现的钩子
- `entryController_signIn_handler` - 登录页按钮
- `base_signInIcons_handler` - MeModule 图标
- `base_beforeSignInButton_handler` - GuestModule 按钮
- `base_beforeSignInLink_handler` - 移动端链接
- `base_discussionFormOptions_handler` - 讨论表单分享选项
- `discussionController_afterBodyField_handler` - 评论表单分享选项
- `discussionModel_afterSaveDiscussion_handler` - 讨论保存后分享
- `commentModel_afterSaveComment_handler` - 评论保存后分享
- `base_afterReactions_handler` - 反应行分享按钮
- `base_getConnections_handler` - 连接列表
- `base_connectData_handler` - SSO 数据传输

---

## 测试与质量

### 测试覆盖
- **单元测试**：无
- **集成测试**：无
- **手动测试**：需要 Twitter 开发者账号

### 安全措施
1. **OAuth 签名验证**：使用 HMAC-SHA1 签名
2. **Token 生命周期**：请求令牌 5 分钟过期
3. **CSRF 保护**：通过 state 参数（会话绑定）
4. **SSL 强制**：所有 Twitter API 调用使用 HTTPS
5. **Token 清理**：访问令牌交换后删除请求令牌

### 已知限制
1. Twitter 字符限制 140 字（现已更新为 280 字，代码可能需要更新）
2. 需要配置 Twitter App 回调 URL
3. 不支持 OAuth 2.0（Twitter 已弃用 OAuth 1.0a）

---

## 常见问题 (FAQ)

### Q1: 如何获取 Twitter API 密钥？
访问 [Twitter Developer Portal](https://developer.twitter.com/en/apps)，创建应用并获取 Consumer Key 和 Consumer Secret。

### Q2: 回调 URL 应该设置成什么？
```
https://yourdomain.com/entry/connect/twitter
```

### Q3: 为什么登录后头像不显示？
Twitter 头像 URL 通过 `profile_image_url_https` 字段获取，检查 API 返回是否包含该字段。

### Q4: 如何禁用自动分享功能？
在设置页面取消勾选"Social Sharing"，或在配置中设置：
```php
$Configuration['Plugins.Twitter.SocialSharing'] = false;
```

### Q5: OAuth Token 为什么会过期？
请求令牌（Request Token）设计为 5 分钟过期，用于安全性。访问令牌（Access Token）长期有效，除非用户撤销授权。

---

## 相关文件清单

```
plugins/Twitter/
├── addon.json                          # 插件元数据
├── class.twitter.plugin.php            # 主插件类（900+ 行）
├── TwitterAuthenticator.php            # 认证器类
├── views/
│   └── settings.php                    # 后台设置页面
├── design/
│   └── help-consumervalues.png         # 帮助图片
├── icon.png                            # 插件图标
└── twitter_social_connect.png          # 功能图标
```

---

## 变更记录 (Changelog)

| 日期 | 版本 | 变更内容 |
|------|------|---------|
| 2026-01-17 20:57:17 | 1.2 | 补充扫描生成文档 |

---

**最后更新**：2026-01-17 20:57:17
**维护状态**：活跃
**文档版本**：1.0.0
