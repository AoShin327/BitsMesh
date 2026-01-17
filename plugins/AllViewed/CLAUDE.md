# All Viewed 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **AllViewed**

---

## 模块职责
**All Viewed** 插件允许用户将所有讨论或特定分类的讨论标记为已读。

---

## 核心功能

### 1. 全部标记已读
- 一键将所有讨论标记为已读
- 清除未读标记

### 2. 分类标记已读
- 将特定分类下的讨论标记为已读
- 支持递归子分类

### 3. 显示位置
- 讨论列表顶部
- 分类页面
- 用户菜单（可能）

---

## 使用场景
- 长时间未访问后快速清除未读
- 分类浏览后标记已读
- 提升用户体验

---

## 数据处理
更新用户的讨论查看记录（UserDiscussion 表）：
```php
DateLastViewed = NOW()
CountComments = Discussion.CountComments
```

---

## 常见问题

### Q1: 是否支持撤销操作？
不支持，标记为已读后无法撤销。

### Q2: 对性能有影响吗？
大量讨论时可能需要较长时间，建议异步处理。

### Q3: 游客可以使用吗？
不可以，需要登录用户。

---

## 相关文件清单
```
plugins/AllViewed/
├── addon.json
├── class.allviewed.plugin.php
├── all-viewed.png
└── icon.png
```

---

**最后更新**：2026-01-17 20:57:17 | **版本**：2.2 | **许可证**：GNU GPLv2
