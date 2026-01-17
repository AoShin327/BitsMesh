# Vanilla Statistics 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **VanillaStats**

---

## 模块职责
在管理后台提供论坛活动统计图表（新用户、讨论、评论、页面浏览量）。

---

## 核心功能

### 1. 数据可视化
- 使用 C3.js 图表库
- 支持时间范围选择（日、周、月）
- 显示活跃度趋势

### 2. 统计指标
- 新用户注册数
- 新讨论数
- 新评论数
- 页面浏览量（需配置）
- 浏览器统计

---

## 关键依赖

### 前端库
- **C3.js**：图表库（`design/vendors/c3.min.css`）
- **D3.js**：依赖库（推测）

### 前端资源
- `js/vanillastats.js` - 交互脚本
- `design/*.png` - 图标和精灵图
  - `dashboard-sprites.png`
  - `daterange-sprites.png`
  - `slider-sprites.png`
  - 浏览器图标（Chrome, Firefox, Safari, Opera）

---

## 视图文件
- `views/dashboard.php` - 主仪表盘
- `views/dashboardlocalhost.php` - 本地环境提示
- `views/dashboardsummaries.php` - 数据摘要

---

## 配置项
- 需要配置统计数据源
- 本地环境可能显示警告

---

## 常见问题

### Q1: 本地环境无法查看统计？
本地环境（localhost）需要特殊配置，使用 `dashboardlocalhost.php` 视图。

### Q2: 数据如何收集？
可能依赖 Vanilla Analytics 服务或自定义埋点。

---

## 相关文件清单
```
plugins/VanillaStats/
├── addon.json
├── class.vanillastats.plugin.php
├── js/vanillastats.js
├── design/
│   ├── vendors/c3.min.css
│   ├── *.png (精灵图和图标)
├── views/
│   ├── dashboard.php
│   ├── dashboardlocalhost.php
│   └── dashboardsummaries.php
└── icon.png
```

---

**最后更新**：2026-01-17 20:57:17 | **版本**：2.0.7
