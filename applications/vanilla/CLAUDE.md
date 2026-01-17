[根目录](../../CLAUDE.md) > [applications](../) > **vanilla**

---

# Vanilla 应用模块（论坛核心）

## 变更记录

| 日期 | 变更内容 |
|------|---------|
| 2026-01-17 20:48:21 | 初始化模块文档 |

---

## 模块职责

**Vanilla** 是论坛的核心应用，提供讨论板、分类、评论、标签等社区互动功能。

**核心功能**：
- 讨论（Discussion）的创建、编辑、删除
- 评论（Comment）系统
- 分类（Category）层级管理
- 标签（Tags）功能
- 书签（Bookmarks）
- 内容审核与举报
- 搜索功能

---

## 入口与启动

### 应用配置
**文件**：`addon.json`

```json
{
  "key": "vanilla",
  "name": "Vanilla",
  "type": "addon",
  "priority": 10,
  "allowDisable": false,
  "setupController": "setup"
}
```

### 主要控制器
- **讨论列表**：`controllers/class.discussionscontroller.php`
- **讨论详情**：`controllers/class.discussioncontroller.php`
- **分类管理**：`controllers/class.categoriescontroller.php`
- **发布内容**：`controllers/class.postcontroller.php`
- **标签**：`controllers/class.tagscontroller.php`
- **审核**：`controllers/class.moderationcontroller.php`

---

## 对外接口

### REST API

**位置**：`controllers/api/*ApiController.php`

| 端点 | 控制器 | 说明 |
|------|--------|------|
| `/api/v2/discussions` | `DiscussionsApiController` | 讨论 CRUD |
| `/api/v2/comments` | `CommentsApiController` | 评论 CRUD |
| `/api/v2/categories` | `CategoriesApiController` | 分类管理 |
| `/api/v2/drafts` | `DraftsApiController` | 草稿管理 |

### 数据结构示例

#### Discussion
```json
{
  "discussionID": 123,
  "name": "讨论标题",
  "body": "讨论内容（富文本）",
  "categoryID": 5,
  "insertUserID": 2,
  "dateInserted": "2026-01-17T12:00:00Z",
  "countComments": 42,
  "countViews": 1234
}
```

#### Category
```json
{
  "categoryID": 5,
  "name": "技术讨论",
  "urlCode": "tech",
  "parentCategoryID": 1,
  "depth": 1,
  "countDiscussions": 150
}
```

---

## 关键依赖与配置

### PHP 依赖
- `CategoryModel` - 分类模型
- `DiscussionModel` - 讨论模型
- `CommentModel` - 评论模型
- `DraftModel` - 草稿模型
- `VanillaSearchModel` - 搜索引擎

### 配置项
```php
// 默认控制器
$Configuration['Routes']['DefaultController'] = 'discussions';

// 输入格式
$Configuration['Garden']['InputFormatter'] = 'Rich'; // Rich, Html, BBCode, Markdown

// 编辑超时（秒）
$Configuration['Garden']['EditContentTimeout'] = 3600;

// 提及功能
$Configuration['Garden']['Format']['Mentions'] = true;
```

---

## 数据模型

### 主要数据表
- `GDN_Discussion` - 讨论主题
- `GDN_Comment` - 评论
- `GDN_Category` - 分类
- `GDN_Draft` - 草稿
- `GDN_Tag` - 标签
- `GDN_TagDiscussion` - 标签-讨论关联
- `GDN_UserDiscussion` - 用户-讨论关联（书签等）

### 模型文件
**位置**：`models/`

| 模型类 | 文件 | 职责 |
|--------|------|------|
| `DiscussionModel` | `class.discussionmodel.php` | 讨论 CRUD、搜索、排序 |
| `CommentModel` | `class.commentmodel.php` | 评论 CRUD、分页 |
| `CategoryModel` | `class.categorymodel.php` | 分类树管理、权限 |
| `DraftModel` | `class.draftmodel.php` | 草稿自动保存 |
| `VanillaSearchModel` | `class.vanillasearchmodel.php` | 全文搜索 |

### 核心方法示例

#### 获取讨论列表
```php
$discussionModel = new DiscussionModel();
$discussions = $discussionModel->get(0, 20, [
    'CategoryID' => 5
]);
```

#### 添加评论
```php
$commentModel = new CommentModel();
$commentID = $commentModel->save([
    'DiscussionID' => 123,
    'Body' => '评论内容',
    'Format' => 'Rich'
]);
```

---

## 测试与质量

### 测试覆盖
- **单元测试**：未在当前扫描中发现
- **API 测试**：建议补充
- **集成测试**：建议补充

### 建议测试场景
- 讨论创建与权限验证
- 评论嵌套与分页
- 分类树的增删改查
- 标签关联与搜索
- 草稿自动保存

---

## 常见问题 (FAQ)

### Q1: 如何自定义讨论排序？
**A**: 修改 `DiscussionModel::get()` 的 `$Wheres` 参数，或使用 `orderBy()` 方法。

### Q2: 如何添加自定义字段到讨论？
**A**:
1. 通过 `structure()` 在 `setup()` 方法中添加字段
2. 修改 `DiscussionModel::save()` 和 `DiscussionModel::get()`
3. 更新视图模板

### Q3: 评论如何实现分页？
**A**: `CommentModel` 自带分页支持，使用 `$Offset` 和 `$Limit` 参数。

### Q4: 如何集成第三方搜索引擎？
**A**: 实现 `SearchModel` 接口，或覆盖 `VanillaSearchModel::search()` 方法。

---

## 相关文件清单

### 关键目录结构
```
vanilla/
├── addon.json                 # 应用配置
├── controllers/               # 控制器
│   ├── api/                  # REST API
│   │   ├── DiscussionsApiController.php
│   │   ├── CommentsApiController.php
│   │   └── CategoriesApiController.php
│   ├── class.discussionscontroller.php
│   ├── class.discussioncontroller.php
│   ├── class.categoriescontroller.php
│   └── ...
├── models/                    # 数据模型
│   ├── class.discussionmodel.php
│   ├── class.commentmodel.php
│   ├── class.categorymodel.php
│   └── ...
├── views/                     # Smarty 视图
│   ├── discussions/
│   ├── post/
│   ├── categories/
│   └── ...
├── settings/                  # 设置页面
│   └── ...
├── design/                    # CSS 样式
│   ├── tag.css
│   └── spoilers.css
└── library/                   # 工具类
    └── class.categorycollection.php
```

### 权限节点
- `Vanilla.Discussions.View` - 查看讨论
- `Vanilla.Discussions.Add` - 发起讨论
- `Vanilla.Discussions.Edit` - 编辑讨论
- `Vanilla.Comments.Add` - 发表评论
- `Vanilla.Comments.Edit` - 编辑评论
- `Vanilla.Discussions.Close` - 关闭讨论
- `Vanilla.Discussions.Delete` - 删除讨论

---

**最后更新**：2026-01-17 20:48:21
