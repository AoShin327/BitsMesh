# Akismet 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **Akismet**

---

## 模块职责

**Akismet** 插件集成 Akismet 反垃圾服务，自动检测未验证用户的帖子和注册申请中的垃圾内容。

---

## 入口与启动

### 主文件
- **插件类**：`class.akismet.plugin.php` - `AkismetPlugin`
- **配置文件**：`addon.json`

### 设置页面
- **路径**：`/settings/akismet`
- **权限**：`Garden.Settings.Manage`

---

## 对外接口

### Akismet API
- **服务地址**：`https://{apikey}.rest.akismet.com/1.1/`
- **端点**：
  - `/comment-check` - 检查内容是否为垃圾
  - `/submit-spam` - 提交误判为垃圾
  - `/submit-ham` - 提交误判为正常

---

## 核心功能

### 1. 自动垃圾检测
- 检测未验证用户的帖子
- 检测注册申请
- 自动标记垃圾内容

### 2. 手动审核
- 管理员可审核被标记内容
- 提交误判反馈给 Akismet

### 3. 白名单
- 已验证用户不检测
- 管理员和版主不检测

---

## 关键依赖与配置

### 配置项
```php
$Configuration['Plugins.Akismet.APIKey'] = '...';  // Akismet API Key
$Configuration['Plugins.Akismet.VerifyUsers'] = false; // 检测已验证用户
```

### API Key 获取
访问 [Akismet.com](https://akismet.com/) 注册获取 API Key。

---

## 事件钩子

### 已实现的钩子
- 讨论/评论保存前钩子（垃圾检测）
- 用户注册钩子（检测垃圾注册）

---

## 测试与质量

### 测试覆盖
- **单元测试**：无

### 已知限制
1. 需要外部 API，依赖网络连接
2. 可能误判正常内容
3. API 调用有频率限制

---

## 常见问题 (FAQ)

### Q1: 如何获取 Akismet API Key？
访问 [Akismet.com](https://akismet.com/) 注册账号获取。

### Q2: 个人博客可以免费使用吗？
Akismet 提供个人非商业使用的免费 API Key。

### Q3: 如何处理误判？
在管理后台审核内容，标记为"非垃圾"并提交反馈给 Akismet。

---

## 相关文件清单

```
plugins/Akismet/
├── addon.json                          # 插件元数据
├── class.akismet.plugin.php            # 主插件类
├── views/                              # 设置页面视图（可能）
└── icon.png                            # 插件图标
```

---

**最后更新**：2026-01-17 20:57:17 | **文档版本**：1.0.0
