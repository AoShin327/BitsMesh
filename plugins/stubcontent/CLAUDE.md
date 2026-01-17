# Stub Content 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **stubcontent**

---

## 模块职责
**Stub Content** 插件为新论坛自动生成示例内容（讨论、评论、用户、对话），方便演示和测试。

---

## 核心功能

### 1. 自动生成示例数据
- 示例用户
- 示例讨论
- 示例评论
- 示例私信对话

### 2. 数据模板
使用 YAML 文件定义示例内容：
- `content/user.yaml` - 用户模板
- `content/discussion.yaml` - 讨论模板
- `content/comment.yaml` - 评论模板
- `content/conversation.yaml` - 对话模板

---

## 使用场景
- 新论坛初始化
- 演示环境搭建
- 测试功能
- UI 开发

---

## 数据模板示例

### user.yaml（推测）
```yaml
- username: JohnDoe
  email: john@example.com
  name: John Doe

- username: JaneSmith
  email: jane@example.com
  name: Jane Smith
```

### discussion.yaml（推测）
```yaml
- title: "Welcome to Vanilla Forums!"
  body: "This is a sample discussion..."
  category: General

- title: "How to customize themes?"
  body: "Let's discuss theme customization..."
  category: Support
```

---

## 触发方式
- 插件启用时自动生成（推测）
- 或通过管理后台手动触发

---

## 清理
生成的示例内容可在管理后台批量删除。

---

## 常见问题

### Q1: 如何自定义示例内容？
编辑 `content/*.yaml` 文件。

### Q2: 生成内容后如何删除？
在管理后台批量删除用户和讨论。

### Q3: 对生产环境有影响吗？
建议仅在开发/演示环境启用。

---

## 相关文件清单
```
plugins/stubcontent/
├── addon.json
├── class.stubcontent.plugin.php
├── content/
│   ├── user.yaml
│   ├── discussion.yaml
│   ├── comment.yaml
│   └── conversation.yaml
├── locale/empty
├── stubcontent-plugin.png
└── icon.png (推测)
```

---

**最后更新**：2026-01-17 20:57:17 | **版本**：1.0
