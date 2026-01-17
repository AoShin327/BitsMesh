# Index Photos 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **IndexPhotos**

---

## 模块职责
**Index Photos** 插件在现代布局的讨论列表中显示发起人的头像和姓名。

---

## 核心功能

### 1. 头像显示
- 在讨论列表每一行显示发起人头像
- 仅在现代布局生效（非表格布局）

### 2. 响应式设计
- 移动端友好
- 自适应不同屏幕尺寸

### 3. 样式控制
- `design/indexphotos.css` - 头像样式
- 可自定义头像大小和位置

---

## 使用场景
- 让讨论列表更具社交属性
- 提升用户辨识度
- 美化论坛界面

---

## 注意事项
- 仅在现代布局（Modern Layout）生效
- 表格布局（Table Layout）无效果

---

## 样式自定义

### 调整头像大小
编辑 `design/indexphotos.css`:
```css
.IndexPhoto {
    width: 50px;
    height: 50px;
}
```

---

## 常见问题

### Q1: 为什么没有显示头像？
检查论坛是否启用了表格布局，插件仅支持现代布局。

### Q2: 如何调整头像位置？
修改 CSS 中的 `position` 和 `margin` 属性。

### Q3: 与其他插件冲突？
可能与自定义主题或布局插件冲突，需要调整 CSS。

---

## 相关文件清单
```
plugins/IndexPhotos/
├── addon.json
├── class.indexphotos.plugin.php
├── design/indexphotos.css
├── discussion_photos.png
└── icon.png (推测)
```

---

**最后更新**：2026-01-17 20:57:17 | **版本**：1.2.2
