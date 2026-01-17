# Flagging 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **Flagging**

---

## 模块职责

**Flagging** 插件允许用户举报违反论坛规则的讨论和评论内容，管理员可查看举报并采取行动。

---

## 入口与启动

### 主文件
- **插件类**：`class.flagging.plugin.php` - `FlaggingPlugin`
- **配置文件**：`addon.json`

### 设置页面
- **路径**：`/dashboard/plugin/flagging`
- **权限**：`Garden.Moderation.Manage`

---

## 对外接口

### 举报功能
- **举报按钮**：在讨论/评论操作栏显示
- **举报表单**：弹出窗口或页面
- **举报类型**：垃圾信息、冒犯性内容、其他

### 视图文件
- `views/flag.php` - 举报表单
- `views/report.php` - 举报列表（管理员）
- `views/reportcomment.php` - 评论举报详情
- `views/reportemail.php` - 举报邮件模板
- `views/flagging.php` - 插件设置页面

---

## 核心功能

### 1. 用户举报
- 任何用户可举报内容
- 填写举报原因
- 自动记录举报人信息

### 2. 管理员通知
- 新举报邮件通知（可配置）
- 权限：`Plugins.Flagging.Notify`
- 邮件模板：`views/reportemail.php`

### 3. 举报管理
- 查看所有举报
- 审核并处理举报
- 删除或驳回举报

### 4. 举报统计
- 记录举报次数
- 显示举报状态

---

## 关键依赖与配置

### 权限
```php
'Plugins.Flagging.Notify' => '接收举报通知'
```

### 配置项
- 举报阈值（可选）
- 通知邮箱列表
- 自动处理规则

---

## 数据模型

### 举报表（可能存在）
- `FlagID` - 举报 ID
- `RecordType` - 内容类型（Discussion/Comment）
- `RecordID` - 内容 ID
- `UserID` - 举报人 ID
- `Reason` - 举报原因
- `DateInserted` - 举报时间
- `Status` - 状态（Pending/Resolved/Dismissed）

---

## 事件钩子

### 已实现的钩子
- 讨论/评论操作栏钩子（添加举报按钮）
- 邮件发送钩子（通知管理员）

---

## 测试与质量

### 测试覆盖
- **单元测试**：无
- **集成测试**：无

---

## 常见问题 (FAQ)

### Q1: 如何自定义举报原因选项？
修改插件代码中的举报原因数组。

### Q2: 举报是否匿名？
默认记录举报人信息，但可修改为匿名举报。

### Q3: 如何批量处理举报？
在管理后台举报列表中使用批量操作功能。

---

## 相关文件清单

```
plugins/Flagging/
├── addon.json                          # 插件元数据
├── class.flagging.plugin.php           # 主插件类
├── views/
│   ├── flag.php                        # 举报表单
│   ├── report.php                      # 举报列表
│   ├── reportcomment.php               # 评论举报详情
│   ├── reportemail.php                 # 举报邮件模板
│   └── flagging.php                    # 插件设置页面
├── icon.png                            # 插件图标
└── flagging.png                        # 功能图标
```

---

**最后更新**：2026-01-17 20:57:17 | **文档版本**：1.0.0
