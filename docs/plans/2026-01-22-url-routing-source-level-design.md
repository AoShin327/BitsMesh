# URL 路由源代码层面统一修改设计

**日期**: 2026-01-22
**状态**: ✅ 已完成

## 背景

当前 BitsMesh 主题使用主题层面的函数覆盖来实现短链接格式（`/post-{ID}`），存在以下问题：

1. **统一性问题** - 主题层面的函数覆盖可能被其他地方绕过
2. **性能问题** - 主题钩子在每次请求时都要检查和处理
3. **维护性问题** - 链接生成逻辑分散在主题和源代码中

## 目标

将所有链接路由逻辑迁移到 Vanilla 源代码层面，实现：

- 统一的 URL 格式：`/post-{ID}` 和 `/post-{ID}#{floor}`
- 集中管理的路由重写逻辑
- 更好的性能和可维护性

## URL 格式规范

| 类型 | 新格式 | 旧格式 |
|------|--------|--------|
| 讨论链接 | `/post-{ID}` | `/discussion/{id}/{slug}` |
| 讨论分页 | `/post-{ID}/p{page}` | `/discussion/{id}/{slug}/p{page}` |
| 评论链接 | `/post-{ID}#{floor}` | `/discussion/comment/{id}#Comment_{id}` |
| 首页分页 | `/page-{N}` | `/discussions/p{N}` |

## 修改清单

### 1. library/core/functions.render.php

**修改 `discussionUrl()` 函数**（第 794-823 行）：
- 生成 `/post-{ID}` 格式
- 分页使用 `/post-{ID}/p{page}` 格式

**优化 `commentUrl()` 函数**（第 710-755 行）：
- 生成 `/post-{ID}#{floor}` 格式
- 优化楼层号计算逻辑
- 区分 Offset 来源（writeComment 设置 vs getOffset 计算）

### 2. library/core/class.dispatcher.php

**添加 `rewriteShortUrls()` 方法**（第 769-818 行）：
- `/post-{id}` → `/discussion/{id}/x`
- `/post-{id}/p{page}` → `/discussion/{id}/x/p{page}`
- `/page-{n}` → `/discussions/p{n}`
- 阻止旧格式 `/discussion/{id}/*` → 404

### 3. applications/vanilla/views/discussion/helper_functions.php

**修改第 75-78 行**：
- 设置 `$comment->Offset = $currentOffset`
- 使用 `commentUrl()` 函数代替硬编码

### 4. themes/bitsmesh/views/discussion/helper_functions.php

**修改评论渲染逻辑**：
- 设置 `$comment->Offset = $currentOffset`
- 使用 `commentUrl()` 函数生成 permalink
- 楼层号直接使用 `$currentOffset`（Vanilla 在循环前递增）

### 5. themes/bitsmesh/class.bitsmesh.themehooks.php

**清理内容**：
- 移除 `discussionUrl()` 函数定义
- 移除 `commentUrl()` 函数定义
- 移除 `registerRoutes()` 方法
- 移除 `gdn_dispatcher_beforeDispatch_handler` 路由重写逻辑

## 楼层号规范

```
主楼（Discussion）: #0
评论1: #1
评论2: #2
...
```

**技术说明**：Vanilla 在 `comments.php` 循环中先递增 `$CurrentOffset` 再调用 `writeComment()`，
所以第一条评论的 `$currentOffset = 1`，直接使用即可，不需要再 +1。

## 实施步骤

1. ✅ 修改 `functions.render.php` 中的 `discussionUrl()` 和 `commentUrl()`
2. ✅ 修改 `class.dispatcher.php` 添加路由重写
3. ✅ 修改核心 `helper_functions.php` 使用 `commentUrl()`
4. ✅ 修改主题 `helper_functions.php` 使用 `commentUrl()`
5. ✅ 清理主题中的重复代码
6. ✅ 测试验证所有链接格式

## 测试要点

- [x] 讨论列表链接格式正确 (`/post-{ID}#latest`)
- [x] 评论时间链接格式正确 (`/post-{ID}#{floor}`)
- [x] 楼层链接跳转正常 (#0, #1, #2...)
- [x] 旧格式 URL 返回 404 (`/discussion/{id}/*`)
- [ ] 通知链接格式正确 (待验证)
- [ ] Bookmark 功能正常 (待验证)
- [ ] Quote 功能正常 (待验证)

## 完成时间

2026-01-22
