[根目录](../../CLAUDE.md) > [applications](../) > **conversations**

---

# Conversations 应用模块（私信系统）

## 变更记录

| 日期 | 变更内容 |
|------|---------|
| 2026-01-17 20:48:21 | 初始化模块文档 |

---

## 模块职责

**Conversations** 提供用户之间的私密对话功能，类似于邮件或即时消息系统。

**核心功能**：
- 创建会话（Conversation）
- 发送消息（Message）
- 多人对话
- 消息通知（邮件/站内）
- 会话管理（删除、标记已读）

---

## 入口与启动

### 应用配置
**文件**：`addon.json`

```json
{
  "key": "conversations",
  "name": "Conversations",
  "type": "addon"
}
```

### 主要控制器
- **会话列表**：`controllers/class.conversationscontroller.php`
- **消息详情**：`controllers/class.messagescontroller.php`

---

## 对外接口

### REST API

**位置**：`controllers/api/*ApiController.php`

| 端点 | 控制器 | 说明 |
|------|--------|------|
| `/api/v2/conversations` | `ConversationsApiController` | 会话 CRUD |
| `/api/v2/messages` | `MessagesApiController` | 消息 CRUD |

### 数据结构示例

#### Conversation
```json
{
  "conversationID": 456,
  "subject": "会话主题",
  "participants": [2, 3, 5],
  "countMessages": 15,
  "dateInserted": "2026-01-17T10:00:00Z"
}
```

#### Message
```json
{
  "messageID": 789,
  "conversationID": 456,
  "body": "消息内容",
  "insertUserID": 2,
  "dateInserted": "2026-01-17T10:05:00Z"
}
```

---

## 关键依赖与配置

### PHP 依赖
- `ConversationModel` - 会话模型
- `ConversationMessageModel` - 消息模型

### 配置项
```php
// 邮件通知偏好
$Configuration['Preferences']['Email']['ConversationMessage'] = '1';

// 弹窗通知偏好
$Configuration['Preferences']['Popup']['ConversationMessage'] = '1';
```

---

## 数据模型

### 主要数据表
- `GDN_Conversation` - 会话表
- `GDN_ConversationMessage` - 消息表
- `GDN_UserConversation` - 用户-会话关联表（参与者）

### 模型文件
**位置**：`models/`

| 模型类 | 文件 | 职责 |
|--------|------|------|
| `ConversationModel` | `class.conversationmodel.php` | 会话 CRUD |
| `ConversationMessageModel` | `class.conversationmessagemodel.php` | 消息 CRUD |

---

## 测试与质量

### 建议测试场景
- 多人会话创建
- 消息发送与通知
- 权限验证（只有参与者可见）
- 会话删除（软删除）

---

## 相关文件清单

```
conversations/
├── addon.json
├── controllers/
│   ├── api/
│   │   ├── ConversationsApiController.php
│   │   └── MessagesApiController.php
│   ├── class.conversationscontroller.php
│   └── class.messagescontroller.php
├── models/
│   ├── class.conversationmodel.php
│   └── class.conversationmessagemodel.php
└── views/
    ├── conversations/
    └── messages/
```

---

**最后更新**：2026-01-17 20:48:21
