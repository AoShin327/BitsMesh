# Pockets 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **pockets**

---

## 模块职责
**Pockets** 插件允许管理员在论坛的各个位置插入自定义 HTML 代码（广告、公告、自定义内容等）。

---

## 核心功能

### 1. Pocket 管理
- 创建/编辑/删除 Pocket
- 支持纯 HTML、JavaScript、CSS

### 2. 显示位置
- Head（页面头部）
- AfterBody（body 标签后）
- BeforeBody（body 标签前）
- Panel（侧边栏）
- 自定义位置（通过钩子）

### 3. 显示条件
- 特定页面（首页、分类、讨论）
- 用户角色（游客、成员、管理员）
- 设备类型（桌面、移动）

### 4. 优先级控制
- 排序显示顺序
- 启用/禁用

---

## 权限
```php
'Plugins.Pockets.Manage' => '管理 Pockets'
'Garden.NoAds.Allow' => '无广告权限'
```

---

## 设置页面
- **路径**：`/settings/pockets`
- **权限**：`Plugins.Pockets.Manage`

---

## 视图文件
- `views/index.php` - Pocket 列表
- `views/addedit.php` - 添加/编辑 Pocket
- `views/delete.php` - 删除确认

---

## 前端脚本
- `js/pockets.js` - 编辑器增强
- `design/pockets.css` - 样式

---

## 数据模型

### Pocket 表（可能）
- `PocketID` - Pocket ID
- `Name` - 名称
- `Body` - HTML 内容
- `Location` - 显示位置
- `Sort` - 排序
- `Type` - 类型（default/ad）
- `Condition` - 显示条件（JSON）
- `MobileOnly` / `MobileNever` - 移动端控制

---

## 安全警告
Pockets 允许插入任意 HTML/JS，使用不当可能破坏网站或造成安全风险。仅授权给可信管理员。

---

## 常见用例

### 1. 添加 Google Analytics
位置：Head
```html
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID"></script>
```

### 2. 显示公告
位置：Panel
```html
<div class="Box Announcement">
    <h4>重要公告</h4>
    <p>论坛将于本周五进行维护...</p>
</div>
```

### 3. 添加广告
位置：AfterBody
```html
<div class="Ad">
    <!-- 广告代码 -->
</div>
```

---

## 常见问题

### Q1: 如何隐藏广告给特定角色？
使用角色条件过滤，或授予 `Garden.NoAds.Allow` 权限。

### Q2: Pocket 不显示怎么办？
检查显示条件、页面匹配、排序优先级。

### Q3: 如何备份 Pockets？
导出数据库中的 Pocket 表。

---

## 相关文件清单
```
plugins/pockets/
├── addon.json
├── PocketsPlugin.php
├── library/Pocket.php
├── js/pockets.js
├── design/pockets.css
├── locale/en.php
├── views/
│   ├── index.php
│   ├── addedit.php
│   └── delete.php
├── LICENSE.md
├── icon.png
└── pocket.png
```

---

**最后更新**：2026-01-17 20:57:17 | **版本**：1.4.1
