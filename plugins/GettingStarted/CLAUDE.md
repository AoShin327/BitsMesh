# Getting Started 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **GettingStarted**

---

## 模块职责
在管理后台显示欢迎消息和新手引导清单，帮助新管理员快速上手论坛配置。

---

## 核心功能

### 1. 欢迎面板
- 显示在 Dashboard 首页
- 列出待完成任务清单

### 2. 任务清单示例
- ✓ 配置论坛基本信息
- ✓ 创建分类
- ✓ 邀请用户
- ✓ 自定义主题
- ✓ 安装插件

### 3. 进度跟踪
- 自动检测任务完成状态
- 完成后打勾标记

---

## 配置项
- **hidden**: true（默认隐藏，自动启用）
- 任务完成后可手动关闭面板

---

## 视图文件
- `default.php` - 主面板视图

### 样式
- `design/getting-started.css`
- `design/check.png` - 勾选图标

---

## 常见问题

### Q1: 如何关闭引导面板？
完成所有任务后面板会自动隐藏，或在设置中禁用插件。

### Q2: 可以自定义任务清单吗？
需要修改插件代码添加自定义任务。

---

## 相关文件清单
```
plugins/GettingStarted/
├── addon.json
├── default.php
├── design/
│   ├── getting-started.css
│   └── check.png
└── icon.png
```

---

**最后更新**：2026-01-17 20:57:17 | **版本**：1.0
