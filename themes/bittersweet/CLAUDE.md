# Bitter Sweet 主题文档

[根目录](../../CLAUDE.md) > [themes](../) > **bittersweet**

---

## 模块职责
**Bitter Sweet** 是一个经典主题，展示如何将博客风格的顶部菜单、横幅和广告位与社区论坛结合。

---

## 核心特性

### 1. 设计风格
- 受 Google WebFont "Bitter" 启发
- 注重字体、颜色和边框
- 不改变论坛布局，仅修改样式

### 2. 布局元素
- 顶部菜单栏
- 横幅区域
- 广告位
- 论坛内容区

---

## 文件结构
```
themes/bittersweet/
├── addon.json                          # 主题元数据
├── design/custom.css                   # 自定义样式
├── views/default.master.tpl            # 主模板（Smarty）
└── screenshot.png                      # 主题预览图
```

---

## 自定义

### 修改样式
编辑 `design/custom.css`，调整：
- 字体族（Font Family）
- 颜色方案
- 边框样式
- 间距

### 修改布局
编辑 `views/default.master.tpl`（Smarty 模板），调整：
- 顶部菜单结构
- 横幅位置
- 内容区布局

---

## 常见问题

### Q1: 如何添加顶部菜单项？
在主模板文件中修改菜单 HTML。

### Q2: 如何更换字体？
在 `custom.css` 中修改 `font-family` 属性。

### Q3: 适合移动端吗？
需要额外调整响应式样式。

---

**最后更新**：2026-01-17 20:57:17 | **版本**：1.1
