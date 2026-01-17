# Embed-Friendly 主题文档

[根目录](../../CLAUDE.md) > [themes](../) > **EmbedFriendly**

---

## 模块职责
**Embed-Friendly** 是一个流式布局主题，适合嵌入到其他网站页面中使用。

---

## 核心特性

### 1. 流式布局（Fluid Layout）
- 自适应任意宽度
- 无固定宽度限制
- 适合 iframe 嵌入

### 2. 嵌入场景
- 嵌入到企业网站
- 嵌入到博客
- 嵌入到帮助中心
- 嵌入到社区页面

### 3. 简洁设计
- 最小化外部边框
- 移除不必要的装饰
- 聚焦内容本身

---

## 文件结构
```
themes/EmbedFriendly/
├── addon.json                          # 主题元数据
├── design/custom.css                   # 自定义样式
├── views/default.master.tpl            # 主模板
└── screenshot.png                      # 主题预览图
```

---

## 使用示例

### iframe 嵌入
```html
<iframe src="https://forum.example.com"
        width="100%"
        height="800"
        frameborder="0"></iframe>
```

### 设置主题
1. 在 Dashboard > Appearance > Themes 选择 Embed-Friendly
2. 配置嵌入设置（可选）

---

## 注意事项
- 移除顶部导航和页脚（可选）
- 确保父页面允许 iframe 嵌入
- 调整 iframe 高度以适应内容

---

## 常见问题

### Q1: 如何移除顶部和页脚？
在配置中启用 `Garden.Embed.Allow = true`。

### Q2: 为什么嵌入后无法滚动？
调整 iframe 高度或使用自动高度脚本。

### Q3: 支持响应式吗？
是的，主题会自动适应 iframe 宽度。

---

**最后更新**：2026-01-17 20:57:17 | **版本**：3.0
