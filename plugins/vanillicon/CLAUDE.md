# Vanillicon 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **vanillicon**

---

## 模块职责
为用户提供有趣的默认图标，通过 vanillicon.com 服务生成独特的几何图形头像。

---

## 核心功能
- 自动生成独特的几何图形头像
- 基于用户 ID 或邮箱生成
- 替代 Gravatar 或默认头像

---

## Vanillicon API
```php
$url = "https://vanillicon.com/v2/" . urlencode($identifier) . ".svg";
```

---

## 配置项
- **设置页面**：`/settings/vanillicon`
- **优先级**：可与 Gravatar 配合使用

---

**最后更新**：2026-01-17 20:57:17 | **版本**：2.1.0
