# BitsMesh 主题设计文档

**创建日期**：2026-01-17
**项目**：Vanilla Forums BitsMesh 主题
**状态**：设计完成，待实施

---

## 1. 项目概述

基于 NodeSeek 论坛样式，为 Vanilla Forums 创建名为 BitsMesh 的自定义主题。采用深度定制方案，允许修改 Vanilla 核心代码以实现完美复刻。

### 1.1 核心决策

| 项目 | 决定 |
|------|------|
| **主题名称** | nodeseek (BitsMesh 品牌) |
| **CSS 前缀** | `bits-*` |
| **实现方式** | 从零创建纯净主题 + 深度定制核心代码 |
| **页面范围** | 首页、帖子页、用户中心、分类页、搜索结果 |
| **配色系统** | 后台可配置，CSS 变量驱动 |
| **暗色模式** | 手动切换，localStorage 存储 |
| **测试策略** | Playwright 视觉回归 + 端到端测试 |

### 1.2 开发环境

- PHP 8.0+
- MySQL 5.7
- 本地开发地址：http://localhost:8357/

---

## 2. 主题目录结构

```
themes/nodeseek/
├── addon.json                    # 主题元数据与配置项定义
├── package.json                  # NPM 依赖
├── webpack.config.js             # 构建配置
├── settings/
│   └── configuration.php         # 后台配色配置
├── design/
│   ├── bits-style.css           # 主样式
│   ├── variables.css            # CSS 变量
│   └── images/                  # 图标资源
├── js/
│   ├── theme.js                 # 主脚本入口
│   ├── darkMode.js              # 暗色模式切换
│   ├── imageBox.js              # 图片灯箱
│   ├── concurrency.js           # 并发控制
│   └── cache.js                 # 客户端缓存
├── views/
│   ├── default.master.tpl       # 主模板（三栏布局）
│   ├── discussion.master.tpl    # 帖子详情页
│   └── partials/
│       ├── header.tpl           # 顶栏导航
│       ├── footer.tpl           # 页脚
│       ├── post-list.tpl        # 帖子列表项
│       └── sidebar.tpl          # 右侧面板
└── tests/
    ├── playwright.config.ts
    ├── e2e/                     # 端到端测试
    └── visual/                  # 视觉回归测试
```

---

## 3. 后台配色系统

### 3.1 配置项定义

```php
// settings/configuration.php
$Configuration['Garden']['ThemeOptions']['Options']['PrimaryColor'] = '#2ea44f';
$Configuration['Garden']['ThemeOptions']['Options']['SecondaryColor'] = '#45ca6b';
$Configuration['Garden']['ThemeOptions']['Options']['TextColor'] = '#333333';
$Configuration['Garden']['ThemeOptions']['Options']['LinkColor'] = '#555555';
$Configuration['Garden']['ThemeOptions']['Options']['BgMainColor'] = '#ffffff';
$Configuration['Garden']['ThemeOptions']['Options']['BgSubColor'] = '#fbfbfb';
```

### 3.2 CSS 变量映射

```css
/* design/variables.css */
:root {
  --bits-primary: var(--theme-primary, #2ea44f);
  --bits-secondary: var(--theme-secondary, #45ca6b);
  --bits-text: var(--theme-text, #333);
  --bits-link: var(--theme-link, #555);
  --bits-bg-main: var(--theme-bg-main, #fff);
  --bits-bg-sub: var(--theme-bg-sub, #fbfbfb);
}
```

---

## 4. 响应式布局设计

### 4.1 断点策略

| 断点 | 布局 | 说明 |
|------|------|------|
| ≥1360px | 三栏 | 左侧分类 + 中间内容 + 右侧面板 |
| 800px ~ 1359px | 两栏 | 中间内容 + 右侧面板（分类移入右侧） |
| ≤800px | 单栏 | 仅中间内容，右侧隐藏，分类进入汉堡菜单 |

### 4.2 布局示意

```
┌─ 大屏幕 ≥1360px ─────────────────────────────────────────────┐
│  ┌────────┬──────────────────────────┬─────────────────────┐ │
│  │ 左侧栏 │     中间内容区            │     右侧面板        │ │
│  │ 150px  │     bits-body-left       │     260px           │ │
│  │ 分类   │  帖子列表/帖子详情        │  新用户/统计/广告   │ │
│  └────────┴──────────────────────────┴─────────────────────┘ │
└──────────────────────────────────────────────────────────────┘

┌─ 中屏幕 800px ~ 1359px ──────────────────────────────────────┐
│  ┌──────────────────────────┬─────────────────────────────┐  │
│  │     中间内容区            │     右侧面板                │  │
│  │                          │  📂 分类导航（从左侧移入）  │  │
│  └──────────────────────────┴─────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘

┌─ 小屏幕 ≤800px ──────────────────────────────────────────────┐
│  ┌──────────────────────────────────────────────────────┐    │
│  │     中间内容区（全宽）                                │    │
│  │  （右侧隐藏，分类进入汉堡菜单）                       │    │
│  └──────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────┘
```

### 4.3 CSS 实现

```css
#bits-left-panel { display: none; }

@media (min-width: 1360px) {
  #bits-left-panel { display: block; }
  #bits-right-panel .category-list { display: none; }
}

@media (max-width: 1359px) {
  #bits-right-panel .category-list { display: block; }
}

@media (max-width: 800px) {
  #bits-right-panel { display: none; }
}
```

---

## 5. 暗色模式实现

### 5.1 切换机制

```javascript
// js/darkMode.js
class DarkModeToggle {
  constructor() {
    this.storageKey = 'bits-theme-mode';
    this.darkClass = 'dark-layout';
  }

  init() {
    const saved = localStorage.getItem(this.storageKey);
    if (saved === 'dark') {
      document.body.classList.add(this.darkClass);
    }
    this.bindToggleButton();
  }

  toggle() {
    const isDark = document.body.classList.toggle(this.darkClass);
    localStorage.setItem(this.storageKey, isDark ? 'dark' : 'light');
  }
}
```

### 5.2 CSS 变量覆盖

```css
/* 亮色模式（默认） */
:root {
  --bits-text: #333;
  --bits-bg-main: #fff;
  --bits-bg-sub: #fbfbfb;
  --bits-link: #555;
}

/* 暗色模式 */
body.dark-layout {
  --bits-text: #aaa;
  --bits-bg-main: #272727;
  --bits-bg-sub: #3b3b3b;
  --bits-link: #c5c5c5;
}
```

---

## 6. 自动化测试

### 6.1 测试结构

```
tests/
├── playwright.config.ts
├── e2e/
│   ├── homepage.spec.ts
│   ├── discussion.spec.ts
│   ├── user-profile.spec.ts
│   └── dark-mode.spec.ts
└── visual/
    ├── screenshots/
    └── visual-regression.spec.ts
```

### 6.2 测试用例示例

```typescript
// e2e/homepage.spec.ts
test('帖子列表正确显示', async ({ page }) => {
  await page.goto('http://localhost:8357/');
  await expect(page.locator('.bits-post-list')).toBeVisible();
});

// visual/visual-regression.spec.ts
test('首页视觉回归', async ({ page }) => {
  await page.goto('http://localhost:8357/');
  await expect(page).toHaveScreenshot('homepage.png');
});
```

### 6.3 运行命令

```bash
npx playwright test              # 运行所有测试
npx playwright test visual/      # 仅视觉回归
npx playwright test --update-snapshots  # 更新基准截图
```

---

## 7. 核心页面组件

### 7.1 帖子列表组件

```html
<div class="bits-post-list">
  <div class="bits-post-list-item">
    <div class="avatar-normal">
      <img src="{$Author.PhotoUrl}" alt="{$Author.Name}">
    </div>
    <div class="bits-post-list-content">
      <a class="bits-post-title" href="{$Discussion.Url}">
        {if $Discussion.Pinned}<span class="bits-badge pined">置顶</span>{/if}
        {$Discussion.Name}
      </a>
      <div class="bits-post-info">
        <span class="info-item">{$Author.Name}</span>
        <span class="info-item">{$Discussion.DateInserted|date_format}</span>
        <span class="info-item">💬 {$Discussion.CountComments}</span>
        <span class="info-item">👁 {$Discussion.CountViews}</span>
      </div>
    </div>
    <a class="bits-post-category" href="{$Category.Url}">{$Category.Name}</a>
  </div>
</div>
```

### 7.2 帖子详情组件

```html
<div class="bits-post-wrapper">
  <div class="bits-post">
    <h1 class="bits-post-title">{$Discussion.Name}</h1>
    <div class="bits-content-meta-info">
      <div class="avatar-wrapper">
        <img class="avatar-normal" src="{$Author.PhotoUrl}">
      </div>
      <div class="author-info">
        <span class="author-name">{$Author.Name}</span>
        {if $Author.RoleTag}<span class="role-tag">{$Author.RoleTag}</span>{/if}
      </div>
    </div>
    <div class="bits-post-content">
      {$Discussion.Body}
    </div>
  </div>
</div>
```

### 7.3 用户角色标签

```css
.role-tag { border: 1px solid var(--bits-text); border-radius: 3px; padding: 0 3px; }
.role-tag.role-admin { background: #2ea44f; color: #fff; }
.role-tag.role-mod { background: #3b82f6; color: #fff; }
.role-tag.role-vip { background: #f59e0b; color: #fff; }
```

---

## 8. Vanilla 核心代码修改点

### 8.1 修改文件清单

| 路径 | 修改内容 |
|------|----------|
| `applications/vanilla/views/discussions/index.php` | 列表容器结构 |
| `applications/vanilla/views/discussions/discussion.php` | 单条讨论项模板 |
| `applications/vanilla/views/discussion/index.php` | 帖子主体结构 |
| `applications/vanilla/views/discussion/comment.php` | 评论项样式 |
| `applications/dashboard/views/profile/index.php` | 用户卡片样式 |
| `applications/vanilla/views/categories/all.php` | 分类列表样式 |
| `applications/dashboard/views/default.master.tpl` | 主框架三栏布局 |

### 8.2 修改原则

- 尽量通过 CSS 类名覆盖实现样式修改
- 必须改结构时，保留原有功能逻辑
- 添加注释标记修改点：`// BITS-THEME: modified`

---

## 9. 架构原则 - 轻后端 / 重客户端

### 9.1 核心理念

| 原则 | 说明 |
|------|------|
| **MySQL 纯存储** | 不使用存储过程、触发器、复杂 JOIN，只做 CRUD |
| **计算前移** | 排序、过滤、聚合等运算尽量在客户端完成 |
| **并发客户端控制** | 乐观锁、请求队列、防抖节流由前端处理 |

### 9.2 架构图

```
┌─────────────────────────────────────────────────────────────┐
│  客户端 (JavaScript)                                        │
│  • 数据过滤/排序/分页                                       │
│  • 本地缓存 (localStorage / IndexedDB)                     │
│  • 并发控制 (请求队列 / 乐观锁 / 防抖)                     │
├─────────────────────────────────────────────────────────────┤
│  服务端 (PHP)                                               │
│  • 简单 CRUD 操作                                          │
│  • 身份验证 / 权限校验                                     │
├─────────────────────────────────────────────────────────────┤
│  MySQL (纯存储)                                             │
│  • SELECT / INSERT / UPDATE / DELETE                       │
│  • 无存储过程 / 无触发器                                   │
└─────────────────────────────────────────────────────────────┘
```

### 9.3 客户端并发控制

```javascript
// js/concurrency.js
class RequestQueue {
  constructor() {
    this.queue = [];
    this.processing = false;
  }

  async enqueue(request) {
    return new Promise((resolve, reject) => {
      this.queue.push({ request, resolve, reject });
      this.process();
    });
  }

  async optimisticUpdate(endpoint, data, version) {
    const response = await fetch(endpoint, {
      method: 'POST',
      body: JSON.stringify({ ...data, _version: version })
    });
    if (response.status === 409) {
      return { conflict: true, newData: await response.json() };
    }
    return { success: true };
  }
}
```

### 9.4 客户端缓存

```javascript
// js/cache.js
class DataCache {
  constructor() {
    this.storage = localStorage;
    this.ttl = 5 * 60 * 1000; // 5分钟
  }

  set(key, data) {
    this.storage.setItem(key, JSON.stringify({
      data,
      timestamp: Date.now()
    }));
  }

  get(key) {
    const cached = this.storage.getItem(key);
    if (!cached) return null;
    const { data, timestamp } = JSON.parse(cached);
    if (Date.now() - timestamp > this.ttl) {
      this.storage.removeItem(key);
      return null;
    }
    return data;
  }
}
```

---

## 10. 下一步

1. 创建主题目录结构
2. 移植 NodeSeek CSS 并重命名类名前缀
3. 创建 Vanilla 模板文件
4. 实现后台配色系统
5. 实现暗色模式
6. 修改 Vanilla 核心代码
7. 编写 Playwright 测试
8. 视觉回归验证

---

**文档版本**：1.0.0
**最后更新**：2026-01-17
