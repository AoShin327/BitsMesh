# Emoji Extender 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **emojiextender**

---

## 模块职责

**Emoji Extender** 插件允许管理员更换论坛的表情包（Emoji Set），支持多种预定义表情集。

---

## 入口与启动

### 主文件
- **插件类**：`class.emojiextender.plugin.php` - `EmojiExtenderPlugin`
- **配置文件**：`addon.json`

### 设置页面
- **路径**：`/settings/emojiextender`
- **权限**：`Garden.Settings.Manage`

---

## 核心功能

### 1. 表情包管理
- 提供多个预定义表情包
- 支持切换不同表情风格

### 2. 表情目录
- `emoji/little/` - 小尺寸表情包
- `emoji/twitter/` - Twitter 风格表情包（可能）
- 包含 PNG 图片和 manifest.php 清单文件

### 3. 表情渲染
- 自动替换表情代码为图片
- 支持 Retina 显示（@2x 图片）

---

## 表情列表示例

```
:smile: :grin: :lol: :wink: :cry: :angry:
:love: :heart: :+1: :-1: :surprised: :confused:
:skull: :fearful: :sunglasses: :star: ...
```

---

## 关键依赖与配置

### 配置项
```php
$Configuration['Garden.EmojiSet'] = 'little'; // 表情包名称
```

---

## 测试与质量

### 测试覆盖
- **单元测试**：无

---

## 常见问题 (FAQ)

### Q1: 如何添加自定义表情包？
1. 在 `emoji/` 目录创建新文件夹
2. 添加表情图片（PNG 格式）
3. 创建 `manifest.php` 配置文件
4. 在设置页面选择新表情包

### Q2: 表情图片尺寸要求？
建议 20x20 或 32x32 像素，同时提供 @2x 高清版本。

---

## 相关文件清单

```
plugins/emojiextender/
├── addon.json                          # 插件元数据
├── class.emojiextender.plugin.php      # 主插件类（推测）
├── emoji/
│   ├── little/
│   │   ├── manifest.php                # 表情清单
│   │   ├── *.png                       # 表情图片（50+ 个）
│   │   └── *@2x.png                    # Retina 图片
│   └── twitter/                        # Twitter 风格（可能）
├── emoji_set.png                       # 功能图标
└── icon.png                            # 插件图标（推测）
```

---

**最后更新**：2026-01-17 20:57:17 | **文档版本**：1.0.0
