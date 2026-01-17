# Hero Image 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **heroimage**

---

## 模块职责
**Hero Image** 插件为每个分类添加头图上传功能，支持默认图片和父分类继承。

---

## 核心功能

### 1. 分类头图上传
- 每个分类独立头图
- 支持 JPG/PNG/GIF 格式

### 2. 继承机制
- 子分类可继承父分类头图
- 支持设置默认头图

### 3. Smarty 模板标签
```smarty
{hero_image_url}           // 获取当前分类头图 URL
{hero_image_link}          // 获取头图链接（通过 SmartyPlugins）
```

---

## 设置页面
- **路径**：`/settings/heroimage`
- **权限**：`Garden.Settings.Manage`

---

## 文件结构
- `class.heroimage.plugin.php` - 主插件类
- `SmartyPlugins/function.hero_image_link.php` - Smarty 自定义函数

---

## 使用示例

### 在主题中显示头图
```smarty
<div class="Hero" style="background-image: url({hero_image_url});">
    <h1>分类标题</h1>
</div>
```

或使用链接函数：
```smarty
{hero_image_link category=$Category}
```

---

## 数据存储
头图路径存储在分类表（GDN_Category）的自定义字段或元数据中。

---

## 常见问题

### Q1: 如何设置默认头图？
在插件设置页面上传默认头图。

### Q2: 如何禁用继承？
需要修改插件代码，移除父分类查找逻辑。

### Q3: 支持响应式图片吗？
需要在主题中添加 CSS 支持。

---

## 相关文件清单
```
plugins/heroimage/
├── addon.json
├── class.heroimage.plugin.php
├── SmartyPlugins/
│   └── function.hero_image_link.php
├── hero_image.png
└── icon.png
```

---

**最后更新**：2026-01-17 20:57:17 | **版本**：1.0 | **许可证**：Proprietary
