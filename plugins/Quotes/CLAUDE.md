# Quotes 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **Quotes**

---

## 模块职责

**Quotes** 插件为评论添加引用功能，允许用户轻松引用他人的评论内容进行回复。

---

## 入口与启动

### 主文件
- **插件类**：`class.quotes.plugin.php` - `QuotesPlugin`
- **前端脚本**：`js/quotes.js`
- **样式文件**：`css/cleditor.css`
- **配置文件**：`addon.json`

---

## 对外接口

### 前端功能
- **引用按钮**：在每条评论下方添加"Quote"链接
- **引用格式**：自动插入引用块到编辑器
- **视图**：
  - `views/quotes.php` - 引用按钮渲染
  - `views/getquote.php` - 获取引用内容

### JavaScript API
- **文件**：`js/quotes.js`
- **功能**：
  - 点击 Quote 按钮触发
  - AJAX 获取原评论内容
  - 插入引用格式到编辑器

---

## 核心功能

### 1. 引用按钮
- 在评论操作栏添加"Quote"链接
- 支持移动端友好设计

### 2. 引用格式
```html
<blockquote class="Quote">
  <div class="QuoteAuthor">@username said:</div>
  <div class="QuoteText">原始评论内容...</div>
</blockquote>
```

### 3. 编辑器兼容
- 支持传统编辑器（CLEditor）
- 通过 CSS 控制引用样式

---

## 关键依赖与配置

### 外部依赖
- **jQuery**：前端交互
- **Vanilla 编辑器**：插入引用内容

### 配置项
- 无特殊配置项，开箱即用

---

## 测试与质量

### 测试覆盖
- **单元测试**：无
- **集成测试**：无

### 已知限制
1. 仅支持传统编辑器，可能与富文本编辑器（Rich Editor）冲突
2. 引用嵌套层级无限制（可能导致 UI 问题）
3. 无引用通知功能

---

## 事件钩子

### 已实现的钩子
- 评论操作栏钩子（添加 Quote 按钮）
- 编辑器内容插入钩子

---

## 常见问题 (FAQ)

### Q1: 如何自定义引用样式？
修改 `css/cleditor.css` 文件中的 `.Quote` 类样式。

### Q2: 与 Rich Editor 插件冲突怎么办？
Rich Editor 插件有自己的引用功能，建议禁用 Quotes 插件。

### Q3: 如何限制引用层级？
需要修改插件代码，添加引用深度检测逻辑。

### Q4: 引用是否支持 Markdown？
取决于论坛配置的格式器，引用内容会保留原格式。

---

## 相关文件清单

```
plugins/Quotes/
├── addon.json                          # 插件元数据
├── class.quotes.plugin.php             # 主插件类
├── js/
│   └── quotes.js                       # 前端交互脚本
├── css/
│   └── cleditor.css                    # 引用样式
├── views/
│   ├── quotes.php                      # 引用按钮视图
│   └── getquote.php                    # 获取引用内容视图
├── icon.png                            # 插件图标
└── quotes.png                          # 功能图标
```

---

## 变更记录 (Changelog)

| 日期 | 版本 | 变更内容 |
|------|------|---------|
| 2026-01-17 20:57:17 | 1.9 | 补充扫描生成文档 |

---

**最后更新**：2026-01-17 20:57:17
**维护状态**：活跃
**文档版本**：1.0.0
