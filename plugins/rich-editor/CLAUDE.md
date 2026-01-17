[根目录](../../CLAUDE.md) > [plugins](../) > **rich-editor**

---

# Rich Editor 插件

## 变更记录

| 日期 | 变更内容 |
|------|---------|
| 2026-01-17 20:48:21 | 初始化模块文档 |

---

## 模块职责

**Rich Editor** 是 Vanilla 的新一代 WYSIWYG 富文本编辑器，提供直观的内容编辑体验。

**核心功能**：
- 所见即所得编辑
- 富内容嵌入（图片、视频、链接）
- Markdown 支持
- BBCode 兼容模式
- Emoji 选择器
- @ 提及自动补全
- 代码高亮
- 移动端优化

---

## 入口与启动

### 插件配置
**文件**：`addon.json`

```json
{
  "key": "rich-editor",
  "name": "Rich Editor",
  "version": "1.0.1",
  "require": {
    "vanilla": ">=2.5",
    "dashboard": ">= 2.6"
  }
}
```

### 入口文件
- **后端插件类**：`RichEditorPlugin.php`
- **前端入口**：
  - `src/scripts/entries/forum.ts`（论坛侧）
  - `src/scripts/entries/admin.ts`（管理侧）

---

## 对外接口

### PHP 接口
- **样式控制器**：`controllers/RichEditorStylesController.php`
- **API 控制器**：`controllers/api/RichApiController.php`

### TypeScript API

#### 主要模块
**位置**：`src/scripts/`

| 模块路径 | 职责 |
|---------|------|
| `quill/` | Quill 编辑器核心与自定义模块 |
| `editor/` | 编辑器组件与样式 |
| `flyouts/` | 弹出菜单（Emoji、媒体插入） |
| `toolbars/` | 工具栏（格式化、链接） |
| `menuBar/` | 菜单栏（段落格式） |

#### Quill 自定义模块
- `ClipboardModule` - 粘贴处理
- `MarkdownModule` - Markdown 语法支持
- `FocusModule` - 焦点管理
- `EmbedInsertionModule` - 嵌入内容插入
- `KeyboardBindings` - 快捷键

---

## 关键依赖与配置

### 前端依赖
- **Quill.js**：富文本编辑器核心
- **React**：UI 组件
- **TypeScript**：类型安全
- **Redux**：状态管理

### 配置项
```php
// 启用 Rich Editor
$Configuration['EnabledPlugins']['rich-editor'] = true;

// 默认格式
$Configuration['Garden']['InputFormatter'] = 'Rich';
$Configuration['Garden']['MobileInputFormatter'] = 'Rich';
```

---

## 数据模型

### 内容格式
Rich Editor 使用 **Quill Delta** 格式存储内容，并转换为 HTML 用于显示。

#### Delta 格式示例
```json
{
  "ops": [
    {"insert": "Hello "},
    {"insert": "World", "attributes": {"bold": true}},
    {"insert": "\n"}
  ]
}
```

#### 输出 HTML
```html
<p>Hello <strong>World</strong></p>
```

---

## 测试与质量

### 测试文件
**位置**：`src/scripts/__tests__/`, `src/scripts/quill/**/*.test.ts`

**已有测试**：
- `ClipboardModule.test.ts` - 粘贴功能测试
- `KeyboardBindings.test.ts` - 快捷键测试
- `MarkdownModule.test.ts` - Markdown 转换测试
- `Formatter.test.ts` - 格式化测试
- `FocusModule.test.ts` - 焦点管理测试

### 运行测试
```bash
cd plugins/rich-editor
yarn test
```

---

## 常见问题 (FAQ)

### Q1: 如何添加自定义 Blot（内容块）？
**A**: 在 `src/scripts/quill/blots/` 下创建新 Blot 类，继承 `FocusableEmbedBlot` 或其他基类。

### Q2: 如何自定义工具栏按钮？
**A**: 修改 `src/scripts/toolbars/` 下的工具栏组件。

### Q3: 如何处理自定义 Markdown 语法？
**A**: 扩展 `MarkdownModule` 的规则映射。

### Q4: 如何调试前端代码？
**A**:
```bash
yarn dev        # 开发模式（热重载）
yarn build      # 生产构建
```

---

## 相关文件清单

### 关键目录结构
```
rich-editor/
├── addon.json                 # 插件配置
├── RichEditorPlugin.php       # 后端主类
├── controllers/               # 控制器
│   ├── RichEditorStylesController.php
│   └── api/
│       └── RichApiController.php
├── src/                       # TypeScript 源码
│   └── scripts/
│       ├── entries/          # 入口
│       │   ├── admin.ts
│       │   └── forum.ts
│       ├── quill/            # Quill 核心
│       │   ├── blots/       # 自定义 Blot
│       │   ├── modules/     # 自定义模块
│       │   └── *.test.ts    # 测试
│       ├── editor/           # 编辑器组件
│       ├── flyouts/          # 弹出菜单
│       ├── toolbars/         # 工具栏
│       └── menuBar/          # 菜单栏
├── views/                     # PHP 视图
├── locale/                    # 多语言
│   └── en.php
├── yarn.lock                  # 依赖锁定
└── package.json               # （推测，未直接扫描到）
```

---

**最后更新**：2026-01-17 20:48:21
