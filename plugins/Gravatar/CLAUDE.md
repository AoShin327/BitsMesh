# Gravatar 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **Gravatar**

---

## 模块职责
为未上传自定义头像的用户提供 Gravatar 全球统一头像服务。

---

## 核心功能
- 自动从 Gravatar.com 获取用户头像
- 基于邮箱地址 MD5 哈希匹配
- 提供默认头像（`default.png`）

---

## 配置项
- **设置页面**：`/settings/gravatar`
- **默认头像**：`default.png` / `default_250.png`

---

## Gravatar API
```php
$url = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email)));
```

---

**最后更新**：2026-01-17 20:57:17 | **版本**：1.5
